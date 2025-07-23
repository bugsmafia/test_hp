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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;

/**
 * Class HyperPcTablePositions
 *
 * @since   2.0
 */
class HyperPcTablePositions extends Table
{

    /**
     * HyperPcTablePositions constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_POSITIONS, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Overloaded bind function.
     *
     * @param   array|object $array
     * @param   string $ignore
     * @return  bool
     *
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($array, $ignore = '')
    {
        if (key_exists('images', $array)) {
            $content = new JSON($array['images']);
            $array['images'] = $content->write();
        } else {
            $array['images'] = '{}';
        }

        if (key_exists('barcodes', $array)) {
            $barcodes = $array['barcodes'];
            if (is_string($barcodes)) {
                $barcodes = new JSON(preg_replace('/\p{Zs}/u', '', $barcodes));
            } else {
                $barcodes = new JSON($barcodes);
            }

            $array['barcodes'] = $barcodes->write();
        }

        if (!key_exists('description', $array)) {
            $array['description'] = '';
        }

        if (isset($array['list_price']) && $array['list_price'] instanceof Money) {
            $array['list_price'] = $array['list_price']->val();
        }

        if (isset($array['sale_price']) && $array['sale_price'] instanceof Money) {
            $array['sale_price'] = $array['sale_price']->val();
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Override check function.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function check()
    {
        $this->alias = trim($this->alias);
        if ($this->alias === '') {
            $this->alias = $this->uuid;
        }

        return true;
    }

    /**
     * Method to store a node in the database table.
     *
     * @param   bool $updateNulls
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function store($updateNulls = false)
    {
        // Verify that the alias is unique
        $table = Table::getInstance('Positions');
        if ($table->load(['alias' => $this->alias, 'product_folder_id' => $this->product_folder_id]) &&
            ($table->id != $this->id || $this->id == 0)) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_ARTICLE_UNIQUE_ALIAS'));
            return false;
        }

        return parent::store($updateNulls);
    }
}
