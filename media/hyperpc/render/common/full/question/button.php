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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

/**
 * @var         string $itemName
 * @var         string $price
 * @var         string $type
 * @var         RenderHelper $this
 */

$mode = $this->hyper['params']->get($type . '_question_form_module_mode', 'simpleform'); // custom or simpleform

$modalKey = 'question-modal';

$formExists = true;
try {
    $getFormMethod = $mode === 'simpleform' ? 'renderQuestionForm' : 'renderQuestionModalContent';
    $questionForm = $this->hyper['helper']['moyskladProduct']->$getFormMethod();
    if (!empty($questionForm)) {
        $template = 'common/full/question/_modal';
        if ($mode === 'custom') {
            $template .= '_custom';
        }

        echo $this->hyper['helper']['render']->render($template, [
            'modalKey' => $modalKey,
            'form'     => $questionForm,
            'type'     => $type
        ]);
    }
} catch (\Throwable $th) {
    $formExists = false;
}
?>

<?php if ($formExists) :
    $itemInfo = [
        'name'  => $itemName,
        'price' => $price
    ]
    ?>
    <a href="#<?= $modalKey ?>" class="uk-link-reset jsQuestionButton" title="<?= Text::_('COM_HYPERPC_ASK_A_SPECIALIST') ?>" data-item-info='<?= json_encode($itemInfo) ?>' uk-toggle>
        <span class="hp-conditions-item hp-conditions-item--link uk-flex uk-flex-middle">
            <span class="hp-conditions-item__icon">
                <span uk-icon="icon:users" class="uk-icon"></span>
            </span>
            <span>
                <span class="hp-conditions-item__text">
                    <?= Text::_('COM_HYPERPC_NEED_HELP') ?>
                </span>
                <span class="hp-conditions-item__sub">
                    <?= Text::_('COM_HYPERPC_ASK_FOR_ADVICE') ?>
                </span>
            </span>
        </span>
    </a>
<?php endif;
