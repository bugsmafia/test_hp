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

namespace HYPERPC\Joomla\Toolbar\Button;

use HYPERPC\App;
use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

defined('_JEXEC') or die('Restricted access');

/**
 * Class UpdateProductIndex
 *
 * @package HYPERPC\Joomla\Toolbar\Button
 *
 * @since   2.0
 */
class UpdateProductIndex extends ToolbarButton
{

    /**
     * Button type.
     *
     * @var    string
     *
     * @since   2.0
     */
    protected $_name = 'UpdateProductIndex';

    /**
     * Get the button.
     *
     * @param   string  $type
     * @param   string  $title
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function fetchButton($type = 'UpdateProductIndex', $title = '')
    {
        $app  = App::getInstance();
        $uniq = uniqid('toolbar-wrapper-');

        $app['helper']['assets']
            ->js('js:widget/admin/toolbar/update-product-index.js')
            ->widget('.' . $uniq, 'HyperPC.ToolbarUpdateProductIndex');

        $layout = new FileLayout('joomla.toolbar.update_product_index');
        $layout->setIncludePaths(array_merge(
            $layout->getDefaultIncludePaths(),
            [
                $app['path']->get('admin:layouts')
            ]
        ));

        return $layout->render([
            'uniq'  => $uniq,
            'title' => Text::_($title)
        ]);
    }

    /**
     * Get the button CSS Id
     *
     * @param   string  $type  The button type.
     * @param   string  $name  The name of the button.
     *
     * @return  string  Button CSS Id
     *
     * @since   2.0
     */
    public function fetchId($type = 'UpdateProductIndex', $name = '')
    {
        return $this->_parent->getName() . '-' . Str::slug($name);
    }
}
