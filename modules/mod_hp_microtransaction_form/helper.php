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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

class ModMicrotransactionFormHelper
{
    private const SBER_URL_TEST = 'https://3dsec.sberbank.ru/payment/docsite/assets';
    private const SBER_URL_PROD = 'https://securepayments.sberbank.ru/payment/docsite/assets';

    /**
     * Prepare params data
     *
     * @param   Registry $params module params
     *
     * @return  void
     */
    public static function prepareData(Registry $params)
    {
        $subjects = (array) $params->get('subjects', []);
        $flattenedSubjects = [];
        foreach ($subjects as $subjectsGroup) {
            foreach ($subjectsGroup->subject_group as $subject) {
                $flattenedSubjects[] = $subject;
            }
        }
        $params->set('subjects', $flattenedSubjects);

        if (empty($flattenedSubjects)) {
            $params->set('default_state', '');
            return;
        }

        $defaultState = $params->get('default_state', '');
        $filteredSubjects = array_filter($flattenedSubjects, function ($subject) use ($defaultState) {
            return $subject->key === $defaultState;
        });

        if (empty($filteredSubjects)) {
            $params->set('default_state', $flattenedSubjects[0]->key);
            $params->set('total', $flattenedSubjects[0]->price);
        } else {
            $params->set('total', $filteredSubjects[array_key_first($filteredSubjects)]->price);
        }
    }

    /**
     * Set module assets
     *
     * @param   Registry $params module params
     *
     * @return  void
     *
     * @throws  \Exception
     */
    public static function setAssets(Registry $params)
    {
        $isProdEnv = (bool) $params->get('production', '0');

        $sberUri = $isProdEnv ? self::SBER_URL_PROD : self::SBER_URL_TEST;

        $cssUrl = $sberUri . '/css/payment.modal.css';
        $jsUrl = $sberUri . '/js/ipay.js';

        $document = Factory::getDocument();

        $document->addStyleSheet($cssUrl);
        $document->addScript($jsUrl);

        $tokenPropName = $isProdEnv ? 'api_token' : 'api_token_test';
        $token = $params->get($tokenPropName, '');
        $document->addScriptDeclaration("var ipay = IPAY && new IPAY({api_token: '{$token}'});");

        $subjects = (array) $params->get('subjects', []);
        $jsSubjects = [];
        foreach ($subjects as $subject) {
            $jsSubjects[$subject->key] = $subject->price;
        }

        $hp = App::getInstance();
        $hp['helper']['assets']
            ->js('modules:mod_hp_microtransaction_form/assets/js/microtransaction.js')
            ->widget('.jsMicrotransactionForm', 'HyperPC.Microtransaction', [
                'subjects' => $jsSubjects,
                'defaultState' => $params->get('default_state', ''),
                'checkPaymentError' => Text::_('MOD_HP_MICROTRANSACTION_FORM_ERROR_FAILED_TO_VERIFY_PAYMENT'),
                'purchaseFailMessage' => Text::_('MOD_HP_MICROTRANSACTION_FORM_ERROR_PURCHASE_FAIL'),
                'successPurchaseMessage' => Text::_('MOD_HP_MICROTRANSACTION_FORM_SUCCESS_PURCHASE'),
            ]);
    }
}
