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

use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Html\Data\Category\Complectation;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * Class HyperPcModelStep_Configurator_Moysklad
 *
 * @property ProductFolderHelper $_helper
 * @method   ProductFolderHelper getHelper()
 *
 * @since    2.0
 */
class HyperPcModelStep_Configurator_Moysklad extends ModelAdmin
{
    /**
     * Hold category item
     *
     * @var   ProductFolder
     *
     * @since 2.0
     */
    protected $category;

    /**
     * Get category item
     *
     * @param  null|int $id
     *
     * @return mixed
     *
     * @since  2.0
     */
    public function getCategory($category_id = null)
    {
        if (!$this->category) {
            if ($category_id === null) {
                $category_id = $this->hyper['input']->getInt('category_id', 1);
            }

            $this->category = $this->getHelper()->findById($category_id);
        }

        return $this->category;
    }

    /**
     * Get category complectations
     *
     * @param   null|int $id
     *
     * @return  Complectation
     *
     * @throws  RuntimeException|Exception
     *
     * @since   2.0
     */
    public function getComplectations($id = null)
    {
        $category   = $this->getCategory($id);
        $product_id = $this->hyper['input']->getInt('product_id', 0);

        return new Complectation($category, $product_id);
    }

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->setHelper($this->hyper['helper']['productFolder']);
    }
}
