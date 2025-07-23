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

use Joomla\CMS\Factory;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Path;

/**
 * Class OpengraphHelper
 *
 * @package HYPERPC\Helper
 */
class OpengraphHelper extends AppHelper
{
    private const DEFAULT_OG_IMAGE_PATH = 'media/com_hyperpc/images/opengraph/default.jpg';

    /**
     * Set default metadata to the document.
     *
     * This method doesn't change any already set meta data.
     *
     * @return  void
     */
    public function setDefaultMetadata(): void
    {
        $document = Factory::getApplication()->getDocument();

        $document->setMetaData('og:site_name', $this->hyper['app']->get('sitename'), 'property');

        if (empty($document->getMetaData('og:url', 'property'))) {
            $uri = Uri::getInstance();
            $current = Uri::current();

            if ($uri->hasVar('start')) { // Allow pagination
                $queryString = Uri::buildQuery([
                    'start' => $uri->getVar('start')
                ]);

                $current .= '?' . $queryString;
            }

            $this->setUrl($current);
        }

        if (empty($document->getMetaData('og:type', 'property'))) {
            $this->setType('website');
        }

        if (empty($document->getMetaData('og:title', 'property'))) {
            $this->setTitle($document->getTitle());
        }

        if (empty($document->getMetaData('og:description', 'property'))) {
            $this->setDescription($document->getDescription());
        }

        if (empty($document->getMetaData('og:image', 'property'))) {
            $image = Uri::root() . static::DEFAULT_OG_IMAGE_PATH;

            $document
                ->setMetaData('og:image', $image, 'property')
                ->setMetaData('twitter:card', 'summary_large_image')
                ->setMetaData('twitter:image', $image);
        }
    }

    /**
     * Set description meta tags to the document.
     *
     * @param   string $description
     *
     * @return  static
     */
    public function setDescription(string $description): static
    {
        Factory::getApplication()->getDocument()
            ->setMetaData('og:description', $description, 'property')
            ->setMetaData('twitter:description', $description);

        return $this;
    }

    /**
     * Set image meta tags to the document.
     *
     * If the image file is not found, no meta tags will be set.
     *
     * @param   string $imagePath relative path to the image
     *
     * @return  static
     */
    public function setImage(string $imagePath): static
    {
        $uri = Uri::getInstance($imagePath);
        if (!empty($uri->getHost() && $uri->getHost() !== Uri::getInstance()->getHost())) {
            return $this; // Ignore external links
        }

        $imagePath = $uri->getPath();

        try {
            $image = new Image(Path::clean(JPATH_ROOT . '/' . $imagePath));
        } catch (\Throwable $th) {
            return $this;
        }

        $mtime = substr(\filemtime($image->getPath()), -3);
        $url = Uri::root() . ltrim($imagePath, '/') . '?' . $mtime;

        $document = Factory::getApplication()->getDocument();

        $orientation = $image->getOrientation();
        $twitterCardType = $orientation === Image::ORIENTATION_LANDSCAPE ? 'summary_large_image' : 'summary';

        $document
            ->setMetaData('og:image', $url, 'property')
            ->setMetaData('twitter:card', $twitterCardType)
            ->setMetaData('twitter:image', $url);

        return $this;
    }

    /**
     * Set title meta tags to the document.
     *
     * @param   string $title
     *
     * @return  static
     */
    public function setTitle(string $title): static
    {
        Factory::getApplication()->getDocument()
            ->setMetaData('og:title', $title, 'property')
            ->setMetaData('twitter:title', $title);

        return $this;
    }

    /**
     * Set og type meta tag to the document.
     *
     * @param   string $type
     *
     * @return  static
     */
    public function setType(string $type): static
    {
        Factory::getApplication()->getDocument()
            ->setMetaData('og:type', $type, 'property');

        return $this;
    }

    /**
     * Set og url meta tag to the document.
     *
     * @param   string $type
     *
     * @return  static
     */
    public function setUrl(string $url): static
    {
        Factory::getApplication()->getDocument()
            ->setMetaData('og:url', $url, 'property');

        return $this;
    }
}
