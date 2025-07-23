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
use HYPERPC\Helper\MoyskladStockHelper;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MoyskladUpdateStocksCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     */
    protected static $defaultName = 'moysklad:update-stocks';

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

        $symfonyStyle->title('Update stocks from MoySklad');

        $hp = App::getInstance();

        /** @var MoyskladStockHelper $helper */
        $helper = $hp['helper']['moyskladStock'];

        try {
            $helper->updateStocks();
        } catch (\Throwable $th) {
            $symfonyStyle->error($th->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Configure the command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('This command updatess stocks from MoySklad');
        $this->setHelp(
            <<<EOF
            The <info>%command.name%</info> updatess stocks from MoySklad
            <info>php %command.full_name%</info>
            EOF
        );
    }
}
