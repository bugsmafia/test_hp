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

namespace HYPERPC\Helper;

use HYPERPC\App;
use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use Cake\Utility\Hash;
use Joomla\CMS\Uri\Uri;
use JBZoo\Utils\Filter;
use Cake\Utility\Inflector;
use HYPERPC\Html\Render\Render;
use HYPERPC\Helper\Traits\Templater;
use Joomla\CMS\HTML\HTMLHelper as JHTMLHelper;

/**
 * Class HtmlHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class HtmlHelper extends AppHelper
{

    use Templater;

    const SVG_DEFAULT_HEIGHT_ICON = 20;
    const SVG_DEFAULT_WIDTH_ICON  = 20;

    /**
     * Hold HTML Render object.
     *
     * @var     Render
     *
     * @since   2.0
     */
    protected $_htmlRender;

    /**
     * Templates.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_templates = [
        'icon'  => '<span {{attrs}}></span>',
        'link'  => '<a {{attrs}}>{{content}}</a>',
        'image' => '<img src="{{src}}" {{attrs}} />',
        'tag'   => '<{{tag}}{{attrs}}>{{content}}</{{tag}}>'
    ];

    /**
     * Build html attributes.
     *
     * @param   array $attrs
     *
     * @return  string
     *
     * @since   2.0
     */
    public function buildAttrs(array $attrs)
    {
        return $this->_htmlRender->buildAttrs($attrs);
    }

    /**
     * Create icon.
     *
     * @param   string  $icon
     * @param   array   $options
     *
     * @return  string
     *
     * @since   2.0
     */
    public function icon($icon, array $options = [])
    {
        $icon = Str::low($icon);
        if ($icon) {
            $classes = [];
        } else {
            $classes = ['tm-icon-dummy'];
        }
        $options = $this->addClass($options, implode(' ', $classes));

        $options['uk-icon'] = 'icon: ' . $icon;

        return $this->format('icon', ['attrs' => $this->buildAttrs($options)]);
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_htmlRender = new Render();
        $this->_initSvgIcons();
        $this->_compileTemplates();
    }

    /**
     * Create html link element.
     *
     * @param   string  $title
     * @param   array   $attrs
     *
     * @return  string
     *
     * @since   2.0
     */
    public function link($title, array $attrs = [])
    {
        $attrs = Hash::merge(['href' => null], $attrs);

        if (is_array($attrs['href'])) {
            $attrs['href'] = $this->hyper['helper']['route']->url($attrs['href']);
        }

        $attrs = $this->addClass($attrs, ['hp-link']);
        if (!array_key_exists('title', $attrs)) {
            $attrs['title'] = Str::clean($title);
        }

        $content = $title;
        if (array_key_exists('icon', $attrs)) {
            $content = implode(' ', [
                $this->icon($attrs['icon']),
                $content
            ]);
            unset($attrs['icon']);
        }

        return $this->format(__FUNCTION__, [
            'content' => $content,
            'attrs'   => $this->buildAttrs($attrs)
        ]);
    }

    /**
     * Prepare array list to html ul list.
     *
     * @param   array   $list
     * @param   string  $listType
     *
     * @return  string
     *
     * @since   2.0
     */
    public function arrayToList(array $list, $listType = 'ul')
    {
        $output = [];
        foreach ($list as $item) {
            $output[] = '<li>' . $item . '</li>';
        }

        return sprintf('<' . $listType . '>%s</' . $listType . '>', implode(PHP_EOL, $output));
    }

    /**
     * Render toggle can buy action button.
     *
     * @param   string|int  $value
     * @param   int|string  $i
     * @param   string      $key
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function canBuy($value, $i, $key)
    {
        if (!App::isDevUser()) {
            return $this->published($value);
        }

        return JHTMLHelper::_('jgrid.state', [
            1 => [
                $key . '.from_sale',
                'COM_HYPERPC_UNPUBLISH_FROM_SALE',
                'COM_HYPERPC_UNPUBLISH_FROM_SALE',
                '',
                true,
                'publish',
                'publish'
            ],
            0 => [
                $key . '.on_sale',
                'COM_HYPERPC_PUBLISH_TO_SALE',
                'COM_HYPERPC_PUBLISH_TO_SALE',
                '',
                true,
                'unpublish',
                'unpublish'
            ]
        ], $value, $i);
    }

    /**
     * Render published badge.
     *
     * @param   int $value
     *
     * @return  string
     *
     * @since   2.0
     */
    public function published($value)
    {
        switch ($value) {
            case 0:
                $icon = 'unpublish';
                break;
            case 2:
                $icon = 'archive';
                break;
            case -2:
                $icon = 'trash';
                break;
            default:
                $icon = 'publish';
        }

        return '<span class="icon-' . $icon . '"></span>';
    }

    /**
     * Create image html element.
     *
     * @param   string  $src        Image source.
     * @param   array   $options    Custom array options.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function image($src, array $options = [])
    {
        $options += [
            'class'    => null,
            'fullBase' => true,
            'setSize'  => true
        ];

        $isFullBse = Filter::bool($options['fullBase']);
        $setSize   = Filter::bool($options['setSize']);

        unset($options['fullBase'], $options['setSize']);

        $url  = $src;
        $path = FS::clean(JPATH_ROOT . '/' . $src, '/');
        if ($this->hyper['path']->isVirtual($src)) {
            $path = $this->hyper['path']->get($src);
            $url  = $this->hyper['path']->url($src, false);
        }

        if ($setSize === true) {
            if (FS::isFile($path)) {
                list ($width, $height) = getimagesize($path);
                $options['width']  = $width;
                $options['height'] = $height;
            }
        }

        if ($isFullBse === true) {
            $src = str_replace('/administrator', '', Uri::base()) . ltrim(FS::clean($url, '/'), '/');
        }

        $options = $this->addClass($options, 'hp-image');

        if (isset($options['title'])) {
            $options['alt'] = $options['title'];
        }

        return $this->format(__FUNCTION__, [
            'src'   => $src,
            'attrs' => $this->buildAttrs($options)
        ]);
    }

    /**
     * Render svg icon.
     *
     * @param   string      $icon
     * @param   int|string  $width
     * @param   int|string  $height
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function svgIcon($icon, $width = self::SVG_DEFAULT_WIDTH_ICON, $height = self::SVG_DEFAULT_HEIGHT_ICON)
    {
        $templateKey = 'svg_' . Inflector::underscore($icon);
        if (array_key_exists($templateKey, $this->_templates)) {
            return $this->format($templateKey, ['width' => Filter::int($width), 'height' => Filter::int($height)]);
        }

        return null;
    }

    /**
     * Create custom tag.
     *
     * @param   string  $name Tag name
     * @param   string  $text
     * @param   array   $options
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function tag($name, $text = null, array $options = [])
    {
        if (empty($name)) {
            return $text;
        }

        if (isset($options['escape']) && $options['escape']) {
            $text = Str::clean($text);
            unset($options['escape']);
        }

        return $this->format($name, [
            'content' => $text,
            'attrs'   => $this->buildAttrs($options)
        ]);
    }

    /**
     * Initialize svg icons.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function _initSvgIcons()
    {
        $path = $this->hyper['path']->get('admin:config/svg_icons.php');
        if (is_file($path)) {
            /** @noinspection PhpIncludeInspection */
            $svgTemplates = require_once $path;
            foreach ((array) $svgTemplates as $svgIcon => $svgTemplate) {
                $this->_templates['svg_' . Inflector::underscore($svgIcon)] = $svgTemplate;
            }
        }
    }
}
