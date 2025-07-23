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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\Review;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\View\ViewLegacy;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewProfile_Reviews
 *
 * @property    Review[] $reviews
 *
 * @since       2.0
 */
class HyperPcViewProfile_Reviews extends ViewLegacy
{

    /**
     * Display action.
     *
     * @param   null $tpl
     *
     * @return  bool|mixed
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $app  = $this->hyper['app'];
        $user = $this->hyper['user'];

        //  Check logged the user.
        if (!$user->id) {
            $app->enqueueMessage(Text::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'info');
            $app->redirect(Route::_('index.php?option=com_users&view=login', false));
            return false;
        }

        /** @var \HyperPcModelReviews $mOrder */
        $mOrder = ModelList::getInstance('Reviews');

        $this->reviews = $mOrder->getItems();

        return parent::display($tpl);
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        $this->hyper['helper']['assets']
            ->jqueryRaty()
            ->addScript('
                $("#adminForm .jsUserRating").each(function () {
                    $(this).raty({
                        starType : "i",
                        readOnly : true
                    });
                });
            ');
    }
}
