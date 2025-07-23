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

namespace HYPERPC\Helper\Traits;

use Cake\Utility\Hash;

/**
 * Trait Templater
 *
 * @property    array $_templates
 *
 * @package     HYPERPC\Helper\Traits
 *
 * @since 2.0
 */
trait Templater
{

    /**
     * Adds a class and returns a unique list either in array or space separated.
     *
     * @param array|string  $input      The array or string to add the class to.
     * @param array|string  $newClass   The new class or classes to add.
     * @param string        $useIndex   If you are inputting an array with an element other than default of 'class'.
     *
     * @return array|string
     *
     * @since 2.0
     */
    public function addClass($input, $newClass, $useIndex = 'class')
    {
        //  NOOP.
        if (empty($newClass)) {
            return $input;
        }

        if (is_array($input)) {
            $class = Hash::get($input, $useIndex, []);
        } else {
            $class = $input;
            $input = [];
        }

        //  Convert and sanitise the inputs.
        if (!is_array($class)) {
            if (is_string($class) && !empty($class)) {
                $class = explode(' ', $class);
            } else {
                $class = [];
            }
        }

        if (is_string($newClass)) {
            $newClass = explode(' ', $newClass);
        }

        $class = array_unique(array_merge($class, $newClass));

        $input = Hash::insert($input, $useIndex, $class);

        return $input;
    }

    /**
     * Format html element.
     *
     * @param string    $name
     * @param array     $data
     *
     * @return string
     *
     * @since 2.0
     */
    public function format($name, array $data = [])
    {
        if (!isset($this->_templates[$name])) {
            throw new \RuntimeException("Cannot find template named '$name'.");
        }

        $data['tag'] = $name;

        list($template, $placeholders) = $this->_templates[$name];

        $replace = [];
        foreach ($placeholders as $placeholder) {
            $replacement = isset($data[$placeholder]) ? $data[$placeholder] : null;
            if (is_array($replacement)) {
                $replacement = implode('', $replacement);
            }
            $replace[] = $replacement;
        }

        return vsprintf($template, $replace);
    }

    /**
     * Compile templates into a more efficient printf() compatible format.
     *
     * @return void
     *
     * @since 2.0
     */
    protected function _compileTemplates()
    {
        $templates = array_keys($this->_templates);
        foreach ($templates as $name) {
            $template = $this->_templates[$name];

            if ($template === null) {
                $this->_templates[$name] = [null, null];
            }

            $template = str_replace('%', '%%', $template);
            preg_match_all('#\{\{([\w\d\._]+)\}\}#', $template, $matches);
            $this->_templates[$name] = [
                str_replace($matches[0], '%s', $template),
                $matches[1]
            ];
        }
    }
}
