<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace HYPERPC\Joomla\Router\Rules;

use Joomla\CMS\Component\Router\Rules\MenuRules;

/**
 * @package     HYPERPC\Joomla\Routes\Rules
 *
 * @since       2.0
 */
class OrderRules extends MenuRules
{

    /**
     * Dummymethod to fullfill the interface requirements.
     *
     * @param   array  &$segments  The URL segments to parse.
     * @param   array  &$vars      The vars that result from the segments.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function parse(&$segments, &$vars)
    {
        if (@$vars['view'] === 'order' && count($segments) == 2) {
            $vars['id'] = $segments[0];
            $vars['token'] = $segments[1];
        }
    }

    /**
     * Dummymethod to fullfill the interface requirements.
     *
     * @param   array  &$query     The vars that should be converted.
     * @param   array  &$segments  The URL segments to create.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function build(&$query, &$segments)
    {
        if (@$query['view'] === 'order') {
            if (array_key_exists('id', $query)) {
                $segments[] = $query['id'];
                $segments[] = $query['token'];
                unset($query['id'], $query['token']);
            }

            unset($query['view']);
        }
    }
}
