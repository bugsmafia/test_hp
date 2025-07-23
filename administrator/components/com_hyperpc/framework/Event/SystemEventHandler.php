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

namespace HYPERPC\Event;

use JBZoo\Utils\FS;
use JBZoo\Utils\Url;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Cake\Utility\Inflector;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\CMS\Router\Route;
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\AuthHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use HYPERPC\Elements\ElementAuth;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use JBZoo\Assets\Asset\AbstractAsset as Asset;

/**
 * Class SystemEventHandler
 *
 * @package HYPERPC\Event
 *
 * @since   2.0
 */
class SystemEventHandler extends Event
{

    /**
     * This Joomla! event is triggered immediately his event is triggered before the
     * framework creates the Head section of the Document.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function beforeCompileHead()
    {
        self::_excludeSiteStyles([
            'media/com_finder/css/finder.min.css',
            'media/mod_simpleform2/css/styles.css'
        ]);

        self::_loadAssets();
        self::_prepareMeta();
    }

    /**
     * This event is triggered immediately before the framework has rendered the application.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function beforeRender()
    {
        $app = Factory::getApplication();
        if ($app->isClient('site')) {
            $input = $app->getInput();

            $option = $input->get('option');
            $view   = $input->get('view');

            //  Hack for joomla content page my.site/component/content.
            if ($input->count() === 3 && $view === 'categories' && $option === 'com_content') {
                throw new \Exception(Text::_('COM_HYPERPC_PAGE_NOT_FOUND'), 404);
            }
        }
    }

    /**
     * Initialize HYPERPC Framework trigger.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function initialize()
    {
        self::_registerAssets();
        self::_registerJClasses();
    }

    /**
     * Auto load classes by file name.
     *
     * @param   string $path
     * @param   string $classPrefix
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected static function _autoLoadJClasses($path, $classPrefix)
    {
        $app   = self::getApp();
        $fPath = $app['path']->get($path);
        $files = Folder::files($fPath);

        foreach ($files as $file) {
            $fileName = Inflector::camelize($file);
            if (preg_match('/_/', $file)) {
                $nDetails = [];
                $details  = explode('_', $file);
                foreach ($details as $detail) {
                    $nDetails[] = Inflector::camelize($detail);
                }

                $fileName = implode('_', $nDetails);
            }

            $name = FS::filename($fileName);
            $className = $classPrefix . $name;

            if (!class_exists($className)) {
                \JLoader::register($className, $fPath . '/' . $file);
            }
        }
    }

    /**
     * Exclude css file paths
     *
     * @param   string[] $excludePaths
     *
     * @return  void
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    protected static function _excludeSiteStyles(array $excludePaths)
    {
        $app = Factory::getApplication();
        if (!$app->isClient('site')) {
            return;
        }

        $doc = $app->getDocument();
        if (!($doc instanceof HtmlDocument)) {
            return;
        }

        $headData = $doc->getHeadData();
        foreach ($headData['styleSheets'] as $path => $options) {
            foreach ($excludePaths as $excludePath) {
                if (strpos($path, $excludePath) !== false) {
                    unset($headData['styleSheets'][$path]);
                    continue 2;
                }
            }
        }

        $doc->setHeadData($headData);
    }

    /**
     * Load added assets in document.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected static function _loadAssets()
    {
        $app = self::getApp();

        if ($app['cms']->isClient('administrator')) {
            self::_onAdministrationAssets();
        }

        if ($app['cms']->isClient('site')) {
            self::_onSiteAssets();

            $rOption = $app['input']->get('option');
            $rView   = $app['input']->get('view');

            //  Execute cart button assets for next view action...
            if (in_array($rOption, [HP_OPTION, 'com_content']) &&
                !in_array(
                    $rView,
                    ['cart', 'configurator', 'credit', 'dashboard', 'status',
                     'compare_products', 'step_configurator', 'order', 'profile_configurations',
                     'profile_order', 'profile_orders', 'profile_reviews']
                )) {
                $app['helper']['cart']->loadBtnAssets();
            }
        }

        $language = Factory::getApplication()->getLanguage();
        $isRtl = (bool) $language->get('rtl', false);

        $list = $app['assets']->build();
        foreach ((array) $list[Asset::TYPE_JS_FILE] as $fullPath) {
            $app['doc']->addScript(self::_resolvePath($fullPath));
        }

        foreach ((array) $list[Asset::TYPE_CSS_FILE] as $fullPath) {
            $pathInfo = pathinfo($fullPath);
            if ($isRtl && preg_match('/' . HP_CACHE_ASSETS_GROUP . '$/', $pathInfo['dirname'])) {
                $rtlPath = Path::clean("{$pathInfo['dirname']}/{$pathInfo['filename']}.rtl.{$pathInfo['extension']}");

                if (!is_file($rtlPath)) {
                    $cssContent = file_get_contents($fullPath);
                    if ($cssContent) {
                        $cssContent = \CSSJanus::transform($cssContent);
                        File::write($rtlPath, $cssContent);
                    } else {
                        $rtlPath = $fullPath;
                    }
                }

                $fullPath = $rtlPath;
            }

            $app['doc']->addStyleSheet(self::_resolvePath($fullPath));
        }

        foreach ((array) $list[Asset::TYPE_JS_CODE] as $jsCode) {
            $app['doc']->addScriptDeclaration($jsCode);
        }
    }

    /**
     * On administration load assets.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected static function _onAdministrationAssets()
    {
        $app = self::getApp();

        $app['wa']
            ->useScript('jquery-ui')
            ->useScript('jquery-factory');

        $app['assets']
            ->add('admin', 'assets:less/admin.less');

        $app['helper']['assets']
            ->js('js:widget/admin.js')->widget('body.admin', 'HyperPC.Admin');
    }

    /**
     * Adding auth data to the window object and loading auth js script.
     *
     * @return void
     *
     * @todo change $inited to false when vue auth will be ready for prod
     */
    protected static function _setupAuthInitData()
    {
        static $inited = true; // change to false for testing vue auth component

        $app = self::getApp();
        if ($inited || !empty($app['user']->id)) {
            return;
        }

        $props = [
            'methods' => [],
            'codeLength' => AuthHelper::PASS_CODE_LENGTH,
            'profileHref' => Route::_('index.php?option=com_users&view=profile')
        ];

        /** @var ElementAuth[] $elements */
        $elements = array_filter(
            Manager::getInstance()->getByPosition(Manager::ELEMENT_TYPE_AUTH),
            function ($element) {
                /** @var ElementAuth $element */
                return $element->isEnabled();
            }
        );

        $methods = array_keys($elements);

        foreach ($methods as $i => $method) {
            if (!key_exists($method, $elements)) {
                continue;
            }

            $element = $elements[$method];
            $authForm = $element->getAuthForm();

            $methodData = [
                'type' => $method,
                'registrationAllowed' => $element->getConfig('create_new_profile', true, 'bool'),
                'captcha' => $element->getUseCaptcha() ? $authForm->getInput('captcha') : ''
            ];

            if (count($methods) > 1) {
                if ($i === count($methods) - 1) { // last index
                    $methodData['alternative'] = $methods[$i - 1]; // prev method
                } else {
                    $methodData['alternative'] = $methods[$i + 1]; // next method
                }
            }

            $props['methods'][] = $methodData;
        }

        $app['doc']->addScriptOptions('authProps', $props);
        $app['wa']->registerAndUseScript(
            'auth_form',
            'com_hyperpc/apps/dist/common/auth/auth-form.js',
            attributes:['defer' => true],
            dependencies:['vue', 'core']
        );

        $inited = true;
    }

