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

use JBZoo\Image\Image;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\Folder;

/**
 * Class ImageHelper
 *
 * @package HYPERPC\Helper
 */
class ImageHelper extends AppHelper
{
    public const SCALE_FIT = 'fit';
    public const SCALE_CROP = 'crop';

    /**
     * 1x1 transparent gif base64 encoded
     */
    public const TRANSPARENT_PIXEL = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    private const POSITION_PLACEHOLDER_PATH = '/media/hyperpc/img/image-placeholder.png';

    private const CACHE_GROUP = 'img_thumbs';

    /**
     * Allowed image ext.
     *
     * @var     array
     */
    protected static $_allowedImgExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Get position placeholder image path with leading slash.
     * If width is 0 and height is 0 returns original image path
     *
     * @param   int $width
     * @param   int $height
     *
     * @return  string
     *
     * @throws  \LogicException
     */
    public function getPlaceholderPath(int $width = 0, int $height = 0, string $mode = self::SCALE_FIT): string
    {
        if ($width < 0 || $height < 0) {
            throw new \LogicException('Image width or height cannot be negative');
        }

        if ($width === 0 && $height === 0) {
            return self::POSITION_PLACEHOLDER_PATH;
        }

        try {
            $thumb = $this->getThumb(self::POSITION_PLACEHOLDER_PATH, $width, $height, mode:$mode);
        } catch (\Throwable $th) {
            return self::POSITION_PLACEHOLDER_PATH;
        }

        if (key_exists('thumb', $thumb) && $thumb['thumb'] instanceof Image) {
            return Uri::getInstance($thumb['thumb']->getUrl())->getPath();
        }

        return self::POSITION_PLACEHOLDER_PATH;
    }

    /**
     * Resize and cache image.
     *
     * @param   string $imageSource Path to source file
     * @param   int $width          Image width.
     * @param   int $height         Image height.
     * @param   string $cacheGroup  Cache group.
     * @param   int $mode           Resize mode (fit to dimensions or crop after resize). Use the class constants only
     *
     * @return  array [
     *  'original' => JBZoo\Image\Image,
     *  'thumb' => JBZoo\Image\Image
     * ]
     *
     * @throws  \Exception
     * @throws  \JBZoo\Image\Exception
     * @throws  \UnexpectedValueException
     *
     * @todo    Return DTO
     */
    public function getThumb(string $imageSource, int $width, int $height = 0, string $cacheGroup = self::CACHE_GROUP, string $mode = self::SCALE_FIT)
    {
        if ($width < 0 || $height < 0) {
            throw new \LogicException('Image width or height cannot be negative');
        }

        static $imageCache = [];
        $cacheKey = md5(implode(func_get_args()));
        if (key_exists($cacheKey, $imageCache)) {
            return $imageCache[$cacheKey];
        }

        $imageSource = $this->getFSFilePath($imageSource);

        if (!$this->isExistingImage($imageSource)) {
            $imageCache[$cacheKey] = [];

            return [];
        }

        try {
            $output = ['original' => new Image($imageSource)];
        } catch (\Throwable $th) {
            $imageCache[$cacheKey] = [];

            return [];
        }

        if ($width === 0 && $height === 0) { // no resize
            $output['thumb'] = clone $output['original'];
            $imageCache[$cacheKey] = $output;

            return $output;
        }

        $cacheFolder = $this->hyper['path']->get('cache:') . '/' . $cacheGroup;
        if (!is_dir($cacheFolder)) {
            Folder::create($cacheFolder);
        }

        $sourceFileName = pathinfo($imageSource, PATHINFO_FILENAME);
        $sourceFileExt  = pathinfo($imageSource, PATHINFO_EXTENSION);
        $thumbName      = $sourceFileName . '-' . $mode . '-' . $width . 'x' . $height . '.' . $sourceFileExt;
        $thumbFilePath  = $cacheFolder . '/' . $thumbName;
        if (is_file($thumbFilePath)) {
            $output['thumb'] = new Image($thumbFilePath);
        } else {
            $image = clone $output['original'];
            if ($width && $height) {
                if ($mode === self::SCALE_CROP) {
                    $image->thumbnail($width, $height);
                } else {
                    $image->bestFit($width, $height);
                }
            } elseif (!$width) {
                $image->fitToHeight($height);
            } elseif (!$height) {
                $image->fitToWidth($width);
            }
            $output['thumb'] = $image->saveAs($thumbFilePath);
        }

        $imageCache[$cacheKey] = $output;

        return $output;
    }

    /**
     * Checks if source is the existing file with allowed extention
     *
     * @param   string $imageSource path to file
     *
     * @return  bool
     */
    public function isExistingImage(string $imageSource)
    {
        $imageSource = $this->getFSFilePath($imageSource);

        if (!is_file($imageSource)) {
            return false;
        }

        $ext = pathinfo($imageSource, PATHINFO_EXTENSION);

        if (!in_array($ext, static::$_allowedImgExt)) {
            return false;
        }

        return true;
    }

    /**
     * Is the passed path a path to placeholder image or resized variant of it?
     *
     * @param   string $path
     *
     * @return  bool
     */
    public function isPlaceholder(string $path): bool
    {
        $filePath = Path::clean(JPATH_ROOT . '/' . Uri::getInstance($path)->getPath());

        $placeholderFilePath = Path::clean(JPATH_ROOT . self::POSITION_PLACEHOLDER_PATH);

        if ($filePath === $placeholderFilePath) {
            return true;
        }

        $placeholderName = pathinfo($placeholderFilePath, PATHINFO_FILENAME);
        $placeholderExt = pathinfo($placeholderFilePath, PATHINFO_EXTENSION);

        $preg = '/' . self::CACHE_GROUP . '\\' . \DIRECTORY_SEPARATOR . $placeholderName . '-\w+-\d+x\d+\.' . $placeholderExt . '/';

        return (bool) preg_match($preg, $filePath);
    }

    /**
     * Get filesystem path
     *
     * @param   string $path
     *
     * @return  string
     */
    private function getFSFilePath(string $path): string
    {
        if (strpos($path, JPATH_ROOT) === false) {
            $path = JPATH_ROOT . '/' . Uri::getInstance($path)->getPath();
        }

        return Path::clean($path);
    }
}
