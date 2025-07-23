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

use HYPERPC\Data\JSON;
use HYPERPC\Joomla\Controller\ControllerAdmin;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Date\Date;
use Joomla\CMS\User\User as JUser;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Model\UserModel;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcControllerUsers
 *
 * @since   2.0
 */
class HyperPcControllerUsers extends ControllerAdmin
{
    private const NORMALIZE_STEP = 100;

    /**
     * Hook on initialize controller.
     *
     * @param   array  $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->registerTask('ajax-normalize-account', 'ajaxNormalizeAccount');
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  bool|UserModel
     *
     * @since   2.0
     */
    public function getModel($name = 'User', $prefix = 'UsersModel', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Ajax action to clear all unused accounts.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function ajaxNormalizeAccount()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $db = $this->hyper['db'];

        $model = $this->getModel();
        $last  = (int) $this->hyper['input']->post->get('last', 0);
        $limit = self::NORMALIZE_STEP;

        $commonConditions = [
            $db->quoteName('a.registerDate') . ' < ' . $db->quote(Date::getInstance('now -1 month')->toSql(false, $db))
        ];

        $total = $this->hyper['helper']['user']->count($commonConditions);
        if ($total === 0) {
            $this->hyper['cms']->close(\json_encode([
                'total' => 0,
                'limit' => $limit,
                'last' => 0,
                'current' => 0,
                'progress' => 100,
                'deleted' => 0,
                'stop' => true
            ]));
        }

        $findAllConditions = $last === 0 ?
            $commonConditions :
            \array_merge(
                $commonConditions,
                [$db->quoteName('a.id') . ' > ' . $db->quote($last)]
            );

        /** @var User[] $users */
        $users = $this->hyper['helper']['user']->findAll([
            'offset' => 0,
            'limit'  => $limit,
            'select' => ['a.id'],
            'conditions' => $findAllConditions
        ]);
        $userIds = \array_keys($users);

        $currentConditions = \array_merge(
            $commonConditions,
            [$db->quoteName('a.id') . ' <= ' . $db->quote($last)]
        );

        $current = $last === 0 ? \min($limit, $total) : \min($this->hyper['helper']['user']->count($currentConditions) + $limit, $total);

        $output = new JSON([
            'stop' => false
        ]);

        $progress = \round(($current / $total) * 100, 2);
        if ((int) $progress === 100) {
            $output->set('stop', true);
        }

        $currentDeleted = 0;

        /** @var UserFactoryInterface $userFactory */
        $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
        $lastProcessed = \array_key_last(\array_flip($userIds));

        foreach ($userIds as $id) {
            /** @var JUser $user */
            $user = $userFactory->loadUserById($id);
            if (\count($user->groups) <= 1 && \array_key_exists(2, $user->groups)) {
                foreach (['configuration', 'order', 'review'] as $helper) {
                    $count = $this->hyper['helper'][$helper]->count([
                        $db->qn('a.created_user_id') . ' = ' . $db->q($user->id)
                    ]);

                    if ($count > 0) {
                        continue 2;
                    }
                }

                $arg = [$id];
                $model->delete($arg);
                $currentDeleted++;
            }
        }

        $output
            ->set('total', $total)
            ->set('limit', $limit)
            ->set('last', $lastProcessed)
            ->set('current', $current)
            ->set('progress', $progress)
            ->set('deleted', $currentDeleted);

        $this->hyper['cms']->close($output->write());
    }
}
