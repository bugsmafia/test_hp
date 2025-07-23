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

namespace HYPERPC\Printer;

use HYPERPC\Container;
use Joomla\CMS\Uri\Uri;

/**
 * Class PrinterAbstract
 *
 * @package HYPERPC\Printer
 *
 * @since 2.0
 */
abstract class PrinterAbstract extends Container
{

    /**
     * Name of printer.
     *
     * @var string
     *
     * @since 2.0
     */
    protected $_name;

    /**
     * Render output html.
     *
     * @return string
     *
     * @since 2.0
     */
    abstract public function html();

    /**
     * Get page title.
     *
     * @return string
     *
     * @since 2.0
     */
    abstract public function getPageTitle();

    /**
     * Get site html link.
     *
     * @return string
     *
     * @since 2.0
     */
    public function getSiteLink()
    {
        $href = Uri::root();

        $langCode = $this->hyper->getLanguageCode();
        $defaultLangCode = $this->hyper->getDefaultLanguageCode();

        if ($langCode !== $defaultLangCode) {
            $href .= explode('-', $langCode)[0];
        }

        $attrs = [
            'href'  => $href,
            'title' => $href,
            'class' => 'hp-pdf-site-link hp-link'
        ];

        return '<a ' . $this->hyper['helper']['html']->buildAttrs($attrs) . '>' . $href . '</a>';
    }
}
