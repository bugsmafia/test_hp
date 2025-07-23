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

namespace HYPERPC\Plugin\Console\CliCommand;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use HYPERPC\App;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Helper\MoyskladStockHelper;
use HYPERPC\ORM\Table\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;

class MoyskladUpdateProductPricesCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     */
    protected static $defaultName = 'moysklad:update-product-prices';

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->title('Update product prices');

        /** @var \HyperPcTablePrice_Recount_Queue */
        $priceRecountQueueTable = Table::getInstance('Price_Recount_Queue');
        if (!$priceRecountQueueTable) {
            $symfonyStyle->error('Price_Recount_Queue table does not exist');

            return Command::FAILURE;
        }

        $hp = App::getInstance();

        /** @var MoyskladProductHelper $productHelper */
        $productHelper = $hp['helper']['moyskladProduct'];
        /** @var MoyskladStockHelper $stockHelper */
        $stockHelper = $hp['helper']['moyskladStock'];

        $stocksUpdated = $stockHelper->recountProductPrices();
        $productsUpdated = $productHelper->recountPrices();

        $symfonyStyle->table(
            [],
            [
                ['stocks updated', $stocksUpdated],
                ['products updated', $productsUpdated]
            ]
        );

        if ($stocksUpdated || $productsUpdated) {
            $mvc = $this->getApplication()->bootComponent('cache')->getMVCFactory();
            $cacheModel = $mvc->createModel('Cache', 'Administrator');
            $result = $cacheModel->getCache()->clean('com_content');

            $symfonyStyle->text('Content cache cleaned: ' . ($result ? 'true' : 'false'));
        }

        // Clear queue
        $priceRecountQueueTable->clear();

        return Command::SUCCESS;
    }

    /**
     * Configure the command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('This command recalculates product prices');
        $this->setHelp(
            <<<EOF
            The <info>%command.name%</info> command recalculates product prices
            <info>php %command.full_name%</info>
            EOF
        );
    }
}
