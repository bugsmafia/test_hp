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
 */

$root = dirname(__DIR__);
$iniFormatter = $root . '/scripts/vendor/ini-formatter/iniFormatter.php';

if (is_file($iniFormatter)) {
    /** @noinspection PhpIncludeInspection */
    require_once $iniFormatter;
} else {
    echo 'Please, install ini formatter. Use ./bin/ini-formatter.sh';
    exit(0);
}

if (is_file('../composer.json')) {
    $autoload  = '../libraries/hyperpc/vendor/autoload.php';
    if (file_exists($autoload)) {
        /** @noinspection PhpIncludeInspection */
        require_once $autoload;
    } else {
        echo 'Please execute "composer update" !';
        exit(0);
    }
} else {
    echo 'Not find composer.json';
    exit(0);
}

use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use Cake\Utility\Hash;

/**
 * Formatter class.
 *
 * @package Ini
 *
 * @since   2.0
 */
class Formatter
{

    /**
     * Default configuration.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_defaultConfig = [
        'root_path'     => '',
        'output_path'   => '',
        'copyrights'    => [
            '',
            'HYPERPC - The shop of powerful computers.',
            '',
            'This file is part of the HYPERPC package.',
            'For the full copyright and license information, please view the LICENSE',
            'file that was distributed with this source code.',
            '',
            'Note: All ini files need to be saved as UTF-8 (no BOM)',
            '',
            '@package      HYPERPC',
            '@license      Proprietary',
            '@copyright    Proprietary https://hyperpc.ru/license',
            '@link         https://github.com/HYPER-PC/HYPERPC".',
            '@author       Sergey Kalistratov <kalistratov.s.m@gmail.com>',
            ''
        ],
        'defines'     => [
            '_QQ_' => '"\""', // Joomla CMS hack
        ]
    ];

    /**
     * Process module list.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_modules = [
        'mod_hp_cart',
        'mod_hp_configuration',
        'mod_hp_navbar_user',
        'mod_hp_subscription',
        'mod_hp_tradein_calculator'
    ];

    /**
     * Process plugin list.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_plugins = [
        'fields:hpseparator',
        'finder:notebooks',
        'finder:parts',
        'finder:products',
        'simpleform2:notifyaftersubmit',
        'user:hyperpc'
    ];

    /**
     * Root path.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_root;

    /**
     * Formatter constructor.
     *
     * @since   2.0
     */
    public function __construct()
    {
        $this->_root = dirname(__DIR__);
        $this->_defaultConfig['root_path'] = $this->_root;
    }

    /**
     * Build ini formatter.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function build()
    {
        $this
            ->modules()
            ->plugins()
            ->elements()
            ->component();
    }

    /**
     * Process component.
     *
     * @return  $this
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function component()
    {
        $configAdmin = $this->_getConfig(['output_path' => '/administrator/components/com_hyperpc/language/ru-RU']);
        $adminFormatter = new iniFormatter($configAdmin);

        $configSite = $this->_getConfig(['output_path' => '/components/com_hyperpc/language/ru-RU']);
        $siteFormatter = new iniFormatter($configSite);

        /** Administrator component language format */
        $admin    = './administrator/components/com_hyperpc/language/ru-RU/ru-RU.com_hyperpc.ini';
        $adminSys = './administrator/components/com_hyperpc/language/ru-RU/ru-RU.com_hyperpc.sys.ini';

        $adminFormatter->format($admin);
        $adminFormatter->format($adminSys);

        /** Site component language format */
        $site = './components/com_hyperpc/language/ru-RU/ru-RU.com_hyperpc.ini';
        $siteFormatter->format($site);

        return $this;
    }

    /**
     * Process elements.
     *
     * @return  $this
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function elements()
    {
        $elementPath = '../media/hyperpc/elements';
        $executeDirs = ['.', '..'];

        if (FS::isDir($elementPath)) {
            $elementGroups = scandir($elementPath);
            foreach ($elementGroups as $elementGroup) {
                if (!in_array($elementGroup, $executeDirs)) {
                    $groupElements = scandir(FS::clean($elementPath . '/' . $elementGroup));
                    foreach ($groupElements as $element) {
                        if (!in_array($element, $executeDirs)) {
                            $element      = Str::low($element);
                            $elementGroup = Str::low($elementGroup);

                            $elementLangPath = implode('/', [
                                $elementPath,
                                $elementGroup,
                                $element,
                                'language/ru-RU',
                                'ru-RU.el_' . $elementGroup . '_' . $element . '.ini'
                            ]);

                            $elementLangOutputPath = str_replace('..', '', implode('/', [
                                $elementPath,
                                $elementGroup,
                                $element,
                                'language/ru-RU'
                            ]));

                            if (FS::isFile($elementLangPath)) {
                                $elementLangPath = str_replace('..', '', $elementLangPath);

                                $formatter = new iniFormatter($this->_getConfig([
                                    'output_path' => $elementLangOutputPath
                                ]));

                                $formatter->format($elementLangPath);
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Process modules.
     *
     * @return  $this
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function modules()
    {
        foreach ($this->_modules as $module) {
            $formatter = new iniFormatter($this->_getConfig([
                'output_path' => '/modules/' . $module . '/language/ru-RU'
            ]));

            $path = './modules/' . $module . '/language/ru-RU/ru-RU.' . $module . '.ini';
            $formatter->format($path);

            $pathSys = './modules/' . $module . '/language/ru-RU/ru-RU.' . $module . '.sys.ini';
            $formatter->format($pathSys);
        }

        return $this;
    }

    /**
     * Process plugins.
     *
     * @return  $this
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function plugins()
    {
        foreach ($this->_plugins as $plugin) {
            list ($type, $name) = explode(':', $plugin);

            $formatter = new iniFormatter($this->_getConfig([
                'output_path' => '/plugins/' . $type . '/' . $name . '/language/ru-RU'
            ]));

            $path = './plugins/' . $type . '/' . $name . '/language/ru-RU/ru-RU.plg_' . $type . '_' . $name . '.ini';
            $formatter->format($path);

            $pathSys = './plugins/' . $type . '/' . $name . '/language/ru-RU/ru-RU.plg_' . $type . '_' . $name . '.sys.ini';
            $formatter->format($pathSys);
        }

        return $this;
    }

    /**
     * Get configuration.
     *
     * @param   array $options
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getConfig(array $options)
    {
        $config = $this->_defaultConfig;

        if (array_key_exists('output_path', $options)) {
            $config['output_path'] = FS::clean($this->_root . '/' . $options['output_path']);
            unset($options['output_path']);
        } else {
            echo 'Please, set output_path';
            exit(0);
        }

        return Hash::merge($config, $options);
    }
}

(new Formatter())->build();
