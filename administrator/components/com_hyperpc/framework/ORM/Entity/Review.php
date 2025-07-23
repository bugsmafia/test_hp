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

namespace HYPERPC\ORM\Entity;

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use HYPERPC\Joomla\Factory;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\ImageHelper;
use Joomla\CMS\Filesystem\Path;
use HYPERPC\Helper\ReviewHelper;
use Joomla\CMS\Filesystem\Folder;

/**
 * Review class.
 *
 * @property    int         $id
 * @property    float       $rating
 * @property    int         $order_id
 * @property    int         $item_id
 * @property    string      $context
 * @property    string      $file
 * @property    JSON        $params
 * @property    bool        $anonymous
 * @property    bool        $published
 * @property    string      $comment
 * @property    string      $limitations
 * @property    string      $virtues
 * @property    Date        $created_time
 * @property    int         $created_user_id
 * @property    int         $modified_time
 * @property    int         $modified_user_id
 *
 * @property    ReviewHelper  $_helper
 *
 * @method      ReviewHelper  getHelper()
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class Review extends Entity
{

    /**
     * Get admin (backend) edit url.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
        return $this->hyper['route']->build([
            'layout' => 'edit',
            'id'     => $this->id,
            'view'   => 'reviews'
        ]);
    }

    /**
     * Get diff date.
     *
     * @return  bool|\DateInterval
     *
     * @since   2.0
     */
    public function getDiffDate()
    {
        return Factory::getDate()->diff($this->created_time);
    }

    /**
     * Get review item entity.
     *
     * @return  ?Entity
     *
     * @since   2.0
     */
    public function getItem()
    {
        static $items = [];
        $itemHash = md5($this->item_id . ':' . $this->context);
        if (!key_exists($itemHash, $items)) {
            if ($this->context === HP_OPTION . '.position') {
                $items[$itemHash] = $this->hyper['helper']['position']->findById($this->item_id);
            }
        }

        return $items[$itemHash];
    }

    /**
     * Get review href.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getHref()
    {
        return Str::slug($this->context . '_' . $this->item_id . '_' . $this->id);
    }

    /**
     * Render review day ago.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function dayAgo()
    {
        $daysAgo  = Text::_('COM_HYPERPC_TODAY');
        $diffDate = $this->getDiffDate()->days;

        if ($diffDate > 0) {
            $daysAgo = Text::plural('COM_HYPERPC_N_DAYS_AGO', $diffDate);
        }

        return $daysAgo;
    }

    /**
     * Get context title.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getContextTitle()
    {
        list ($option, $type) = explode('.', $this->context);
        return Text::_(Str::up($option . '_REVIEW_CONTEXT_TITLE_' . $type));
    }

    /**
     * Get user object.
     *
     * @return  User
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getUser()
    {
        $params = $this->params;
        if ($this->created_user_id) {
            /** @var User */
            $user = $this->hyper['helper']['user']->findById($this->created_user_id);

            if (!empty($params->get('user_name'))) {
                $user->set('name', $params->get('user_name'));
            }

            if (!empty($params->get('user_avatar'))) {
                $user->set('avatar', $params->get('user_avatar'));
            }

            return $user;
        }

        return new User([
            'name'   => $params->get('user_name'),
            'avatar' => $params->get('user_avatar')
        ]);
    }

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this
            ->setTablePrefix()
            ->setTableType('Reviews');

        parent::initialize();
    }

    /**
     * Check review context.
     *
     * @param   string $context
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isContext($context)
    {
        return (Str::low($context) === $this->context);
    }

    /**
     * Render review images.
     *
     * @param   string  $width
     * @param   string  $height
     * @param   string  $tpl
     *
     * @return  string|null
     *
     * @throws  \Exception
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function renderImages($width = '', $height = '', $tpl = 'images')
    {
        $imgPath = $this->params->get('image', '', 'hpimagepath');

        if (empty($imgPath)) {
            return null;
        }

        /** @var ImageHelper $imageHelper */
        $imageHelper = $this->hyper['helper']['image'];

        $targetPath = Path::clean(JPATH_ROOT . '/' . $imgPath);
        $imagePaths = [];
        if (is_file($targetPath)) {
            $imagePaths = [$targetPath];
        } elseif (is_dir($targetPath)) {
            $dirFiles = Folder::files($targetPath, '.', false, true);
            foreach ($dirFiles as $file) {
                if (!$imageHelper->isExistingImage($file)) {
                    continue;
                }

                $imagePaths[] = $file;
            }
        } else {
            return null; // return null if the path is not a file or a folder
        }

        $images = [];
        foreach ($imagePaths as $imagePath) {
            $images[] = $imageHelper->getThumb($imagePath, intval($width), intval($height), 'hp_review');
        }

        $layout = $this->hyper['helper']['review']->getTpl();

        return $this->hyper['helper']['render']->render('reviews/' . $layout . '/' . $tpl, [
            'entity' => $this,
            'images' => $images
        ]);
    }
}
