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
 *
 * @var         array $displayData
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : [];

if (is_array($data['options'])) {
    $data['options'] = new Registry($data['options']);
}

// Options
$filterButton = $data['options']->get('filterButton', true);
$searchButton = $data['options']->get('searchButton', true);

$filters = $data['view']->filterForm->getGroup('filter');

$filters['filter_search']->class .= ' uk-search-input tm-background-gray-5';
?>

<?php if (!empty($filters['filter_search'])) : ?>
    <?php if ($searchButton) : ?>
        <div class="uk-flex uk-flex-middle uk-width-medium@s">
            <div class="uk-search uk-width-expand uk-search-default tm-background-gray-5">
                <button type="submit" class="uk-search-icon-flip uk-icon uk-search-icon" data-uk-search-icon
                        title="<?= HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT') ?>">
                </button>
                <?= $filters['filter_search']->input ?>
            </div>

            <div class="uk-margin-small-left">
                <button type="button" class="uk-button tm-button-icon js-stools-btn-clear"
                        title="<?= HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_CLEAR') ?>">
                    <span class="uk-icon" data-uk-icon="close"></span>
                </button>
            </div>
        </div>
    <?php endif; ?>
<?php endif;
