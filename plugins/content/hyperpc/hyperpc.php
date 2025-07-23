<?php

use Joomla\CMS\Profiler\Profiler;
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
 */

defined('_JEXEC') or die('Restricted access');

use Cake\Utility\Xml;
use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use HYPERPC\Elements\Manager;
use HYPERPC\Elements\Element;
use Joomla\CMS\User\UserHelper;
use HYPERPC\Elements\ElementHook;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Plugin\CMSPlugin;
use HYPERPC\Elements\ElementOrderHook;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Helper\ConfigurationHelper;
use Joomla\CMS\Component\ComponentHelper;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * Class PlgContenHyperPC
 *
 * @since 2.0
 */
class PlgContentHyperPC extends CMSPlugin
{

    protected static $_onAfterSaveItemEl = [];
    protected static $_onHookItemEl = [];

    /**
     * This is an event that is called after the content is saved into the database.
     * Even though article object is passed by reference, changes will not be saved since storing data into database
     * phase is past. An example use case would be redirecting user to the appropriate place after saving.
     *
     * @param   string  $context
     * @param   Table   $article
     * @param   $isNew
     *
     * @return  void
     *
     * @throws  \JBZoo\Event\Exception
     *
     * @since   2.0
     */
    public function onContentAfterSave($context, $article, $isNew)
    {
        if ($context === HP_OPTION . '.moysklad_product') {
            $this->hyper['event']
                ->on('onMoyskladProductAfterSave', [
                    'HYPERPC\Event\MoyskladProductEventHandler',
                    'onAfterSave'
                ])
                ->trigger('onMoyskladProductAfterSave', [
                    $article,
                    $isNew
                ]);
        }

        if ($this->hyper['cms']->isClient('administrator')) {
            $config       = Factory::getConfig();
            $cacheHandler = $config->get('cache_handler');
            if (class_exists('Memcached') && $cacheHandler === 'memcached') {
                $memcached = new Memcached();
                $memcached->addServer($config->get('memcached_server_host'), $config->get('memcached_server_port'));
                $memcached->flush();
            }
        }
    }

    /**
     * This is an event that is called right before the content is saved into the database.
     *
     * @param   string  $context
     * @param   Table   $article
     * @param   bool    $isNew
     *
     * @return  void
     *
     * @throws  \JBZoo\Event\Exception
     *
     * @since   2.0
     */
    public function onContentBeforeSave($context, $article, $isNew)
    {
        $this->_callEventTrigger($context, __FUNCTION__, [
            $article,
            $isNew
        ]);
    }

    /**
     * Callback method when order success created.
     *
     * @param   string              $context
     * @param   HyperPcTableOrders  $table
     * @param   bool                $isNew
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function onSiteAfterSaveOrder($context, HyperPcTableOrders $table, $isNew)
    {
        $return      = true;
        $manager     = Manager::getInstance();
        $position    = ((int) $table->form === HP_ORDER_FORM_CREDIT) ? 'credit_form' : 'order_form';
        $elementList = (array) $manager->getByPosition($position);

        //  Clear session.
        $this->hyper['helper']['cart']->clearSession();
        $this->hyper['helper']['session']->clear('form');
        $this->hyper['helper']['promocode']->clearSessionData();

        if ($isNew) {
            $this->hyper['helper']['dealMap']->addOrderId($table->id);

            /** @var Element $element */
            foreach ($elementList as $element) {
                if (!in_array($element->getType(), self::$_onAfterSaveItemEl)) {
                    $element->onAfterSaveItem($table, $return, $isNew);
                    self::$_onAfterSaveItemEl[] = $element->getType();
                }
            }

            $hookElements = $manager->getByPosition(Manager::ELEMENT_POS_ORDER_AFTER_SAVE);
            /** @var ElementOrderHook $hElement */
            foreach ($hookElements as $hElement) {
                if (!in_array($hElement->getType(), self::$_onHookItemEl)) {
                    $hElement
                        ->setConfig(['table' => $table])
                        ->hook();

                    self::$_onHookItemEl[] = $hElement->getType();
                }
            }

            /** @var Order $order */
            $order = $this->hyper['helper']['order']->findById($table->id, ['new' => true]);

