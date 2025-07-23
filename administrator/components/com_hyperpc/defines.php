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

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

define('HP_DEFAULT_ROW_COLS', 4);
define('HP_REQUEST_API_KEY', 'ATmZ4D');
define('HP_OPTION', 'com_hyperpc');
define('HP_MOBILE_MASK', '+7 (000) 000-00-00');
define('HP_PHONE_REGEX', '\+?\d{1,3}[\s\-]?\(?[0-9\s\-]{3,8}[\s\-]?\)?[\s\-]?[0-9\s\-]{5,10}');

//  Site context.
define('HP_CONTEXT_EPIX', 'epix');
define('HP_CONTEXT_HYPERPC', 'hyperpc');
define('HP_CONTEXT_PRO_HYPERPC', 'pro.hyperpc');

define('HP_REPOSITORY_PATH', dirname(dirname(dirname(__DIR__))));

//  Path defines.
define('HP_PATH_ADMIN', JPATH_ADMINISTRATOR . '/components/' . HP_OPTION);

define('HP_PATH_TABLES', HP_PATH_ADMIN   . '/tables');
define('HP_ADMIN_PATH_MODELS', HP_PATH_ADMIN   . '/models');
define('HP_SITE_PATH_MODELS', JPATH_ROOT      . '/components/' . HP_OPTION . '/models');

define('HP_STATUS_TRASHED', -2);
define('HP_STATUS_ARCHIVED', 2);
define('HP_STATUS_PUBLISHED', 1);
define('HP_STATUS_UNPUBLISHED', 0);

//  Single data bases.
define('HP_DB_PORT', 3306);
define('HP_DB_COMMON', 'hyperpc_common');

$config = Factory::getConfig();
$params = ComponentHelper::getParams(HP_OPTION);

if (!defined('HP_DB_COMPONENT')) {
    define('HP_DB_COMPONENT', $config->get('db'));
}

//  Table defines.
define('HP_TABLE_PRIMARY_KEY', 'id');
define('HP_TABLE_PREFIX', 'hp_');
define('HP_TABLE_CLASS_PREFIX', 'HyperPcTable');
define('HP_MODEL_CLASS_PREFIX', 'HyperPcModel');

//  Component ROOT table names.
define('HP_TABLE_LEADS', '#__' . HP_TABLE_PREFIX . 'leads');
define('HP_TABLE_PARTS', '#__' . HP_TABLE_PREFIX . 'parts');
define('HP_TABLE_GROUPS', '#__' . HP_TABLE_PREFIX . 'groups');
define('HP_TABLE_OPTIONS', '#__' . HP_TABLE_PREFIX . 'options');
define('HP_TABLE_WORKERS', '#__' . HP_TABLE_PREFIX . 'workers');
define('HP_TABLE_DEAL_MAP', '#__' . HP_TABLE_PREFIX . 'deal_map');
define('HP_TABLE_STATUSES', '#__' . HP_TABLE_PREFIX . 'statuses');
define('HP_TABLE_ORDER_LOGS', '#__' . HP_TABLE_PREFIX . 'order_logs');

//  Component site table names.
define('HP_TABLE_GAMES', '#__' . HP_TABLE_PREFIX . 'games');
define('HP_TABLE_NOTES', '#__' . HP_TABLE_PREFIX . 'notes');
define('HP_TABLE_STORES', '#__' . HP_TABLE_PREFIX . 'stores');
define('HP_TABLE_ORDERS', '#__' . HP_TABLE_PREFIX . 'orders');
define('HP_TABLE_REVIEWS', '#__' . HP_TABLE_PREFIX . 'reviews');
define('HP_TABLE_PRODUCTS', '#__' . HP_TABLE_PREFIX . 'products');
define('HP_TABLE_USER_CODES', '#__' . HP_TABLE_PREFIX . 'user_codes');
define('HP_TABLE_CATEGORIES', '#__' . HP_TABLE_PREFIX . 'categories');
define('HP_TABLE_BANNED_IDS', '#__' . HP_TABLE_PREFIX . 'banned_ids');
define('HP_TABLE_STORE_ITEMS', '#__' . HP_TABLE_PREFIX . 'store_items');
define('HP_TABLE_PROMO_CODES', '#__' . HP_TABLE_PREFIX . 'promo_codes');
define('HP_TABLE_FORM_COUNTER', '#__' . HP_TABLE_PREFIX . 'form_counter');
define('HP_TABLE_FORM_RECORDS', '#__' . HP_TABLE_PREFIX . 'form_records');
define('HP_TABLE_PRODUCTS_INDEX', '#__' . HP_TABLE_PREFIX . 'products_index');
define('HP_TABLE_COMPATIBILITIES', '#__' . HP_TABLE_PREFIX . 'compatibilities');
define('HP_TABLE_PRODUCTS_OPTIONS', '#__' . HP_TABLE_PREFIX . 'products_options');
define('HP_TABLE_MICROTRANSACTIONS', '#__' . HP_TABLE_PREFIX . 'microtransactions');
define('HP_TABLE_PRODUCTS_IN_STOCK', '#__' . HP_TABLE_PREFIX . 'products_in_stock');
define('HP_TABLE_SAVED_CONFIGURATIONS', '#__' . HP_TABLE_PREFIX . 'saved_configurations');
define('HP_TABLE_PRODUCTS_CONFIG_VALUES', '#__' . HP_TABLE_PREFIX . 'products_config_values');