    /**
     * On site load assets.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected static function _onSiteAssets()
    {
        $app = self::getApp();

        /** @var WebAssetManager $wa */
        $wa = $app['wa'];

        if (empty($app['user']->id)) {
            self::_setupAuthInitData();
        }

        $ajaxBase = '/index.php';
        if ($app->getLanguageCode() !== $app->getDefaultLanguageCode()) {
            $ajaxBase = '/' . $app->getLanguageSef(); 
        }
        $app['doc']->addScriptOptions('ajaxBase', $ajaxBase);

        // Custom captcha loader
        if ($wa->assetExists('script', 'plg_captcha_recaptcha.api') && $wa->isAssetActive('script', 'plg_captcha_recaptcha.api')) {
            $wa
                ->disableAsset('script', 'plg_captcha_recaptcha.api')
                ->registerAndUseScript('captcha_loader', 'templates/hyperpc/js/captcha-loader.js', [], ['defer' => true]); /** @todo load from media folder */
        }

        $wa->useScript('jquery-factory');

        $app['assets']
            ->add('site', 'assets:less/site.less');

        if ($app['input']->get('view') !== 'configurator') {
            $app['helper']['assets']
                ->js('js:widget/site/misc.js')
                ->widget('body', 'HyperPC.SiteMisc', [
                    'waitMsg' => Text::_('COM_HYPERPC_CONFIGURATOR_PLEASE_WAIT')
                ]);
        }

