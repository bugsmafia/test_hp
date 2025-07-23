<?php
/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package     HYPERPC
 * @license     Proprietary
 * @copyright   Proprietary https://hyperpc.ru/license
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Cake\Utility\Xml;
use JBZoo\Utils\Timer;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Cookie;
use Joomla\CMS\Form\Form;
use Joomla\Filesystem\Path;
use JBZoo\Profiler\Profiler;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Access\Exception\NotAllowed;

/**
 * Class PlgSystemHyperPC
 *
 * @since 2.0
 */
class PlgSystemHyperPC extends CMSPlugin
{

    const PROFILER_LOAD_AVG     = 2;
    const PROFILER_PAGE_SPEED   = 1500;

    /**
     * Hold HYPERPC Application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Hold Profiler object.
     *
     * @var     Profiler
     *
     * @since   2.0
     */
    protected $_profiler;

    /**
     * Hold styleTags.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_styleTags = [];

    /**
     * This event is triggered after the framework has loaded and the application initialise method has been called.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  NotAllowed
     *
     * @since   2.0
     */
    public function onAfterInitialise()
    {
        $this->_initFramework();

        $this->hyper = App::getInstance();

        if ($this->hyper['helper']['auth']->isUserBanned()) {
            $this->hyper['cms']->close('Access is limited');
        }

        if ($this->hyper['cms']->isClient('site')) {
            $input = $this->hyper['input'];
            if (strpos($input->get('task', ''), '.display-group-configurator') !== false && $input->get('iframe') !== '1') {
                $this->hyper['cms']->redirect(Uri::current());
            }
        }

        $executeUserView = ['login', 'reset', 'registration', 'remind'];
        $userParams      = ComponentHelper::getParams('com_users');

        //  Allowed com_users views actions.
        if ((int) $userParams->get('use_hyperpc_component') === 1 &&
            $this->hyper['input']->get('option') === 'com_users' &&
            in_array($this->hyper['input']->get('view'), $executeUserView)
        ) {
            $this->hyper['cms']->redirect('/');
        }

        //  Set manager cookie. See cart render form fields.
        /** @var Cookie $cookie */
        $cookie = $this->hyper['input']->cookie;
        if ($cookie->get(HP_COOKIE_HMP) || $this->hyper['input']->get(HP_COOKIE_HMP) === 'allowed') {
            $expires = time() + 60 * 60 * 24 * 30;
            $cookie->set(HP_COOKIE_HMP, true, [
                'expires'  => $expires,
                'path'     => '/'
            ]);
        }
    }

