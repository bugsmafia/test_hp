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

namespace HYPERPC\Joomla\View;

use HYPERPC\App;
use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Application\SiteApplication;

/**
 * Class ViewLegacy
 *
 * @package HYPERPC\Joomla\View
 *
 * @since   2.0
 */
class ViewLegacy extends HtmlView
{

    /**
     * Hold framework application.
     *
     * @var         App
     *
     * @since       2.0
     *
     * @deprecated  Use $this->hyper property
     */
    public $app;

    /**
     * Hold framework application.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Flag of render or return html output.
     *
     * @var     bool
     *
     * @since   2.0
     */
    protected $_returnHtml = false;

    /**
     * ViewLegacy constructor.
     *
     * @param   array $config
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->app   = App::getInstance();
        $this->hyper = App::getInstance();

        $this->_prepareDocumentMeta();

        $this->initialize($config);
    }

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  mixed
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $this->_loadAssets();

        $result = $this->loadTemplate($tpl);

        if ($result instanceof \Exception) {
            return $result;
        }

        if ($this->_returnHtml === true) {
            return $result;
        }

        echo $result;
    }

    /**
     * Hook on initialize view.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
    }

    /**
     * Render view layout.
     *
     * @param   string  $name
     * @param   array   $args
     * @param   bool    $cached
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function renderLayout($name, array $args = [], $cached = true)
    {
        $layout    = $this->_name . '/tmpl/' . $name;
        $tplLayout = $this->_name . '/' . $name;

        $template = $this->hyper['path']->get('views:' . $tplLayout . '.php');
        if ($template !== null) {
            $layout = $tplLayout;
        }

        return $this->hyper['helper']['render']->render($layout, $args, 'views', $cached);
    }

    /**
     * Get view title.
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getViewTitle()
    {
        return Text::_('COM_HYPERPC_VIEW_' . Str::up($this->getName()) . '_TITLE');
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        $view = $this->hyper['input']->getCmd('view');
        if ($view !== '') {
            $this->hyper['helper']['assets']->js("js:widget/{$view}.js");
        }
    }

    /**
     * Sets document metategs from app params
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _prepareDocumentMeta()
    {
        if (!($this->hyper['app'] instanceof SiteApplication)) {
            return;
        }

        $appParams = $this->hyper['app']->getParams();

        if ($appParams->get('menu-meta_description')) {
            $this->hyper['doc']->setDescription($appParams->get('menu-meta_description'));
        }

        if ($appParams->get('menu-meta_keywords')) {
            $this->hyper['doc']->setMetadata('keywords', $appParams->get('menu-meta_keywords'));
        }

        if ($appParams->get('robots')) {
            $this->hyper['doc']->setMetadata('robots', $appParams->get('robots'));
        }
    }
}
