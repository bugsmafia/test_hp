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

namespace Joomla\Plugin\Simpleform2\Calltouch\Extension;

use HYPERPC\App;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Mail\Mail;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\CMSPlugin;
use HYPERPC\Helper\CalltouchHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Calltouch plugin
 */
final class Calltouch extends CMSPlugin
{
    /**
     * On process before send email.
     *
     * @param   Mail $mail
     * @param   $form
     * @param   Registry $moduleParams
     */
    public function onProcessBeforeSendEmail($mail, $form, $moduleParams)
    {
        $appInput = $this->getApplication()->getInput();

        $userEmail  = $appInput->getString('email', '');
        $userName   = $appInput->getString('name', '');
        $userPhone  = $appInput->getString('phone', '');
        $requestUrl = $appInput->getString('page-url', '');
        $subject    = $mail->Subject;

        if ($requestUrl && strpos($requestUrl, Uri::root()) === false) {
            return;
        }

        try {
            $hp = $this->getHyperpcApp();

            /** @var CalltouchHelper */
            $calltouchHelper = $hp['helper']['calltouch'];
        } catch (\Exception $th) {
            return;
        }

        $calltouchHelper->registerCall($userName, $userPhone, $userEmail, $subject, $requestUrl);
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
