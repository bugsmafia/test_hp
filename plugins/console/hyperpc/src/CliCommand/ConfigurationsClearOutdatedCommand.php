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
use HYPERPC\Helper\ConfigurationHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Table\Table;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigurationsClearOutdatedCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     */
    protected static $defaultName = 'configurations:delete-outdated';

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

        $symfonyStyle->title('Delete outdated configurations');

        $dateTwoMonthAgo = Date::getInstance('now -2 month');
        $dateSixMonthAgo = Date::getInstance('now -6 month');

        $hp = App::getInstance();

        /** @var DatabaseInterface $db */
        $db = $hp['db'];

        /** @var ConfigurationHelper $helper */
        $helper = $hp['helper']['configuration'];

        $commonConditions = [ // order_id is NULL or 0
            '(' . $db->quoteName('order_id') . ' IS NULL OR ' . $db->quoteName('order_id') . ' = ' . $db->quote(0) . ')'
        ];

        $conditionsOne = array_merge($commonConditions, [
            '(' . $db->quoteName('created_user_id') . ' IS NULL OR ' . $db->quoteName('created_user_id') . ' = ' . $db->quote(0) . ')',
            $db->quoteName('modified_time') . ' <= ' . $db->quote($dateTwoMonthAgo->toSql()),
            $db->quoteName('params') . ' NOT LIKE ' . $db->quote('%"readonly": true%')
        ]);

        $conditionsTwo = array_merge($commonConditions, [
            $db->quoteName('modified_time') . ' <= ' . $db->quote($dateSixMonthAgo->toSql())
        ]);

        $table = $helper->getTable();

        $countOne = $this->getCount($table, $conditionsOne);
        if ($countOne > 0) {
            $helper->delete(['conditions' => $conditionsOne]);
        }

        $countTwo = $this->getCount($table, $conditionsTwo);
        if ($countTwo > 0) {
            $helper->delete(['conditions' => $conditionsTwo]);
        }

        $message = sprintf('Rows found to remove: <info>%s</info>', $countOne + $countTwo);
        $symfonyStyle->writeln($message);

        return Command::SUCCESS;
    }

    /**
     * Configure the command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('This command removes outdated configurations');
        $this->setHelp(
            <<<EOF
            The <info>%command.name%</info> removes outdated configurations
            <info>php %command.full_name%</info>
            EOF
        );
    }

    /**
     * Get rows count by conditions.
     *
     * @param   Table $table
     * @param   array $conditions
     *
     * @return  int
     *
     * @throws  \RuntimeException
     */
    private function getCount(Table $table, array $conditions): int
    {
        $db = $table->getDbo();

        $query = $db
            ->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($table->getTableName()))
            ->where($conditions);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
