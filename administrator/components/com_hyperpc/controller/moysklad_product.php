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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use HYPERPC\Joomla\Controller\ControllerPosition;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

/**
 * Class HyperPcControllerMoysklad_Product
 *
 * @since   2.0
 */
class HyperPcControllerMoysklad_Product extends ControllerPosition
{
    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->registerTask('copy-configuration', 'copyConfiguration');
    }

    /**
     * Copies configuration and price fields from the source product to the target product.
     */
    public function copyConfiguration()
    {
        $this->app->mimeType = 'application/json';
        $this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
        $this->app->sendHeaders();

        if (!Session::checkToken()) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            $this->app->close();
        }

        $sourceId = $this->app->getInput()->getInt('source');
        $targetId = $this->app->getInput()->getInt('target');

        $data = [
            'source' => $sourceId,
            'target' => $targetId
        ];

        $isError = !$this->hyper['helper']['moyskladProduct']->copyConfiguration($sourceId, $targetId);
        $message = $isError ? Text::_('COM_HYPERPC_ERROR_ITEM_NOT_WRITE_IN_DB') : Text::_('COM_HYPERPC_PRODUCT_SAVE_SUCCESS');

        echo new JsonResponse($data, $message, $isError);
        $this->app->close();
    }
}
