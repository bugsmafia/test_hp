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

namespace HYPERPC\Helper;

use JBZoo\Utils\FS;
use JBZoo\Utils\Url;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class MicrodataHelper
 *
 * @package     HYPERPC\Helper
 *
 * @since       2.0
 */
class MicrodataHelper extends AppHelper
{

    /**
     * Get JSON-LD for schema.org.
     *
     * @param ProductMarker|PartMarker|MoyskladService $entity
     * @param OptionMarker|null $option
     *
     * @return   string
     *
     * @throws   \JBZoo\Image\Exception
     * @throws   \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getEntityMicrodata(Entity $entity, $option = null)
    {
        $params = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => htmlspecialchars($entity->name, ENT_COMPAT),
            'url' => Url::pathToUrl($entity->getViewUrl())
        ];

        $price = $entity->getListPrice();

        $offer = [
            '@type' => 'Offer',
            'itemCondition' => 'https://schema.org/NewCondition',
            'priceCurrency' => $this->hyper['helper']['money']->getCurrencyIsoCode($price)
        ];

        if ($entity instanceof Stockable) {
            $offer['availability'] = 'http://schema.org/' . $entity->getAvailability();
        }

        if ($entity instanceof ProductMarker) {
            $imageList = $entity->getImages(true);
            $imagePath = array_shift($imageList);

            $offer['price'] = $entity->getConfigPrice()->val();

            $params['brand'] = strtoupper($this->hyper['params']->get('site_context'));
            $params['productID'] = preg_replace('/^(\w+)-(\d+)(-.+)?/', '${1}-${2}', $entity->getItemKey()); // base product itemkey
            $params['description'] = HTMLHelper::_('content.prepare', strip_tags($entity->description));
            $params['image'] = !empty($imagePath) ? Url::pathToUrl($imagePath) : null;
            $params['offers'] = [$offer];
        } elseif ($entity instanceof PartMarker) {
            // TODO: get brand for parts

            $vendorCode = $entity->vendor_code;
            $price = $entity->getListPrice();

            $imgWidth = $this->hyper['params']->get('catalog_part_img_width', HP_PART_IMAGE_THUMB_WIDTH);
            $imgHeight = $this->hyper['params']->get('catalog_part_img_height', HP_PART_IMAGE_THUMB_HEIGHT);
            $imagePath = $entity->getExportImage();

            $itemKey = preg_replace('/^(\w+)-(\d+)(-.+)?/', '${1}-${2}', $entity->getItemKey()); // base part itemkey
            $option = $option ?? $entity->option ?? $entity->getDefaultOption();
            if ($option instanceof OptionMarker && $option->id) {
                $itemKey .= '-' . $option->id;
                $price = $option->getListPrice();
                $vendorCode = $option->vendor_code;

                $params['name'] .= ' (' . htmlspecialchars($option->name, ENT_COMPAT) . ')';

                $offer['availability'] = $option->getAvailability();

                if ($option->id !== $entity->getDefaultOptionId()) {
                    $params['url'] .= '/' . $option->alias;
                }

                $optionImagePath = $option->params->get('image', '', 'hpimagepath');
                if (!empty($optionImagePath)) {
                    $imagePath = $optionImagePath;
                }
            }

            $params['productID'] = $itemKey;
            $params['description'] = strip_tags($entity->getParams()->get('short_desc'));

            $image = $entity->getRender()->image($imgWidth, $imgHeight, 'hp_part_img', $imagePath);

            if (array_key_exists('thumb', $image)) {
                $cacheImg = $image['thumb'];
                $params['image'] = $cacheImg->getUrl();
            }

            $offer['price'] = $price->val();

            if ($vendorCode) {
                $params['sku'] = $entity->vendor_code;
            }

            if ($entity->isForRetailSale()) {
                $params['offers'] = [$offer];
            }
        }

        return implode(PHP_EOL, [
            '<script type="application/ld+json">',
            json_encode($params, JSON_UNESCAPED_SLASHES),
            '</script>']);
    }

    /**
     * Get JSON-LD for schema.org Organization.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOrganizationMicrodata()
    {
        $config = $this->hyper['params'];

        $result = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $config->get('microdata_organization_name', 'HYPERPC'),
            'url' => Uri::root(),
            'description' => $config->get('microdata_organization_description'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $config->get('microdata_organization_address'),
                'addressLocality' => $config->get('microdata_organization_locality'),
                'addressRegion' => $config->get('microdata_organization_region'),
                'postalCode' => $config->get('microdata_organization_postal_code'),
                'addressCountry' => $config->get('microdata_organization_country')
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Shop',
                'telephone' => $config->get('microdata_organization_phone')
            ]
        ];

        $pathToLogo = trim($config->get('microdata_organization_logo', ''));
        if (!empty($pathToLogo) && FS::isFile(JPATH_ROOT . $pathToLogo)) {
            $logoUrl = Url::pathToUrl($pathToLogo);
            $result['logo'] = $logoUrl;
            $result['image'] = $logoUrl;
        }

        return implode(PHP_EOL, [
            '<script type="application/ld+json">',
            json_encode($result, JSON_UNESCAPED_SLASHES),
            '</script>'
        ]);
    }
}
