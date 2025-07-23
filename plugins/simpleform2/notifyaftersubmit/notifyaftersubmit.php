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

use HYPERPC\App;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Mail\MailTemplate;
use HYPERPC\Object\Mail\TemplateData;

/**
 * Class plgSimpleform2NotifyAfterSubmit
 *
 * @since   2.0
 */
class plgSimpleform2NotifyAfterSubmit extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  2.0
     */
    protected $autoloadLanguage = true;

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
        $recepient = $moduleParams->get('sfMailTo');
        $replyTo   = $moduleParams->get('sfMailReply');

        if (strpos($replyTo, '{email}') !== false && strpos($recepient, '{email}') === false) {
            $hyper = App::getInstance();
            $siteContext = $hyper['params']->get('site_context', 'hyperpc');

            $uri       = Uri::getInstance();
            $app       = Factory::getApplication();
            $userEmail = $app->input->getString('email', '');
            $userName  = $app->input->getString('name', '');
            $subject   = $this->params->get('subject', Text::sprintf('PLG_SIMPLEFORM2_NOTIFYAFTERSUBMIT_SUBJECT_DEFAULT', strtoupper($siteContext)));

            $message = $hyper['helper']['macros']
                ->setData(['username' => $userName])
                ->text($this->params->get('message'));

            $templateData = new TemplateData([
                'subject' => $subject,
                'heading' => '',
                'message' => $message,
                'reason' => Text::sprintf('PLG_SIMPLEFORM2_NOTIFYAFTERSUBMIT_REASON', $uri->getHost())
            ]);

            $mailer = new MailTemplate('com_hyperpc.mail', $app->getLanguage()->getTag());
            $mailer->addTemplateData($templateData->toArray());
            $mailer->addRecipient($userEmail);

            return $mailer->send();
        }
    }
}