//  Moysklad tables
define('HP_TABLE_POSITIONS', '#__' . HP_TABLE_PREFIX . 'positions');
define('HP_TABLE_MOYSKLAD_PARTS', '#__' . HP_TABLE_PREFIX . 'moysklad_parts');
define('HP_TABLE_POSITION_TYPES', '#__' . HP_TABLE_PREFIX . 'position_types');
define('HP_TABLE_MOYSKLAD_STORES', '#__' . HP_TABLE_PREFIX . 'moysklad_stores');
define('HP_TABLE_PRODUCT_FOLDERS', '#__' . HP_TABLE_PREFIX . 'product_folders');
define('HP_TABLE_PROCESSINGPLANS', '#__' . HP_TABLE_PREFIX . 'processingplans');
define('HP_TABLE_MOYSKLAD_PRODUCTS', '#__' . HP_TABLE_PREFIX . 'moysklad_products');
define('HP_TABLE_MOYSKLAD_SERVICES', '#__' . HP_TABLE_PREFIX . 'moysklad_services');
define('HP_TABLE_MOYSKLAD_WEBHOOKS', '#__' . HP_TABLE_PREFIX . 'moysklad_webhooks');
define('HP_TABLE_MOYSKLAD_VARIANTS', '#__' . HP_TABLE_PREFIX . 'moysklad_variants');
define('HP_TABLE_POSITIONS_TRANSLATIONS', '#__' . HP_TABLE_PREFIX . 'positions_translations');
define('HP_TABLE_MOYSKLAD_CHARACTERISTICS', '#__' . HP_TABLE_PREFIX . 'moysklad_characteristics');
define('HP_TABLE_PRODUCT_FOLDERS_TRANSLATIONS', '#__' . HP_TABLE_PREFIX . 'product_folders_translations');
define('HP_TABLE_MOYSKLAD_VARIANTS_TRANSLATIONS', '#__' . HP_TABLE_PREFIX . 'moysklad_variants_translations');
define('HP_TABLE_MOYSKLAD_CHARACTERISTICS_VALUES', '#__' . HP_TABLE_PREFIX . 'moysklad_characteristics_values');

define('HP_TABLE_MOYSKLAD_STORE_ITEMS', '#__' . HP_TABLE_PREFIX . 'moysklad_store_items');
define('HP_TABLE_MOYSKLAD_PRODUCTS_INDEX', '#__' . HP_TABLE_PREFIX . 'moysklad_products_index');
define('HP_TABLE_MOYSKLAD_PRODUCT_VARIANTS', '#__' . HP_TABLE_PREFIX . 'moysklad_product_variants');

define('HP_TABLE_PRICE_RECOUNT_QUEUE', '#__' . HP_TABLE_PREFIX . 'price_recount_queue');

//  Joomla tables.
define('JOOMLA_TABLE_TAGS', '#__tags');
define('JOOMLA_TABLE_FIELDS', '#__fields');
define('JOOMLA_TABLE_FIELDS_VALUES', '#__fields_values');
define('JOOMLA_TABLE_FIELDS_CATEGORIES', '#__fields_categories');

//  Order form types.
define('HP_ORDER_FORM', 0);      // Тип формы "оформление заказа"
define('HP_ORDER_FORM_CREDIT', 1);      // Тип формы "оформление кредита"

//  Cache groups.
define('HP_CACHE_PART_GROUP', 'hp_part');
define('HP_CACHE_ASSETS_GROUP', 'hp_assets');
define('HP_CACHE_PRODUCT_GROUP', 'hp_product');
define('HP_CACHE_POSITION_GROUP', 'hp_position');

//  Part image size.
define('HP_PART_IMAGE_THUMB_WIDTH', 319);
define('HP_PART_IMAGE_THUMB_HEIGHT', 179);

//  Payment types.
define('HP_PAYMENT_TYPE_CREDIT', 'credit');

//  Joomla components names.
define('JOOMLA_COM_USERS', 'com_users');
define('JOOMLA_COM_FIELDS', 'com_fields');

//  Others.
define('HP_QUANTITY_MIN_VAL', 1);
define('HP_COOKIE_HMP', 'hpm');
define('HP_COOKIE_UID', 'hp_uid');
define('JOOMLA_FORM_CONTROL', 'jform');
define('FILTER_URL_DELIMITER', '|');
define('HP_INHERIT_VALUE', '-1');

if ($params->get('site_context') === HP_CONTEXT_HYPERPC) {
    define('GROUP_ID_NOTEBOOK_ROOT', 186);
} elseif ($params->get('site_context') === HP_CONTEXT_EPIX) {
    define('GROUP_ID_NOTEBOOK_ROOT', 186);
} elseif ($params->get('site_context') === HP_CONTEXT_PRO_HYPERPC) {
    define('GROUP_ID_NOTEBOOK_ROOT', 232);
}

define('HP_ORDER_CONTEXT_LIST', [
    HP_CONTEXT_HYPERPC,
    HP_CONTEXT_EPIX,
    HP_CONTEXT_PRO_HYPERPC
]);

//  Developer Constants.
define('HP_DEV_USERNAME', 'save@hyperpc.ru');
define('HP_DEV_EMAIL', 'save@hyperpc.ru');
define('HP_DEV_PHONE', '+7 (000) 000-00-00');
