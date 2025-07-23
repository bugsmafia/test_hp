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

defined('_JEXEC') or die('Restricted access');

$deprecatedStatises = [
    '[A01]' => 'AppFormDispalyed',
    '[A1]'  => 'ContactsReceived',
    '[A15]' => 'ApprScoring',
    '[A16]' => 'ApplicationSuccessfullyExecuted',
    '[A4]'  => 'PaymentInitiated',
    '[A5]'  => 'PersonIdentificationSkipped',
    '[A6]'  => 'PersonIdentified',
    '[C2]'  => 'CardHoldSuccess',
    '[C5]'  => 'InitFee',
    '[L0]'  => 'DeliveryAddressReceived',
    '[L10]' => 'CourierSentToCustomer',
    '[L11]' => 'WaitingForGoodsShipment',
    '[L12]' => 'WaitSignMerch',
    '[L4]'  => 'ScanSendToFO',
    '[L6]'  => 'GoodsDeliveredToClient',
    '[L7]'  => 'WaitSignFO',
    '[L9]'  => 'PostponedSignDate',
    '[P0]'  => 'UploadedTo1C',
    '[P3]'  => 'ShipmentStatusSentTo1C',
    '[R0]'  => 'CreditRejectedByScoring',
    '[R12]' => 'DenAtSignFO',
    '[R13]' => 'DenAtSign7S',
    '[R14]' => 'RejectedMeetingCourier',
    '[R15]' => 'DenAtSignCust',
    '[R20]' => 'DenScoringIgnored',
    '[R22]' => 'DenAtSignMerch',
    '[R23]' => 'AppBlocked',
    '[R24]' => 'CodeCheckFailed',
    '[R25]' => 'AppAbandoned',
    '[R26]' => 'GoodsAreNotAvailableReject',
    '[R8]'  => 'CardHoldFail',
    '[V0]'  => 'SentForVerification'
];

return [
    '[A0]'  => 'BasketReceived', // Корзина получена
    '[A2]'  => 'PhoneConfirmed', // Телефон клиента подтвержден
    '[A10]' => 'WaitAccept', // Ожидание от клиента акцепта кредита
    '[A11]' => 'Accepted', // Получен акцепт клиента
    '[A12]' => 'AppSentToFO', // Заявка направлена в финансовые организации
    '[A13]' => 'ApprFO', // Одобрено финансовой организацией
    '[A14]' => 'AppSentToCallCenter', // Заявка передана в колл-центр
    '[C0]'  => 'CreditApproved', // Кредит подписан
    '[C3]'  => 'LoanOpened', // Ссуда открыта
    '[L13]' => 'DeliveryDateReceived', // Получена дата подписания
    '[P2]'  => 'FundsPaidToMerchant', // Средства переведены в магазин
    '[R1]'  => 'CreditRejectedByFinOrgs', // Отказ финансовых организаций
    '[R16]' => 'CustomerCallFailed', // КЦ не дозвонился до клиента
    '[R17]' => 'CustCancelBeforeAppr', // Отказ клиента до получения предложений
    '[R18]' => 'CustCancelAfterAppr', // Отказ клиента после получения предложений
    '[V1]'  => 'VerificationPassed', // Верификация пройдена
    '[V2]'  => 'VerificationFailed' // Верификация не пройдена
];
