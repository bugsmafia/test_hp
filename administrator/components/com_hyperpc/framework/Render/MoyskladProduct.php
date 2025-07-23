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

namespace HYPERPC\Render;

use HYPERPC\Helper\CartHelper;

/**
 * Class MoyskladProduct
 *
 * @package     HYPERPC\Render
 * @property    \HYPERPC\Joomla\Model\Entity\MoyskladProduct $_entity
 *
 * @since       2.0
 */
class MoyskladProduct extends AbstractProduct
{
    /**
     * Render product configuration.
     *
     * @param   bool $excludeExternalParts
     *
     * @return  string
     *
     * @since   2.0
     */
    public function configuration(bool $excludeExternalParts = false)
    {
        return $this->hyper['helper']['render']->render('product/configuration_parts', [
            'product' => clone $this->_entity,
            'excludeExternalParts' => $excludeExternalParts
        ]);
    }

    /**
     * Get cart buttons.
     *
     * @param   string $tpl     Layout template.
     * @param   array $buttons  Default button for view.
     * @return  string
     *
     * @since   2.0
     */
    public function getCartBtn($tpl = 'button', $buttons = ['buy'])
    {
        $type = $tpl === 'teaser_button' ? 'product' : 'position';

        return $this->hyper['helper']['render']->render($type . '/cart/' . strtolower($tpl), [
            $type      => $this->_entity,
            'buttons'  => $buttons,
            'isInCart' => $this->_entity->isInCart(),
            'type'     => CartHelper::TYPE_POSITION,
            'itemKey'  => $this->_entity->getItemKey()
        ]);
    }

    /**
     * Get and render product cart button for add configurations to basket.
     *
     * @param   string $tpl     Layout template.
     * @return  string
     *
     * @since   2.0
     */
    public function cartBtnForConfigurator($tpl = 'configurator')
    {
        return $this->hyper['helper']['render']->render('product/cart/button_' . strtolower($tpl), [
            'isInCart' => $this->_entity->isInCart(),
            'product'  => $this->_entity,
            'type'     => CartHelper::TYPE_CONFIGURATION,
            'itemKey'  => $this->_entity->getItemKey()
        ]);
    }

    /**
     * Render full or teaser image.
     *
     * @param   bool    $isTeaser       Get image full or teaser.
     * @param   string  $tpl            Render template.
     * @param   bool    $linkToPage     Set link to product page.
     *
     * @return  string
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function image($isTeaser = true, $tpl = 'product/image', $linkToPage = true)
    {
        $images = [];
        if ($this->_entity->isFromStock() || $this->_entity->hasNonDefaultImageParts()) {
            $this->_entity->params->set('image_from_part', true);
            $maxImageHeight = $isTeaser ? $this->hyper['params']->get('product_img_teaser_height', 450) : 0;
            $image = $this->_entity->getConfigurationImage(0, $maxImageHeight);
            if (!empty($image)) {
                $images[] = $image;
            }
        } else {
            $imagePaths = $this->_entity->getImages($isTeaser);
            foreach ($imagePaths as $key => $image) {
                $resize = $this->_getResizeImage($image, $isTeaser);

                if ($resize) {
                    $images[$key] = $resize;
                }
            }
        }

        return $this->hyper['helper']['render']->render($tpl, [
            'images'     => $images,
            'isTeaser'   => $isTeaser,
            'linkToPage' => $linkToPage,
            'entity'     => $this->_entity
        ]);
    }

    /**
     * Get image path by param name
     *
     * @param   string $paramName
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getImagePath(string $paramName)
    {
        return $this->_entity->images->get($paramName, '', 'hpimagepath');
    }
}
