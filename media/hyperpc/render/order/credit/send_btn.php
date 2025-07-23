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

use Joomla\CMS\Language\Text;
use HYPERPC\Elements\Manager;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * @var         RenderHelper $this
 * @var         Order        $order
 */

/** @var ElementCredit[] $creditElements */
$creditElements = Manager::getInstance()->getByPosition(Manager::ELEMENT_TYPE_CREDIT);

$elementsForRender = [];
foreach ($creditElements as $creditElement) {
    if (!$creditElement->isAvailableInOrder($order)) {
        continue;
    }

    $elementsForRender[] = $creditElement;
}
?>

<?php if (count($elementsForRender)) :
    $orderTotal = (int) $order->getTotal()->val();
    $currentUserIsOwner = $this->hyper['user']->id === $order->created_user_id;
    $isClearanceDayLimit = $this->hyper['helper']['credit']->checkClearanceDayLimit($order);
    ?>
    <div class="uk-margin-medium-top uk-h4">
        <?= Text::_('COM_HYPERPC_CREDIT_SEND_REQUEST_TO_BANKS') ?>:
    </div>
    <div class="uk-grid uk-grid-small uk-child-width-1-2@s uk-margin uk-grid-match" uk-grid>
        <?php foreach ($elementsForRender as $creditElement) :
            $elementKeyParam = $order->params->find($creditElement->getParamKey());
            $alreadySent     = (bool) $elementKeyParam;
            $config          = $creditElement->getConfig();
            $loanType        = trim($config->get('loan_type', ''));
            $loanTypeLogic   = $config->get('loan_type_logic', 'credit');
            $description     = trim($config->get('description', ''));
            $maxPrice        = $config->get('max_price', 0, 'int');
            ?>
            <div>
                <div class="uk-card uk-card-default uk-card-small uk-card-body uk-flex uk-flex-column uk-flex-between">
                    <div>
                        <div class="uk-text-muted uk-text-small uk-text-lowercase">
                            <?= !empty($loanType) ? $loanType : Text::_('COM_HYPERPC_ORDER_PAYMENT_TYPE_CREDIT') ?>
                        </div>

                        <div class="uk-text-emphasis" style="font-size: 1.1rem">
                            <?= $creditElement->getMethodName() ?>
                        </div>

                        <div class="uk-margin-small-top">
                            <?= $config->get('period', '') ?>
                        </div>
                    </div>

                    <div class="uk-margin-top">
                        <?php if ($alreadySent) :
                            $viewUrl = filter_var($creditElement->getViewUrl($order), FILTER_VALIDATE_URL);
                            ?>
                            <div>
                                <?php if ($viewUrl) : ?>
                                    <a href="<?= $viewUrl ?>" target="_blank">
                                        <?= Text::_('COM_HYPERPC_CREDIT_GO_TO_SERVICE_PROFILE') ?>
                                    </a>
                                <?php else : ?>
                                    <span class="uk-text-success">
                                        <?= Text::_('COM_HYPERPC_CREDIT_METHOD_ALREADY_SENT') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($loanTypeLogic === 'installment' && $order->hasNotebooks()) : ?>
                            <div class="uk-text-danger">
                                <?= Text::_('COM_HYPERPC_CREDIT_METHOD_NOT_AVAILABLE_FOR_NOTEBOOKS') ?>
                            </div>
                        <?php elseif ($maxPrice && $maxPrice < $orderTotal) : ?>
                            <div class="uk-text-danger">
                                <?= Text::_('COM_HYPERPC_CREDIT_METHOD_AMOUNT_EXCEEDED') ?>
                            </div>
                        <?php elseif (!$isClearanceDayLimit) : ?>
                            <div class="uk-text-danger">
                                <?= Text::_('COM_HYPERPC_CREDIT_DAY_LIMIT_INFO') ?>
                            </div>
                        <?php else :
                            $btnAttrs = [
                                'class' => 'jsCreditMethodSendButton uk-button uk-button-small uk-button-primary uk-width-1-1',
                                'data'  => [
                                    'type' => $creditElement->getType()
                                    ]
                                ];

                            if ($currentUserIsOwner) {
                                $btnUrl = $this->hyper['route']->build([
                                    'id'      => $order->id,
                                    'token'   => $order->getToken(),
                                    'element' => $creditElement->getType(),
                                    'task'    => 'credit.send-profile-request',
                                ]);

                                $btnAttrs['href'] = $btnUrl;
                                $btnAttrs['target'] = '_blank';
                                $btnAttrs['onclick'] = 'this.classList.add(\'uk-disabled\')';
                            } else {
                                $btnAttrs['href'] = '#';
                                $btnAttrs['class'] .= ' uk-disabled';
                            }
                            ?>
                            <a <?= $this->hyper['helper']['html']->buildAttrs($btnAttrs) ?>>
                                <?= Text::_('COM_HYPERPC_CREDIT_SEND_REQUEST') ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($description)) : ?>
                        <button class="uk-position-small uk-position-top-right uk-link-muted uk-icon" type="button" uk-icon="question"></button>
                        <div class="uk-drop" data-uk-drop="pos: left-center">
                            <div class="uk-card uk-card-small uk-card-body uk-card-default uk-text-small">
                                <?= HTMLHelper::_('content.prepare', $description); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
    $creditSupportInfo = $this->hyper['helper']['module']->renderById($this->hyper['params']->get('credit_order_info'));
    if (!empty(trim($creditSupportInfo))) : ?>
        <div class="uk-margin-medium-bottom">
            <?= $creditSupportInfo ?>
        </div>
    <?php endif; ?>
<?php endif;
