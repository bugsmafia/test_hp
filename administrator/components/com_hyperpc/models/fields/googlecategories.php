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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldGoogleCategories
 *
 * @since 2.0
 */
class JFormFieldGoogleCategories extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'GoogleCategories';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Method to get google merchant categories for field.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $file = $this->_getFile();

        $data = [];
        $data[0]['value'] = 0;
        $data[0]['text']  = Text::sprintf('COM_HYPERPC_SELECT_CATEGORY_GOOGLE');

        foreach ($file as $line) {
            if ($line === '' || substr($line, 0, 1) === '#') {
                continue;
            }

            list($id, $categoryPath) = explode(' - ', $line);

            $lineParts = explode(' > ', $categoryPath);
            $data[$id]['value'] = $id;
            $data[$id]['text']  = str_repeat("-", count($lineParts)) . ' ' . end($lineParts);
        }

        return array_merge(parent::getOptions(), $data);
    }

    /**
     * Get google merchant categories list
     *
     * @return array
     *
     * @since  2.0
     */
    protected function _getFile()
    {
        $lang   = Factory::getApplication()->getLanguage();
        $locale = $lang->getTag();

        $filepath = JPATH_ROOT . '/tmp/google/';
        $filename = 'taxonomy-with-ids.' . $locale . '.txt';

        if (!File::exists($filepath.$filename)) {
            if (!Folder::exists($filepath)) {
                Folder::create($filepath);
            }

            $file = file('https://www.google.com/basepages/producttype/' . $filename);
            File::write($filepath.$filename, $file);
        } else {
            $file = file($filepath . $filename);
        }

        return !$file ? [] : $file;
    }
}
