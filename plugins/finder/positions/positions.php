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
 * @author      Roman Evsyukov
 */

use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\FinderIndexer\Adapter as HyperPCFinderIndexerAdapter;

//  no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Smart Search adapter for "Moy Sklad" positions.
 *
 * @since 2.0
 */
class PlgFinderPositions extends HyperPCFinderIndexerAdapter
{

    /**
     * Name of indexer node.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_name = 'position';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $state_field = 'state';

    /**
     * Build parts summary.
     *
     * @param   Position $entity
     * @return  string
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getEntitySummary(Entity $entity)
    {
        $positionHelper = $this->hyper['helper']['position'];

        $configurationDesc = [];
        if ($entity->isProduct()) {
            /** @var MoyskladProduct $product */
            $product = $positionHelper->expandToSubtype($entity);
            $parts   = $product->getConfigParts(false);
            if (count($parts)) {
                foreach ($parts as $part) {
                    $configurationDesc[] = $part->getName();
                }
            }

            $description = $product->description;
            if (!empty($configurationDesc)) {
                $lang = Factory::getLanguage();
                $lang->load('plg_' . $this->_type . '_' . $this->_name, __DIR__, 'ru-RU', true);
                $description = Text::sprintf(
                    'PLG_FINDER_POSITIONS_ENTITY_SUMMARY',
                    $product->description,
                    implode(', ', $configurationDesc)
                );
            }

            return $description;
        }  elseif ($entity->isPart()) {
            /** @var MoyskladPart $part */
            $part    = $positionHelper->expandToSubtype($entity);
            $options = $part->getOptions();
            if (count($options)) {
                foreach ($options as $option) {
                    $configurationDesc[] = implode(PHP_EOL, [
                        $entity->name . ' (' . $option->name . '): ' . $option->getParams()->get('short_desc')
                    ]);
                }
            }

            return implode(PHP_EOL, [
                $entity->getParams()->get('short_desc'),
                implode(PHP_EOL, $configurationDesc)
            ]);
        }

        return $entity->getParams()->get('short_desc');
    }
}
