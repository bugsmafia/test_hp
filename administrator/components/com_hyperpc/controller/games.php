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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use HYPERPC\Helper\GameHelper;
use HYPERPC\Joomla\Model\Entity\Game;
use HYPERPC\Joomla\Controller\ControllerAdmin;

/**
 * Class HyperPcControllerGames
 *
 * @since   2.0
 */
class HyperPcControllerGames extends ControllerAdmin
{

    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $text_prefix = 'COM_HYPERPC_GAME';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  bool|JModelLegacy
     *
     * @since   2.0
     */
    public function getModel($name = 'Game', $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Set default game property
     *
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since 2.0
     */
    public function setDefault()
    {
        $cid = $this->input->get('cid', [], 'array');

        /** @var GameHelper $helper */
        $helper = $this->hyper['helper']['game'];
        /** @var HyperPcModelGame $model */
        $model = $this->getModel();

        if (count($cid)) {
            foreach ($cid as $id) {
                /** @var Game $game */
                $game = $helper->findById($id);
                $game->default_game = 1;

                $data = $game->getArray();
                $model->save($data);
            }
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=games', false));
    }

    /**
     * Unset default game property
     *
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since 2.0
     */
    public function unsetDefault()
    {
        $cid = $this->input->get('cid', [], 'array');

        /** @var GameHelper $helper */
        $helper = $this->hyper['helper']['game'];
        /** @var HyperPcModelGame $model */
        $model = $this->getModel();

        if (count($cid)) {
            foreach ($cid as $id) {
                /** @var Game $game */
                $game = $helper->findById($id);
                $game->default_game = 0;

                $data = $game->getArray();
                $model->save($data);
            }
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=games', false));
    }
}
