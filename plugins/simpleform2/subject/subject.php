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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Class plgSimpleform2Subject
 *
 * @since   2.0
 */
class plgSimpleform2Subject extends CMSPlugin
{

    /**
     * On process before send email.
     *
     * @param   $mail
     * @param   $form
     * @param   $moduleParams
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function onProcessBeforeSendEmail($mail, $form, $moduleParams)
    {
        $app     = Factory::getApplication();
        $subject = $moduleParams->get('sfMailSubj', Text::_('MOD_SIMPLEFORM2_MAIL_SUBJECT_DEFAULT'));
        if (strpos($subject, '{subject}') !== false) {
            $subject = str_replace('{subject}', $app->input->getString('subject', Text::_('MOD_SIMPLEFORM2_MAIL_SUBJECT_DEFAULT')), $subject);
        }

        $mail->setSubject($subject);
    }
}
