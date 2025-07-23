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
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Date\Date;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OrderClearLogsCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     */
    protected static $defaultName = 'order:clear-logs';

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

        $symfonyStyle->title('Clear outdated logs');

        $hp = App::getInstance();

        /** @var DatabaseInterface $db */
        $db = $hp['db'];

        /** @var \HyperPcTableOrder_Logs $table */
        $table = Table::getInstance('Order_Logs');

        $date = (Date::getInstance('now -2 month'));

        $total = $table->getCount([
            $db->quoteName('a.created_time') . ' <= ' . $db->quote($date->toSql())
        ]);

        $message = sprintf('Rows found to remove: <info>%s</info>', $total);
        $symfonyStyle->writeln($message);

        $result = $table->deleteByDate($date);

        return $result ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Configure the command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('This command removes outdated order logs');
        $this->setHelp(
            <<<EOF
            The <info>%command.name%</info> removes outdated order logs
            <info>php %command.full_name%</info>
            EOF
        );
    }
}
