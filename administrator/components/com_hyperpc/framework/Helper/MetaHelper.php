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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Helper;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class MetaHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MetaHelper extends AppHelper
{

    /**
     * Setup page meta data.
     *
     * @param   Entity $entity
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function setup(Entity $entity)
    {
        $metaTitle = $entity->metadata->get('meta_title', '');

        if (empty($metaTitle) && property_exists($entity, 'title')) {
            $metaTitle = $entity->get('title', '');
        }

        if (empty($metaTitle)) {
            if ($entity instanceof OptionMarker) {
                $metaTitle = $entity->getPageTitle();
            } elseif (property_exists($entity, 'name')) {
                $metaTitle = $entity->get('name', '');
            }
        }

        $metaTitle = HTMLHelper::_('content.prepare', $metaTitle);

        $description = $entity->metadata->get('meta_desc', '');

        if (empty($description)) {
            if ($entity instanceof ProductMarker) {
                $description = $entity->get('description', '');
            } elseif ($entity instanceof Position) {
                $description = $entity->getParams()->get('short_desc', '');
            } elseif ($entity instanceof OptionMarker) {
                $description = $entity->getShortDescription();
            }
        }

        $description = HTMLHelper::_('content.prepare', $description);

        /** @var MacrosHelper */
        $macrosHelper = $this->hyper['helper']['macros'];

        $macrosHelper->setData(
            array_merge(
                $entity->getArray(),
                $this->renderAvailability($entity)
            )
        );

        $this->hyper['doc']
            ->setTitle($macrosHelper->text($metaTitle))
            ->setDescription($macrosHelper->text(strip_tags($description)))
            ->setMetaData('keywords', $macrosHelper->text($entity->metadata->get('meta_keys', '')));

        $robots = $entity->metadata->get('robots', '');
        if ($robots !== '') {
            $this->hyper['doc']->setMetaData('robots', $robots);
        }
    }

    /**
     * Get array with availability label for snippet in meta tags
     *
     * @param  Entity $entity
     *
     * @return array
     *
     * @since  2.0
     */
    public function renderAvailability($entity)
    {
        if (!$entity instanceof Stockable) {
            return [];
        }

        $availability = $entity->getAvailability();

        if (isset($entity->option) && $entity->option instanceof OptionMarker && !empty($entity->option->id)) {
            $availability = $entity->option->getAvailability();
        }

        $availability = Text::_('COM_HYPERPC_AVAILABILITY_LABEL_' . $availability);

        return ['availability' => $availability];
    }
}
