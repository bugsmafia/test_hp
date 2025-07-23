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

use HYPERPC\ORM\Entity\Note;
use HYPERPC\Helper\NoteHelper;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Elements\ElementConfiguratorActions;

defined('_JEXEC') or die('Restricted access');

/**
 * Class ElementConfigurationActionsNote
 *
 * @property    NoteHelper          $helper
 * @property    HyperPcModelNote    $model
 *
 * @since       2.0
 */
class ElementConfigurationActionsNote extends ElementConfiguratorActions
{

    /**
     * Get note entity.
     *
     * @return  Note
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getNote()
    {
        $configuration = $this->getConfiguration();
        return $this->helper->get(
            $configuration->id,
            $this->getNoteContext(),
            $this->hyper['user']->id
        );
    }

    /**
     * Get note context.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getNoteContext()
    {
        return HP_OPTION . '.configuration';
    }

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this->registerAction('renderForm');
        /** @var  $model */
        $this->model  = ModelAdmin::getInstance('Note');
        $this->helper = $this->hyper['helper']['note'];
    }

    /**
     * Load assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadAssets()
    {
        $this->hyper['helper']['assets']
            ->js('elements:' . $this->_group . '/' . $this->_type . '/assets/js/widget.js')
            ->widget('.jsElementNote', 'HyperPC.SiteConfigurationActionsNote', [
                'token' => Session::getFormToken()
            ]);
    }

    /**
     * Render action button in profile account.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderActionButton()
    {
        return $this->render();
    }
}
