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

namespace HYPERPC\Plugin\Console\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use HYPERPC\Plugin\Console\CliCommand\AuthClearCodesCommand;
use HYPERPC\Plugin\Console\CliCommand\AuthClearCountersCommand;
use HYPERPC\Plugin\Console\CliCommand\ConfigurationsClearOutdatedCommand;
use HYPERPC\Plugin\Console\CliCommand\MoyskladUpdateProductPricesCommand;
use HYPERPC\Plugin\Console\CliCommand\MoyskladUpdateStocksCommand;
use HYPERPC\Plugin\Console\CliCommand\OrderClearLogsCommand;
use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

class ConsolePlugin extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
        ];
    }

    public function registerCommands(): void
    {
        /** @var ConsoleApplication $app */
        $app = $this->getApplication();
        $app->addCommand(new AuthClearCodesCommand());
        $app->addCommand(new AuthClearCountersCommand());
        $app->addCommand(new ConfigurationsClearOutdatedCommand());
        $app->addCommand(new MoyskladUpdateProductPricesCommand());
        $app->addCommand(new MoyskladUpdateStocksCommand());
        $app->addCommand(new OrderClearLogsCommand());
    }
}