            if ($order->created_user_id) {
                /** @var ConfigurationHelper $configHelper */
                $configHelper = $this->hyper['helper']['configuration'];

                $db = $configHelper->getDbo();
                foreach ($order->products->getArrayCopy() as $productConfiguration) {
                    $productConfiguration = new JSON($productConfiguration);

                    $query = $db->getQuery(true)
                        ->update($db->qn($configHelper->getTable()->getTableName(), 'a'))
                        ->set([
                            $db->qn('a.order_id')        . ' = ' . $db->q($order->id),
                            $db->qn('a.created_user_id') . ' = ' . $db->q($order->created_user_id)
                        ])
                        ->where([
                            $db->qn('a.id') . ' = ' . $productConfiguration->get('saved_configuration')
                        ]);

                    $db->setQuery($query)->execute();
                }

                $positionsData = PositionDataCollection::create($order->positions->getArrayCopy());
                foreach ($positionsData as $positionData) {
                    if ($positionData->type !== 'productvariant') {
                        continue;
                    }

                    $configuration = $configHelper->findById($positionData->option_id);
                    if (empty($configuration->order_id)) { // Don't update order_id and created user id if already set
                        $query = $db->getQuery(true)
                            ->update($db->qn($configHelper->getTable()->getTableName(), 'a'))
                            ->set([
                                $db->qn('a.order_id')        . ' = ' . $db->q($order->id),
                                $db->qn('a.created_user_id') . ' = ' . $db->q($order->created_user_id)
                            ])
                            ->where([
                                $db->qn('a.id') . ' = ' . $positionData->option_id
                            ]);

                        $db->setQuery($query)->execute();
                    }
                }

                $this->hyper['cms']->enqueueMessage($this->hyper['helper']['order']->getSuccessMessage($order));

                if ((int) $order->created_user_id !== (int) Factory::getUser()->id) {
                    $user = Factory::getUser($order->created_user_id);

                    $this->hyper['cms']->enqueueMessage(
                        Text::sprintf(
                            'COM_HYPERPC_USER_INFO_ALERT_FOR_AUTH_AFTER_CHECKOUT',
                            sprintf('<strong>%s</strong>', $user->username)
                        ),
                        'Notice'
                    );

                    $this->hyper['cms']->redirect($this->hyper['route']->getAuthUrl());
                }

                $this->hyper['cms']->redirect($order->getAccountViewUrl());
            }
        }

        return $return;
    }

    /**
     *  Callback method when order begin created.
     *
     * @param   string              $context
     * @param   HyperPcTableOrders  $table
     * @param   bool                $isNew
     *
     * @return  bool
     *
     * @throws  \Exception
     * @throws  \JBZoo\Event\Exception
     *
     * @since   2.0
     */
    public function onSiteBeforeSaveOrder($context, HyperPcTableOrders $table, $isNew)
    {
        $this->hyper['event']
            ->on('onBeforeSave', [
                'HYPERPC\Event\OrderEventHandler',
                'onBeforeSave'
            ])
            ->trigger('onBeforeSave', [
                $table,
                $isNew
            ]);

        return true;
    }

    /**
     * This is an event on content prepare.
     *
     * @param   $context
     * @param   $article
     * @param   $params
     * @param   int $page
     *
     * @return  true|void
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        //  Fix of finder content plugin.
        if ($article instanceof \Joomla\CMS\Table\Content) {
            return true;
        }

        $this->_processShowOnTags($article);

        $language = Factory::getApplication()->getLanguage();
        if (!array_key_exists(HP_OPTION, $language->getPaths())) {
            $language->load(HP_OPTION);
        }

        $view = $this->hyper['input']->get('view');

        $this->_clearOldSnippets($article);

        $positions = $this->hyper['helper']['position']->renderBySnippet($article);

        $this->hyper['helper']['credit']->renderBySnippet($article);
        $this->hyper['helper']['productFolder']->renderBySnippet($article);

        $article->text = $this->hyper['helper']['string']->renderDates($article->text);

        $items = array_merge($positions);

        if (count($items)) {
            $listName = null;
            $listId = null;
            if (in_array($view, ['category', 'group', 'product_folder'])) {
                $listName = Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_CATEGORY_PAGE');
                $listId = 'category_page';
            }

            $this->hyper['helper']['google']
                ->setDataLayerViewProductList($items, $listName, $listId)
                ->setJsViewItems($items, false, $listName, $listId)
                ->setDataLayerProductClickFunc()
                ->setDataLayerAddToCart();
        }

        if (preg_match('/hp-ajax-products|jsLoadMoreProducts/', $article->text)) {
            $defaultContext = $this->hyper['params']->get('product_ajax_default_context', 'position');
            $article->text .= $this->hyper['helper']['assets']->loadScript(
                $this->hyper['path']->url('js:widget/site/ajax-products.js', false),
                "$('body').HyperPCAjaxProducts({defaultContext:\"{$defaultContext}\"});"
            );

            $this->hyper['wa']->useScript('product.teaser');
        }

        if (preg_match('/jsServersOnline/', $article->text) && !preg_match('/game-servers.js/', $article->text)) {
            $article->text .= $this->hyper['helper']['assets']->loadScript(
                $this->hyper['path']->url('js:widget/site/game-servers.js', false),
                '$(\'.jsServersOnline\').HyperPCGameServers({});'
            );
        }

        if ($view === 'category' && preg_match('/hp-step-configurator/', $article->text)) {
            $article->text .= $this->hyper['helper']['assets']->loadScript(
                $this->hyper['path']->url('js:widget/site/configurator-sticky-bottom.js', false),
                '$(\'.hp-step-configurator\').HyperPCConfiguratorStickyBottom();'
            );
        }

        $_params = new JSON($params);

        if ($_params->get('render') === 'reviews' && $_params->get('item') instanceof Entity) {
            $article->text .= $this->hyper['helper']['render']->render('reviews/default', [
                'item'    => $_params->get('item'),
                'context' => $_params->get('context')
            ]);
        }

        $jampPlugin = PluginHelper::getPlugin('system', 'jamp');
        if ($context === 'com_content.article' && !empty($jampPlugin)) {
            $regex = '/<picture\sclass="tm-article-banner">[\t\n\r]*(.+)srcset="(.+)"\smedia="\(max-width:\s?639px\)"(.+)(src=".*?")\s+(alt=".+")?.+<\/picture>/i';

            preg_match($regex, $article->text, $matches);

            if (!empty($matches)) {
                $bannerSrc = $matches[2];
                $bannerAlt = isset($matches[5]) ? $matches[5] : 'alt="' . $article->title . '"';
                $ampImageTag = '<img src="' . $bannerSrc . '" ' . $bannerAlt . ' class="tm-amp-only">';

                $output = $matches[0] . PHP_EOL . $ampImageTag;
                $article->text = str_replace($matches[0], $output, $article->text);
            }
        }
    }

    /**
     * Check condition for show-on tag
     *
     * @param   string $key
     * @param   string $value
     *
     * @return  bool
     */
    protected function _checkShowOnCondition($key, $value)
    {
        switch ($key) {
            case 'device':
                $device = $this->hyper['detect']->isMobile() ? 'mobile' : 'desktop';
                return $device === $value;
        }

        return false;
    }

    protected function _clearOldSnippets(&$article)
    {
        $regexes = [
            '/{(categorystartprice|categorystartcredit)(.*?)}/i',
            '/{(productprice|productcredit)(.*?)}/i',
            '/{(?:loadproducts|products)\s(.+?)}/i',
            '/{(?:loadoptions|options)\s(.+?)}/i',
            '/{(?:loadparts|parts)\s(.+?)}/i',
            '/{loadfpscontrol(.*)}/i',
            '/{partname\s(.*?)}/i'
        ];

        foreach ($regexes as $regex) {
            preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

            if ($matches) {
                foreach ($matches as $match) {
                    $article->text = preg_replace("|$match[0]|", addcslashes('', '\\$'), $article->text, 1);
                }
            }
        }
    }

    /**
     * Process {show-on}{/show-on} tags.
     *
     * Example: {show-on device=mobile} mobile only content {/show-on}
     *
     * @param   $article
     */
    protected function _processShowOnTags(&$article)
    {
        if (strpos($article->text, '{show-on') === false) {
            return;
        }

        $pattern = '/\{show-on\s+(\w+)=(\w+)\}(.*?)\{\/show-on\}/is';

        $article->text = preg_replace_callback($pattern, function ($matches) {
            $key = $matches[1];
            $value = $matches[2];
            $innerContent = $matches[3];

            if ($this->_checkShowOnCondition($key, $value)) {
                return $innerContent;
            }

            return '';
        }, $article->text);
    }

    /**
     * On content prepare form.
     *
     * @param   Form         $form
     * @param   object|array $data
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onContentPrepareForm($form, $data)
    {
        $params = ComponentHelper::getParams('com_users');

        //  Only user mobile activate.
        if ($form->getName() === 'com_users.registration' && $params->get('use_hyperpc_component') && is_object($data)) {
            $post = new JSON($this->hyper['input']->get('jform', [], 'array'));
            if ($post->get('email1')) {
                $data->username = $post->get('email1');
                $data->email2   = $post->get('email1');
            }

            $pwd = UserHelper::genRandomPassword();
            $data->password1 = $pwd;
            $data->password2 = $pwd;
        }
    }

    /**
     * Callback method before validation data.
     *
     * @param   Form    $form
     * @param   array   $data
     *
     * @return  void
     *
     * @throws  \Cake\Utility\Exception\XmlException
     *
     * @since   2.0
     */
    public function onUserBeforeDataValidation($form, &$data)
    {
        $_data = new JSON($data);
        if ($_data->get('module') === 'mod_simpleform2') {
            $config     = Factory::getConfig();

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

            $leadNameEl = Xml::build([
                'field' => [
                    '@type'        => 'test',
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
                        ],
                    ],
                    '@description' => 'COM_HYPERPC_AMO_COUNTER_DESC',
                    '@label'       => 'COM_HYPERPC_AMO_COUNTER_LABEL'
                ]
            ]);

            $form->setField($counterEl, 'params', true, 'basic');
        }
    }

    /**
     * Callback event
     *
     * @param   string             $context
     * @param   SaveConfiguration  $configuration
     * @param   JSON               $data
     * @param   JSON               $output
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function onSaveConfigurationSavedForm($context, SaveConfiguration $configuration, $data, $output)
    {
        $manager  = Manager::getInstance();
        $elements = $manager->getByPosition(Manager::ELEMENT_POS_CONFIGURATION_AFTER_SAVE);

        /** @var ElementHook $element */
        foreach ($elements as $element) {
            $element->setConfig([
                'data' => [
                    'form'          => $data,
                    'context'       => $context,
                    'configuration' => $configuration
                ]
            ]);

            if ($element instanceof ElementConfiguratorHookAddUser) {
                $element->setSystemMessage((array) $output->get('message'));
            }

            $element->hook();

            if ($element instanceof ElementConfiguratorHookAddUser) {
                $output
                    ->set('reload', true)
                    ->set('message', $element->getSystemMessage());
            }
        }
    }

    /**
     * Callback event
     *
     * @param   string             $context
     * @param   SaveConfiguration  $configuration
     * @param   JSON               $data
     * @param   JSON               $output
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function onConfiguratorAfterNativeSave($context, SaveConfiguration $configuration, $data, $output)
    {
        $manager  = Manager::getInstance();
        $elements = $manager->getByPosition(Manager::ELEMENT_POS_CONFIGURATION_AFTER_NATIVE_SAVE);

        /** @var ElementHook $element */
        foreach ($elements as $element) {
            $element->setConfig([
                'data' => [
                    'form'          => $data,
                    'context'       => $context,
                    'configuration' => $configuration
                ]
            ]);

            if ($element instanceof ElementConfiguratorHookAddUser) {
                $element->setSystemMessage((array) $output->get('message'));
            }

            $element->hook();

            if ($element instanceof ElementConfiguratorHookAddUser) {
                $output
                    ->set('reload', true)
                    ->set('message', $element->getSystemMessage());
            }
        }
    }

    /**
     * Callback event
     *
     * @param   string             $context
     * @param   SaveConfiguration  $configuration
     * @param   JSON               $data
     * @param   JSON               $output
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function onConfiguratorAfterAuthSave($context, SaveConfiguration $configuration, $data, $output)
    {
        $manager  = Manager::getInstance();
        $elements = $manager->getByPosition(Manager::ELEMENT_POS_CONFIGURATION_AFTER_AUTH_SAVE);

        /** @var ElementHook $element */
        foreach ($elements as $element) {
            $element->setConfig([
                'data' => [
                    'form'          => $data,
                    'context'       => $context,
                    'configuration' => $configuration
                ]
            ]);

            $element->hook();
        }
    }
}