        //  Load assets from mod_hp_subscription.
        $modSubscription = ModuleHelper::isEnabled('mod_hp_subscription');
        if ($modSubscription) {
            $app['helper']['assets']
                ->js('modules:mod_hp_subscription/assets/js/subscription.js')
                ->widget('.jsSubscriptionModule', 'HyperPC.SubscriptionModule', [
                    'formToken' => Session::getFormToken()
                ]);
        }

        //  Load assets from mod_hp_configuration.
        $modConfiguration = ModuleHelper::isEnabled('mod_hp_configuration');
        if ($modConfiguration) {
            $app['helper']['assets']
                ->js('modules:mod_hp_configuration/assets/js/configuration.js')
                ->widget('.jsModuleConfiguration', 'HyperPC.ConfigurationModule');
        }

        //  Load assets from mod_hp_tradein_calculator.
        $modTradeInCalculator = ModuleHelper::isEnabled('mod_hp_tradein_calculator');
        if ($modTradeInCalculator) {
            $app['helper']['assets']
                ->js('modules:mod_hp_tradein_calculator/assets/js/tradein-calculator.js')
                ->widget('.jsTradeinCalculator', 'HyperPC.TradeinCalculator');
        }
    }

    /**
     * Register component assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected static function _registerAssets()
    {
        /** @var WebAssetRegistry $waRegistry */
        $waRegistry = Factory::getContainer()->get(WebAssetRegistry::class);
        $waRegistry->addRegistryFile('media/com_hyperpc/joomla.asset.json');
    }

    /**
     * Register Joomla! classes.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected static function _registerJClasses()
    {
        $app = self::getApp();

        self::_autoLoadJClasses('admin:tables', HP_TABLE_CLASS_PREFIX);

        if ($app['cms']->isClient('administrator')) {
            self::_autoLoadJClasses('admin:models', HP_MODEL_CLASS_PREFIX);
            \JLoader::registerAlias('JToolbarButtonHyperLink', 'HYPERPC\\Joomla\\Toolbar\\Button\\HyperLinkButton');
            \JLoader::registerAlias('JToolbarButtonUpdateProductIndex', 'HYPERPC\\Joomla\\Toolbar\\Button\\UpdateProductIndex');
        }

        if ($app['cms']->isClient('site')) {
            self::_autoLoadJClasses('site:models', HP_MODEL_CLASS_PREFIX);
        }
    }

    /**
     * Resolve assets path.
     *
     * @param   string $fullPath
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since 2.0
     */
    protected static function _resolvePath($fullPath)
    {
        $app = self::getApp();

        if (!Url::isAbsolute($fullPath)) {
            $relPath = $app['path']->url($fullPath, false);

            $fullPath = Url::buildAll(Uri::base(), [
                'path'  => $relPath,
                'query' => 'mtime=' . substr(filemtime($fullPath), -3)
            ]);
        }

        return $fullPath;
    }

    /**
     * Prepare document title and description tags
     *
     * @throws \Exception
     *
     * @since 2.0
     */
    protected static function _prepareMeta()
    {
        $app = Factory::getApplication();
        if ($app->isClient('site')) {
            $doc = $app->getDocument();
            if ($doc instanceof HtmlDocument) {
                $title = trim(preg_replace('/\s\s+/', ' ', $doc->getTitle()));
                $description = trim(preg_replace('/\s\s+/', ' ', $doc->getDescription()));

                $doc->setTitle(HTMLHelper::_('content.prepare', $title));
                $doc->setDescription(HTMLHelper::_('content.prepare', $description));

                $hyper = self::getApp();
                $hyper['helper']['opengraph']->setDefaultMetadata();
            }
        }
    }
}
