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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

namespace HYPERPC\Test;

use Symfony\Component\Finder\Finder;
use JBZoo\PHPUnit\Codestyle as JBCodeStyle;

/**
 * Class CodeStyleTest
 *
 * @package HYPERPC\Test
 */
class CodeStyleTest extends JBCodeStyle
{

    /**
     * Package copyright.
     *
     * @var string
     */
    protected $_packageCopyright = 'Proprietary https://hyperpc.ru/license';

    /**
     * Package license.
     *
     * @var string
     */
    protected $_packageLicense = 'Proprietary';

    /**
     * Package link.
     *
     * @var string
     */
    protected $_packageLink = 'https://github.com/HYPER-PC/_PACKAGE_';

    /**
     * Name of package.
     *
     * @var string
     */
    protected $_packageName = 'HYPERPC';

    /**
     * Sets up the fixture, for example, open a network connection.
     *
     * @throws \JBZoo\PHPUnit\Exception
     */
    public function setUp()
    {
        $this->_packageDesc     = $this->_setPackageDesc();
        $this->_validHeaderPHP  = $this->_setValidHeaderPHP();
        $this->_validHeaderXML  = $this->_setValidHeaderXML();
        $this->_validHeaderINI  = $this->_setValidHeaderINI();
        $this->_validHeaderSH   = $this->_setValidHeaderSH();
        $this->_validHeaderJS   = $this->_setValidHeadJS();
        $this->_validHeaderLESS = $this->_setValidHeadLess();
        $this->_validHeaderSQL  = $this->_setValidHeadSQL();

        parent::setUp();
    }

    /**
     * Try to find cyrilic symbols in the code.
     *
     * @return void
     */
    public function testCyrillic()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(PROJECT_ROOT)
            ->exclude($this->_excludePaths)
            ->exclude('tests')
            ->exclude([
                'administrator/components/com_hyperpc/assets/js',
                'administrator/components/com_hyperpc/framework/Money'
            ])
            ->notPath(basename(__FILE__))
            ->ignoreDotFiles(false)
            ->notName('/\.md$/')
            ->notName('/defines\.php$/')
            ->notName('/\.min\.(js|css)$/')
            ->notName('/\.min\.(js|css)\.map$/')
            ->notName('/ru-RU\.com_hyperpc\.ini$/')
            ->notName('/ru-RU\.com_hyperpc\.sys\.ini$/');

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $content = \JBZoo\PHPUnit\openFile($file->getPathname());

            if (preg_match('#[А-Яа-яЁё]#ius', $content)) {
                \JBZoo\PHPUnit\fail('File contains cyrilic symbols: ' . $file); // Short message in terminal
            } else {
                \JBZoo\PHPUnit\success();
            }
        }
    }

    /**
     * Package description.
     *
     * @return array
     */
    protected function _setPackageDesc()
    {
        return [
            'This file is part of the HYPERPC package.',
            'For the full copyright and license information, please view the LICENSE',
            'file that was distributed with this source code.'
        ];
    }

    /**
     * Valid header for INI files.
     *
     * @return array
     */
    protected function _setValidHeaderINI()
    {
        return [
            '; ',
            '; _PACKAGE_ - The shop of powerful computers.',
            '; ',
            '; _DESCRIPTION_INI_',
            '; ',
            '; Note: All ini files need to be saved as UTF-8 (no BOM)',
            '; ',
            '; @package      _PACKAGE_',
            '; @license      _LICENSE_',
            '; @copyright    _COPYRIGHTS_',
            '; @link         _LINK_'
        ];
    }

    /**
     * Valid header for PHP files.
     *
     * @return array
     */
    protected function _setValidHeaderPHP()
    {
        return [
            '<?php',
            '/**',
            ' * _PACKAGE_ - The shop of powerful computers.',
            ' *',
            ' * _DESCRIPTION_PHP_',
            ' *',
            ' * @package     _PACKAGE_',
            ' * @license     _LICENSE_',
            ' * @copyright   _COPYRIGHTS_',
            ' * @link        _LINK_'
        ];
    }

    /**
     *  Valid header for SH files.
     *
     * @return array
     */
    protected function _setValidHeaderSH()
    {
        return [
            '#!/usr/bin/env sh',
            '',
            '#',
            '# _PACKAGE_ - The shop of powerful computers.',
            '#',
            '# _DESCRIPTION_SH_',
            '#',
            '# @package    _PACKAGE_',
            '# @license    _LICENSE_',
            '# @copyright  _COPYRIGHTS_',
            '# @link       _LINK_'
        ];
    }

    /**
     * Valid header for XML files.
     *
     * @return array
     */
    protected function _setValidHeaderXML()
    {
        return [
            '<?xml version="1.0" encoding="UTF-8" ?>',
            '<!--',
            '    _PACKAGE_ - The shop of powerful computers.',
            '',
            '    _DESCRIPTION_XML_',
            '',
            '    @package    _PACKAGE_',
            '    @license    _LICENSE_',
            '    @copyright  _COPYRIGHTS_',
            '    @link       _LINK_'
        ];
    }

    /**
     * Valid header for JS files.
     *
     * @return array
     */
    protected function _setValidHeadJS()
    {
        return [
            '/**',
            ' * _PACKAGE_ - The shop of powerful computers.',
            ' *',
            ' * _DESCRIPTION_JS_',
            ' *',
            ' * @package    _PACKAGE_',
            ' * @license    _LICENSE_',
            ' * @copyright  _COPYRIGHTS_',
            ' * @link       _LINK_'
        ];
    }

    /**
     * Valid header for LESS files.
     *
     * @return array
     */
    protected function _setValidHeadLess()
    {
        return [
            '//',
            '// _PACKAGE_ - The shop of powerful computers.',
            '//',
            '// _DESCRIPTION_LESS_',
            '//',
            '// @package    _PACKAGE_',
            '// @license    _LICENSE_',
            '// @copyright  _COPYRIGHTS_',
            '// @link       _LINK_'
        ];
    }

    /**
     * Valid header for SQL files.
     *
     * @return array
     */
    protected function _setValidHeadSQL()
    {
        return [
            '--',
            '-- _PACKAGE_ - The shop of powerful computers.',
            '--',
            '-- _DESCRIPTION_SQL_',
            '--',
            '-- @package   _PACKAGE_',
            '-- @license   _LICENSE_',
            '-- @copyright _COPYRIGHTS_',
            '-- @link      _LINK_'
        ];
    }
}
