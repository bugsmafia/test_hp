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

use JBZoo\Utils\FS;
use Joomla\CMS\Filesystem\Folder;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldHPLogos
 *
 * @since   2.0
 */
class JFormFieldHPLogos extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPLogos';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.logos';

    /**
     * Allowed image ext.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_allowedImage = [
        'jpg', 'jpeg', 'png'
    ];

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        $images  = [];
        $folder  = ((string) $this->element['folder'] !== '') ? (string) $this->element['folder'] : 'logos';
        $path    = FS::clean(JPATH_ROOT . '/images/' . $folder, '/');

        if (!FS::isFile($path)) {
            Folder::create($path);
        }

        $files   = FS::ls($path);

        foreach ($files as $file) {
            if (in_array(FS::ext($file), self::$_allowedImage)) {
                $images[] = FS::clean($file, '/');
            }
        }

        return array_merge(parent::getLayoutData(), ['images' => $images]);
    }
}