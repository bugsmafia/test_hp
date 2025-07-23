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

namespace HYPERPC\Render;

use JBZoo\Image\Image;
use HYPERPC\Helper\ImageHelper;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\Folder;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class AbstractProduct
 *
 * @package     HYPERPC\Render
 *
 * @property    ProductMarker $_entity
 *
 * @since       2.0
 */
abstract class AbstractProduct extends Render
{
    const CACHE_GROUP = 'hp_product_img';
    const GALLERY_MAX_ITEMS = 20;

    /**
     * Render product image.
     *
     * @param   bool    $isTeaser       Get image full or teaser.
     * @param   string  $tpl            Render template.
     * @param   bool    $linkToPage     Set link to product page.
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function image($isTeaser = true, $tpl = 'product/image', $linkToPage = true);

    /**
     * Get image path by param name
     *
     * @param   string
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract protected function _getImagePath(string $paramName);

    /**
     * Resize any image for product.
     *
     * @param   string $pathToImg       Path to image file
     * @param   string|int $width       Thumb image width (px).
     * @param   string|int $height      Thumb image height (px).
     *
     * @return  Image|null
     *
     * @throws  \Exception
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function customSizeImage(string $pathToImg, $width, $height)
    {
        /** @var ImageHelper $imageHelper */
        $imageHelper = $this->hyper['helper']['image'];
        $image = $imageHelper->getThumb($pathToImg, $width, $height, $this->_entity->getCacheKey());

        return $image['thumb'] ?? null;
    }

    /**
     * Render product gallery.
     *
     * @param   string $gallery         Gallery param name.
     * @param   string|int $width       Thumb image width (px).
     * @param   string|int $height      Thumb image height (px).
     * @param   string $tpl             Layout template name.
     *
     * @return  null|string
     *
     * @throws  \Exception
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function gallery($gallery, $width = '', $height = '', $tpl = 'gallery')
    {
        $path = $this->_getImagePath($gallery);
        $dir  = Path::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $path);

        if (empty($path) || !Folder::exists($dir)) {
            return null;
        }

        $galleryCacheGroup = $this->_entity->getCacheKey() . DIRECTORY_SEPARATOR . $gallery;

        /** @var ImageHelper $imageHelper */
        $imageHelper = $this->hyper['helper']['image'];

        $files = Folder::files($dir);
        $images = [];
        $i = 0;
        foreach ($files as $fileName) {
            $thumb = $imageHelper->getThumb($dir . DIRECTORY_SEPARATOR . $fileName, $width, $height, $galleryCacheGroup);
            if (empty($thumb)) {
                continue;
            }

            $images[] = $thumb;
            if (++$i === static::GALLERY_MAX_ITEMS) { // Max limit for gallery.
                break;
            }
        }

        return $this->hyper['helper']['render']->render('product/' . $tpl, [
            'images'    => $images,
            'wrapperId' => $gallery,
            'entity'    => $this->_entity
        ]);
    }

    /**
     * Render product logos.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function logos()
    {
        $output = [];
        $logos  = $this->_entity->params->get('logo', []);
        foreach ((array) $logos as $_logo) {
            $path = JPATH_ROOT . $_logo;
            if (is_file($path)) {
                $output[] = $this->hyper['helper']['render']->render('product/logos', ['logo' => $_logo]);
            }
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Get image width height.
     *
     * @param   string $imagePath   Full image path.
     * @return  array
     *
     * @since   2.0
     */
    protected function _getImgSize($imagePath)
    {
        list($width, $height) = getimagesize($imagePath);

        return [
            $this->hyper['params']->get('product_img_teaser_width', $width),
            $this->hyper['params']->get('product_img_teaser_height', $height)
        ];
    }

    /**
     * Get resize image object.
     *
     * @param   string $image       Path to image.
     * @param   bool $isFull        Use original or processed image.
     *
     * @return  null|Image
     *
     * @throws  \Exception
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    protected function _getResizeImage($image, $teaser = false)
    {
        /** @var ImageHelper $imageHelper */
        $imageHelper = $this->hyper['helper']['image'];

        $imgPath = Path::clean(JPATH_ROOT . '/' . $image);
        if (!$imageHelper->isExistingImage($imgPath)) {
            return null;
        }

        if (!$teaser) {
            return (new Image())
                ->loadFile($imgPath);
        }

        list($width, $height) = $this->_getImgSize($imgPath);
        if ($this->_entity->params->get('image_from_part', false)) {
            $width = 0;
        }

        $image = $imageHelper->getThumb($imgPath, $width, $height, $this->_entity->getCacheKey());

        return $image['thumb'] ?? null;
    }
}
