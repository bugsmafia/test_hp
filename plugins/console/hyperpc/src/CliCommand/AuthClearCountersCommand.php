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
use HYPERPC\Helper\FormCounterHelper;
use Joomla\CMS\Date\Date;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AuthClearCountersCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     */
    protected static $defaultName = 'auth:clear-counters';

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

        $symfonyStyle->title('Clear outdated counters');

        $hp = App::getInstance();

        /** @var FormCounterHelper $helper */
        $helper = $hp['helper']['formcounter'];

        /** @var DatabaseInterface $db */
        $db = $hp['db'];

        $date = (Date::getInstance('now -24 hour'))->toSql();

        $total = $helper->count([
            $db->quoteName('a.created_time') . ' <= ' . $db->quote($date)
        ]);

        $message = sprintf('Rows found to remove: <info>%s</info>', $total);
        $symfonyStyle->writeln($message);

        $result = $helper->delete([
            'conditions' => [
                $db->quoteName('created_time') . ' <= ' . $db->quote($date)
            ]
        ]);

        return $result ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Configure the command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('This command removes outdated form counters');
        $this->setHelp(
            <<<EOF
            The <info>%command.name%</info> command removes outdated form counters
            <info>php %command.full_name%</info>
            EOF
        );
    }
}
