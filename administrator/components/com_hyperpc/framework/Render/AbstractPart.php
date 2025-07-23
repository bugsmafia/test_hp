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

use HYPERPC\Helper\ImageHelper;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\Folder;

/**
 * Class AbstractPart
 *
 * @package     HYPERPC\Render
 *
 * @since       2.0
 */
abstract class AbstractPart extends Render
{
    const CACHE_GROUP = 'hp_part_img';
    const GALLERY_MAX_ITEMS = 20;

    /**
     * Get entity image path.
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getEntityImage();

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
     * Render part gallery.
     *
     * @param   string $gallery         Gallery param name.
     * @param   int $maxWidth           Thumb image max width (px).
     * @param   int $maxHeight          Thumb image max height (px).
     * @param   string $tpl             Layout template name.
     *
     * @return  null|string
     *
     * @throws  \Exception
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function gallery(string $gallery, int $maxWidth, int $maxHeight = 0, $tpl = 'default')
    {
        $path = $this->_getImagePath($gallery);
        $dir  = Path::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $path);

        if (empty($path) || !Folder::exists($dir)) {
            return null;
        }

        $entityPrefix = strtolower((new \ReflectionClass($this->_entity))->getShortName());

        $cacheGroup = static::CACHE_GROUP . DIRECTORY_SEPARATOR . $entityPrefix . '_' . $this->_entity->get('id') . '_' . $gallery;

        /** @var ImageHelper $imageHelper */
        $imageHelper = $this->hyper['helper']['image'];

        $files = Folder::files($dir);
        $images = [];
        $i = 0;
        foreach ($files as $fileName) {
            $thumb = $imageHelper->getThumb($dir . DIRECTORY_SEPARATOR . $fileName, $maxWidth, $maxHeight, $cacheGroup);
            if (empty($thumb)) {
                continue;
            }

            $images[] = $thumb;
            if (++$i === static::GALLERY_MAX_ITEMS) { // Max limit for gallery.
                break;
            }
        }

        //  Rename gallery template for view part from popup configurator.
        if ($tpl === 'default' && $this->hyper['input']->get('tmpl') === 'component') {
            $tpl = 'component';
        }

        return $this->hyper['helper']['render']->render('part/gallery/' . $tpl, [
            'images'    => $images,
            'wrapperId' => $gallery,
            'entity'    => $this->_entity
        ]);
    }

    /**
     * Get image in given sizes.
     *
     * @param   int $maxWidth           Image max width.
     * @param   int $maxHeight          Image max height.
     * @param   string $cacheGroup      Cache group.
     * @param   string $imagePath       Path to custom image
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function image($maxWidth = 0, $maxHeight = 0, $cacheGroup = self::CACHE_GROUP, $imagePath = '')
    {
        $imagePath = empty($imagePath) ? $this->getEntityImage() : $imagePath;

        /** @var ImageHelper $imageHelper */
        $imageHelper = $this->hyper['helper']['image'];

        $image = $imageHelper->getThumb($imagePath, $maxWidth, $maxHeight, $cacheGroup);

        if (empty($image)) {
            return $imageHelper->getThumb(
                $imageHelper->getPlaceholderPath(),
                $maxWidth,
                $maxHeight
            );
        }

        return $image;
    }
}