    /**
     * After route.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onAfterRoute()
    {
        $app = Factory::getApplication();
        if ($app->getInput()->get('option') !== HP_OPTION) {
            if ($app->isClient('site')) {
                $lang = $app->getLanguage();
                $lang->load(HP_OPTION, Path::clean(JPATH_SITE . '/components/' . HP_OPTION), $lang->getTag());
            }

            if ($app->isClient('administrator')) {
                $lang = $app->getLanguage();
                $lang->load(HP_OPTION, Path::clean(JPATH_ADMINISTRATOR . '/components/' . HP_OPTION), $lang->getTag());
            }
        }

        if ($this->hyper['cms']->isClient('site') && !$this->hyper['user']->isManager()) {
            /** @var Cookie $cookie */
            $cookie = $this->hyper['input']->cookie;
            if (!$cookie->get(HP_COOKIE_HMP)) {
                $activeMenuItem = $this->hyper['cms']->getMenu()->getActive();

                if (empty($activeMenuItem)) {
                    return;
                }

                $viewlevel = $this->hyper['helper']['user']->getViewlevelTitle($activeMenuItem->access);

                if ($viewlevel === 'HYPERPC Manager') {
                    $this->hyper['cms']->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
                    $this->hyper['cms']->redirect('/', 303);
                }
            }
        }
    }

    /**
     * Called after the data for a JForm has been retrieved.
     * It can be used to modify the data for a JForm object in memory before rendering.
     * This is usually used in tandem with the onContentPrepareForm method -
     * this event adds the data to the already altered JForm.
     *
     * @param   Form    $form
     * @param   mixed   $data
     *
     * @return  bool
     *
     * @throws  \UnexpectedValueException
     * @throws  \Cake\Utility\Exception\XmlException
     *
     * @since   2.0
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        FormHelper::addFieldPath($this->hyper['path']->get('admin:models/fields'));

        $userParams = ComponentHelper::getParams('com_users');
        if ($form->getName() === 'com_users.profile' && (int) $userParams->get('use_hyperpc_component') === 1) {
            $form->setFieldAttribute('password1', 'type', 'hidden');
            $form->setFieldAttribute('password2', 'type', 'hidden');
            $form->setFieldAttribute('email2', 'type', 'hidden');
            $form->setFieldAttribute('email2', 'class', 'jsEmailValue');
            $form->setFieldAttribute('name', 'class', 'uk-input');
            $form->setFieldAttribute('username', 'class', 'uk-input');
        }

        /** @var Joomla\CMS\Input\Input $input */
        $input = $this->hyper['input'];

        if ($input->getCmd('option') === 'com_fields' && $input->getCmd('context') === 'com_hyperpc.position') {
            if ($input->getCmd('view') === 'fields') { // fields list view
                $assignedCatIdsEl = Xml::build([
                    'field' => [
                        '@multiple'     => true,
                        'option'        => [
                            [
                                '@value' => 0,
                                '@'      => 'JALL'
                            ]
                        ],
                        '@type'         => 'hpfolders',
                        '@label'        => 'JCATEGORY',
                        '@name'         => 'assigned_cat_ids',
                        '@onchange'     => 'this.form.submit();',
                        '@description'  => 'JFIELD_FIELDS_CATEGORY_DESC',
                        '@hint'         => 'JOPTION_SELECT_CATEGORY'
                    ]
                ]);

                $form->setField($assignedCatIdsEl, 'filter');
            } elseif ($input->getCmd('layout') === 'edit') { // edit field page actions
                $assignedCatIdsEl = Xml::build([
                    'field' => [
                        '@multiple'     => true,
                        'option'        => [
                            [
                                '@value' => 0,
                                '@'      => 'JALL'
                            ]
                        ],
                        '@type'         => 'hpfolders',
                        '@label'        => 'JCATEGORY',
                        '@name'         => 'assigned_cat_ids',
                        '@description'  => 'JFIELD_FIELDS_CATEGORY_DESC'
                    ]
                ]);

                $form->setField($assignedCatIdsEl);
            }
        }

        if (ComponentHelper::getComponent('com_hyperpc')->id &&
            $input->getCmd('option') === 'com_config' &&
            $input->getCmd('component') === 'com_hyperpc' &&
            $input->getCmd('task') === 'config.save.component.apply'
        ) {
            $formData = $input->get('jform', [], 'array');
            $fields   = (array) $formData['compare_notebook_fields'];
            if (count($fields)) {
                $this->hyper['helper']['fields']->changeOrdering($fields);
            }
        }

        if (ComponentHelper::getComponent('com_hyperpc')->id &&
            $input->getCmd('option') === 'com_config' &&
            $input->getCmd('component') === 'com_users'
        ) {
            $form->setField(Xml::build([
                'field' => [
                    '@default'     => 0,
                    '@type'        => 'radio',
                    '@name'        => 'use_hyperpc_component',
                    '@layout'      => 'joomla.form.field.radio.switcher',
                    'option'       => [
                        [
                            '@value' => 0,
                            '@'      => 'JNO'
                        ],
                        [
                            '@value' => 1,
                            '@'      => 'JYES'
                        ]
                    ],
                    '@description' => 'COM_HYPERPC_USE_HYPERPC_REGISTRATION_DESC',
                    '@label'       => 'COM_HYPERPC_USE_HYPERPC_REGISTRATION_LABEL'
                ]
            ]), null, true, 'user_options');
        }

        if ($form->getName() === 'com_modules.module' && is_object($data)) {
            if ($data->module === 'mod_simpleform2') {
                $config     = Factory::getApplication()->getConfig();
                $mailFromEl = Xml::build([
                    'field' => [
                        '@type'        => 'text',
                        '@name'        => 'mail_from',
                        '@description' => Text::sprintf('COM_HYPERPC_ANSWER_SENDER_DESC', $config->get('mailfrom')),
                        '@label'       => 'COM_HYPERPC_ANSWER_SENDER_EMAIL_LABEL'
                    ]
                ]);

                $form->setField($mailFromEl, 'params', true, 'basic');

                $mailFromNameEl = Xml::build([
                    'field' => [
                        '@type'        => 'text',
                        '@name'        => 'mail_from_name',
                        '@description' => Text::sprintf('COM_HYPERPC_ANSWER_SENDER_DESC', $config->get('fromname')),
                        '@label'       => 'COM_HYPERPC_ANSWER_SENDER_NAME_LABEL'
                    ]
                ]);

                $form->setField($mailFromNameEl, 'params', true, 'basic');

                $dividerUserIdEl = Xml::build([
                    'field' => [
                        '@type'  => 'hpseparator',
                        '@label' => 'COM_HYPERPC_AMO_CRM_SEPARATOR_LABEL'
                    ]
                ]);

                $form->setField($dividerUserIdEl, 'params', true, 'basic');

                $leadNameEl = Xml::build([
                    'field' => [
                        '@type'        => 'text',
                        '@name'        => 'lead_name',
                        '@description' => 'COM_HYPERPC_AMO_LEAD_NAME_DESC',
                        '@label'       => 'COM_HYPERPC_AMO_LEAD_NAME_LABEL'
                    ]
                ]);

                $form->setField($leadNameEl, 'params', true, 'basic');

                $responsibleUserIdEl = Xml::build([
                    'field' => [
                        '@type'        => 'worker',
                        '@name'        => 'worker_id',
                        '@description' => 'COM_HYPERPC_AMO_RESPONSIBLE_USER_DESC',
                        '@label'       => 'COM_HYPERPC_AMO_RESPONSIBLE_USER_LABEL'
                    ]
                ]);

                $form->setField($responsibleUserIdEl, 'params', true, 'basic');

                $tagsEl = Xml::build([
                    'field' => [
                        '@type'        => 'text',
                        '@name'        => 'amo_tags',
                        '@description' => 'COM_HYPERPC_AMO_TAGS_DESC',
                        '@label'       => 'COM_HYPERPC_AMO_TAGS_LABEL'
                    ]
                ]);

                $form->setField($tagsEl, 'params', true, 'basic');

                $pipelinesEl = Xml::build([
                    'field' => [
                        '@name'        => 'pipeline',
                        '@type'        => 'pipelines',
                        '@description' => 'COM_HYPERPC_AMO_PIPELINE_DESC',
                        '@label'       => 'COM_HYPERPC_AMO_PIPELINE_LABEL'
                    ]
                ]);

                $form->setField($pipelinesEl, 'params', true, 'basic');

                $counterEl = Xml::build([
                    'field' => [
                        '@default'     => 0,
                        '@type'        => 'list',
                        '@name'        => 'counter',
                        'option'       => [
                            [
                                '@value' => 0,
                                '@'      => 'JNO'
                            ],
                            [
                                '@value' => 1,
                                '@'      => 'JYES'
                            ]
                        ],
                        '@description' => 'COM_HYPERPC_AMO_COUNTER_DESC',
                        '@label'       => 'COM_HYPERPC_AMO_COUNTER_LABEL'
                    ]
                ]);

                $form->setField($counterEl, 'params', true, 'basic');
            }
        }

        return true;
    }

    /**
     * This event is triggered before the framework creates the Head section of the Document..
     *
     * @return  void
     *
     * @throws  JBZoo\Event\Exception
     *
     * @since   2.0
     */
    public function onBeforeCompileHead()
    {
        $this->hyper['event']
            ->on('beforeCompileHead', [
                'HYPERPC\Event\SystemEventHandler',
                'beforeCompileHead'
            ])
            ->trigger('beforeCompileHead');
    }

    /**
     * This event is triggered immediately before the framework has rendered the application.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\Event\Exception
     *
     * @since   2.0
     */
    public function onBeforeRender()
    {
        $this->hyper['event']
            ->on('beforeRender', [
                'HYPERPC\Event\SystemEventHandler',
                'beforeRender'
            ])
            ->trigger('beforeRender');

        $this->_profilerModeDocumentRender();
    }

    /**
     * Listener for the onAfterRender event
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since 2.0
     */
    public function onAfterRender()
    {
        $app = Factory::getApplication();
        if ($app->isClient('site')) {
            $docHml = $app->getBody();

            $this->_styleTags = [];

            $docHml = preg_replace_callback(
                '/(?<!(<!--\s)|(<!--))<style\X+?<\/style>/',
                function ($matches) {
                    $this->_styleTags[] = $matches[0];
                    return '';
                },
                $docHml
            );

            if (!empty($this->_styleTags)) {
                $newHtml = preg_replace('/<\/head>/', implode(PHP_EOL, $this->_styleTags) . PHP_EOL . '</head>', $docHml);
                if ($newHtml) {
                    $docHml = $newHtml;
                }
            }
            $app->setBody($docHml);
        }
    }

    /**
     * Initialize HYPERPC Framework.
     *
     * @return  void
     *
     * @throws  NotAllowed
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _initFramework()
    {
        $hpBootstrap = JPATH_ROOT . '/administrator/components/com_hyperpc/bootstrap.php';
        if (file_exists($hpBootstrap)) {
            /** @noinspection PhpIncludeInspection */
            require_once $hpBootstrap;
            $this->_profiler = new Profiler();
            if ($this->_isProfilerMode()) {
                $this->_profiler->start();
            }
        } else {
            throw new NotAllowed(Text::_('COM_HYPERPC_BOOTSTRAP_NOT_FIND_ERROR'), 403);
        }
    }

    /**
     * Check profiler mode.
     *
     * @return  bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    private function _isProfilerMode()
    {
        $app = Factory::getApplication();
        $profilerVal = $app->input->get('profiler', 0, 'int');
        return ($app->isClient('site') && $profilerVal === 1);
    }

    /**
     * Render all document for check profiler.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    private function _profilerModeDocumentRender()
    {
        if ($this->_isProfilerMode()) {
            $app = Factory::getApplication();
            $doc = clone $app->getDocument();

            $docOptions = [
                'template'  => $app->get('theme'),
                'params'    => $app->get('themeParams'),
                'file'      => $app->get('themeFile', 'index.php')
            ];

            if ($app->get('themes.base')) {
                $docOptions['directory'] = $app->get('themes.base');
            } else {
                $docOptions['directory'] = defined('JPATH_THEMES') ? JPATH_THEMES : (defined('JPATH_BASE') ? JPATH_BASE : __DIR__) . '/themes';
            }

            $doc->parse($docOptions);
            $doc->render(false, $docOptions);

            $this->_profiler->stop();

            $loadAvg  = 0;
            $siteTime = round($this->_profiler->getTime() * 1000);
            if (function_exists('sys_getloadavg')) {
                $sysAvg  = sys_getloadavg();
                $loadAvg = $sysAvg[0];
            }

            $status = 200;
            if ($siteTime > self::PROFILER_PAGE_SPEED || $loadAvg > self::PROFILER_LOAD_AVG) {
                $status = 503;
            }

            $app->setHeader('Status', $status, true);
            $app->sendHeaders();

            $app->close(implode(', ', [
                sprintf('Time: %s', Timer::format($this->_profiler->getTime())),
                sprintf('CPU: %s', $loadAvg)
            ]));
        }
    }
}
