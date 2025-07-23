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

namespace Joomla\Plugin\Simpleform2\FillUser\Extension;

use HYPERPC\App;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class FillUser
 *
 * @since   2.0
 */
final class FillUser extends CMSPlugin
{
    /**
     * On process before send email.
     *
     * @param   $mail
     * @param   $form
     * @param   $moduleParams
     *
     * @since   2.0
     */
    public function onProcessBeforeSendEmail($mail, $form, $moduleParams)
    {
        $app = $this->getApplication();

        $user = $app->getIdentity();
        if (!$user || !$user->id) {
            return;
        }

        $formEmail = $app->getInput()->get('email', '', 'clean');
        $formName = $app->getInput()->get('name', '', 'clean');

        $hasChanges = false;

        $hp = $this->getHyperpcApp();

        $isAutoEmail = $hp['helper']['string']->isAutoEmail($user->email);
        if ($isAutoEmail && !empty($formEmail)) {
            $user->email = $formEmail;
            $hasChanges = true;
        }

        $isAutoName = $user->name === $user->email || preg_match('/^(hyperpc|epix)-\d+/', $user->name);
        if ($isAutoName && !empty($formName)) {
            $user->name = $formName;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $user->save(true);
        }
    }

    /**
     * Get an instanse of the HYPERPC app.
     *
     * @return App
     */
    private function getHyperpcApp(): App
    {
        if (!class_exists('HYPERPC\\App')) {
            $bootstrap = JPATH_ADMINISTRATOR . '/components/com_hyperpc/bootstrap.php';
            if (file_exists($bootstrap)) {
                /** @noinspection PhpIncludeInspection */
                require_once $bootstrap;
            }
        }

        return App::getInstance();
    }
}
