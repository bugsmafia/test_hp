--
-- HYPERPC - The shop of powerful computers.
--
-- This file is part of the HYPERPC package.
-- For the full copyright and license information, please view the LICENSE
-- file that was distributed with this source code.
--
-- @package   HYPERPC
-- @license   Proprietary
-- @copyright Proprietary https://hyperpc.ru/license
-- @link      https://github.com/HYPER-PC/HYPERPC".
-- @author    Sergey Kalistratov <kalistratov.s.m@gmail.com>
-- @author    Artem Vyshnevskiy
--

--
-- Create order table.
--
CREATE TABLE `#__hp_orders` (
    `id`                INT(11) NOT NULL AUTO_INCREMENT,
    `cid`               VARCHAR(255) NOT NULL,
    `to_1c`             TINYINT(1) NULL DEFAULT 0,
    `total`             FLOAT NULL DEFAULT NULL,
    `parts`             MEDIUMTEXT NULL,
    `status`            INT(2) NULL DEFAULT NULL,
    `products`          MEDIUMTEXT NULL,
    `positions`         TEXT NULL,
    `elements`          TEXT NULL,
    `params`            MEDIUMTEXT NULL,
    `promo_code`        VARCHAR(50) NULL DEFAULT NULL,
    `form`              TINYINT(1) NOT NULL DEFAULT '0',
    `status_history`    TEXT NULL,
    `delivery_type`     VARCHAR(50) NULL DEFAULT NULL,
    `payment_type`      VARCHAR(50) NULL DEFAULT NULL,
    `worker_id`         INT(11) NULL DEFAULT NULL,
    `CONTEXT`           VARCHAR(20) NULL DEFAULT NULL,
    `created_time`      DATETIME NULL DEFAULT NULL,
    `created_user_id`   INT(11) NULL DEFAULT NULL,
    `modified_time`     DATETIME NULL DEFAULT NULL,
    `modified_user_id`  INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = MyISAM
    AUTO_INCREMENT  = 1
;

--
-- Create products config values table.
--
CREATE TABLE `#__hp_products_config_values` (
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `context`           VARCHAR(50) NOT NULL,
    `product_id`        INT(11) UNSIGNED NOT NULL,
    `stock_id`          INT(11) UNSIGNED NULL DEFAULT NULL,
    `part_id`           INT(11) UNSIGNED NOT NULL,
    `option_id`         INT(11) UNSIGNED NULL DEFAULT NULL,
    `value`             VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create saved_configurations table.
--
CREATE TABLE `#__hp_saved_configurations` (
    `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `context`           VARCHAR(15) NOT NULL DEFAULT 'legacy',
    `order_id`          INT(11) NULL DEFAULT NULL,
    `created_user_id`   INT(11) NULL DEFAULT NULL,
    `deleted`           TINYINT(1) NULL DEFAULT NULL,
    `price`             FLOAT(12,2) NOT NULL DEFAULT '0.00',
    `product`           TEXT NOT NULL,
    `parts`             MEDIUMTEXT NOT NULL,
    `params`            MEDIUMTEXT NOT NULL,
    `created_time`      DATETIME NULL DEFAULT NULL,
    `modified_time`     DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = COMPACT
    AUTO_INCREMENT  = 1
;

--
-- Create moysklad webhooks table.
--
CREATE TABLE `#__hp_moysklad_webhooks` (
    `id`            INT(10) NOT NULL AUTO_INCREMENT,
    `uuid`          VARCHAR(36) NOT NULL,
    `entity_type`   VARCHAR(255) NOT NULL,
    `action`        ENUM('create','update','delete') NOT NULL,
    `url`           VARCHAR(150) NOT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create games table.
--
CREATE TABLE `#__hp_games` (
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(255) NOT NULL,
    `alias`        VARCHAR(255) NOT NULL,
    `published`    TINYINT(1) NOT NULL DEFAULT '0',
    `default_game` TINYINT(1) NOT NULL DEFAULT '0',
    `ordering`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `params`       MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE     = 'utf8_general_ci'
    ENGINE      = InnoDB
    ROW_FORMAT  = DYNAMIC
;

--
-- Create promo codes table.
--
CREATE TABLE `#__hp_promo_codes` (
    `id`                 INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`               VARCHAR(255) NOT NULL,
    `type`               TINYINT(1) NULL DEFAULT NULL,
    `description`        TEXT NOT NULL,
    `published`          TINYINT(1) NOT NULL DEFAULT '0',
    `params`             MEDIUMTEXT NOT NULL,
    `context`            VARCHAR(255) NOT NULL DEFAULT 'com_hyperpc.product',
    `positions`          TEXT NULL,
    `parts`              TEXT NULL,
    `products`           TEXT NULL,
    `rate`               FLOAT NULL DEFAULT NULL,
    `limit`              INT(10) NULL DEFAULT NULL,
    `used `              INT(10) NULL DEFAULT NULL,
    `publish_up`         DATETIME NULL DEFAULT NULL,
    `publish_down`       DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
-- Create leads table.
--
CREATE TABLE `#__hp_leads` (
    `id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`  VARCHAR(255) NOT NULL,
    `email`     VARCHAR(255) NOT NULL,
    `phone`     VARCHAR(18) NULL DEFAULT NULL,
    `params`    TEXT NULL,
    `history`   MEDIUMTEXT NULL,
    `consent`   TINYINT(1) NOT NULL DEFAULT '0',
    `type`      INT(1) NOT NULL DEFAULT '0',
    `created`   DATETIME NULL DEFAULT NULL,
    `modified`  DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
-- Create workers table.
--
CREATE TABLE `#__hp_workers` (
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`                  VARCHAR(100) NOT NULL,
    `published`             TINYINT(1) NOT NULL DEFAULT '0',
    `params`                MEDIUMTEXT NOT NULL,
    `last_order_turn`       DATETIME NULL DEFAULT NULL,
    `last_form_turn`        DATETIME NULL DEFAULT NULL,
    `last_amo_crm_action`   DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 13
;

--
-- Dump workers table.
--
INSERT INTO `#__hp_workers` (`id`, `name`, `published`, `params`) VALUES
(1, 'Администратор', 1, '{\n    \"amo_responsible_user_id\": \"2795269\"\n}'),
(2, 'Васильев Михаил', 1, '{\n    \"amo_responsible_user_id\": \"2791858\"\n}'),
(3, 'Горчанюк Виктор', 1, '{\n    \"amo_responsible_user_id\": \"2791975\"\n}'),
(4, 'Гулькин Юрий', 1, '{\n    \"amo_responsible_user_id\": \"2791993\"\n}'),
(5, 'Меркурьев Максим', 1, '{\n    \"amo_responsible_user_id\": \"2792026\"\n}'),
(6, 'Скляров Сергей', 1, '{\n    \"amo_responsible_user_id\": \"2792038\"\n}'),
(7, 'Бедник Алексей', 1, '{\n    \"amo_responsible_user_id\": \"2795716\"\n}'),
(8, 'Кривошеев Владислав', 1, '{\n    \"amo_responsible_user_id\": \"2854504\"\n}'),
(9, 'Менеджеры соц. сети', 1, '{\n    \"amo_responsible_user_id\": \"2894176\"\n}'),
(10, 'Александр', 1, '{\n    \"amo_responsible_user_id\": \"2789113\"\n}'),
(11, 'Маркетинг', 1, '{\n    \"amo_responsible_user_id\": \"2789116\"\n}'),
(12, 'Производство', 1, '{\n    \"amo_responsible_user_id\": \"2891530\"\n}');


--
-- Create statuses table.
--
CREATE TABLE `#__hp_statuses` (
    `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `pipeline_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `name`          VARCHAR(100) NOT NULL,
    `published`     TINYINT(1) NOT NULL DEFAULT '0',
    `params`        MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 32
;

--
-- Dump statuses table.
--
INSERT INTO `#__hp_statuses` (`id`, `pipeline_id`, `name`, `published`, `params`) VALUES
(1, 1364734, 'Заказ оформлен', 1, '{\n    \"amo_status_id\": \"21912211\",\n    \"color\": \"#d17171\"\n}'),
(2, 1364734, 'Выставлен счет', 1, '{\n    \"amo_status_id\": \"21989422\"\n}'),
(3, 1364734, 'Счет оплачен', 1, '{\n    \"amo_status_id\": \"22358122\",\n    \"color\": \"#375ddb\"\n}'),
(4, 1453930, 'Отказ по кредиту', 1, '{\n    \"amo_status_id\": \"22762291\"\n}'),
(5, 1364734, 'Кредит одобрен', 1, '{\n    \"amo_status_id\": \"22358119\"\n}'),
(6, 1364734, 'Передан на производство', 1, '{\n    \"amo_status_id\": \"21917599\"\n}'),
(7, 1428370, 'Добавлен в очередь', 1, '{\n    \"amo_status_id\": \"22357714\"\n}'),
(8, 1428370, 'На сборке (ожидаем комплектующие)', 1, '{\n    \"amo_status_id\": \"22357720\"\n}'),
(9, 1428370, 'На сборке', 1, '{\n    \"amo_status_id\": \"22357717\"\n}'),
(10, 1428370, 'Тестируется', 1, '{\n    \"amo_status_id\": \"22358266\"\n}'),
(11, 1428370, 'Срочный заказ', 1, '{\n    \"amo_status_id\": \"22358269\"\n}'),
(12, 1428370, 'На кастомизации', 1, '{\n    \"amo_status_id\": \"22358272\"\n}'),
(13, 1428370, 'Готов и упакован', 1, '{\n    \"amo_status_id\": \"142\"\n}'),
(14, 1364734, 'Готов к отгрузке', 1, '{\n    \"amo_status_id\": \"21912229\"\n}'),
(15, 1364734, 'Передан курьеру (по Москве)', 1, '{\n    \"amo_status_id\": \"22358143\"\n}'),
(16, 1364734, 'Передан на отправку (по России)', 1, '{\n    \"amo_status_id\": \"22358146\"\n}'),
(17, 1364734, 'Заказ выполнен', 1, '{\n    \"amo_status_id\": \"142\"\n}'),
(18, 1364734, 'Заявка в кредит', 1, '{\n    \"amo_status_id\": \"22446577\"\n}'),
(19, 1364734, 'Ожидает поступления', 1, '{\n    \"amo_status_id\": \"22668208\"\n}'),
(20, 1453930, 'Дубль', 1, '{\n    \"amo_status_id\": \"22705987\",\n    \"color\": \"#ffffff\"\n}'),
(21, 1453930, 'Не корректные данные', 1, '{\n    \"amo_status_id\": \"22705990\",\n    \"color\": \"#ffffff\"\n}'),
(22, 1453930, 'Не берет трубку', 1, '{\n    \"amo_status_id\": \"22705993\",\n    \"color\": \"#ffffff\"\n}'),
(23, 1453930, 'Не абонент', 1, '{\n    \"amo_status_id\": \"22705996\",\n    \"color\": \"#ffffff\"\n}'),
(24, 1453930, 'Нет денег', 1, '{\n    \"amo_status_id\": \"22705999\",\n    \"color\": \"#ffffff\"\n}'),
(25, 1453930, 'Не адекватный', 1, '{\n    \"amo_status_id\": \"22706002\",\n    \"color\": \"#ffffff\"\n}'),
(26, 1453930, 'Купил у конкурентов', 1, '{\n    \"amo_status_id\": \"22706005\",\n    \"color\": \"#ffffff\"\n}'),
(27, 1453930, 'Передумал покупать', 1, '{\n    \"amo_status_id\": \"22706008\",\n    \"color\": \"#ffffff\"\n}'),
(28, 1453930, 'У нас такого нет', 1, '{\n    \"amo_status_id\": \"22706011\",\n    \"color\": \"#ffffff\"\n}'),
(29, 1453930, 'Реклама', 1, '{\n    \"amo_status_id\": \"23097610\",\n    \"color\": \"#ffffff\"\n}'),
(30, 1453930, 'Не смогли обработать', 1, '{\n    \"amo_status_id\": \"22584100\",\n    \"color\": \"#ffffff\"\n}'),
(31, 1364734, 'Отказ', 1, '{\n    \"amo_status_id\": \"143\",\n    \"color\": \"#ffffff\"\n}');

--
-- Create form records table.
--
CREATE TABLE `#__hp_form_records` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_id` INT(11) UNSIGNED NOT NULL,
    `subject`   VARCHAR(255) NOT NULL,
    `recipient` VARCHAR(255) NOT NULL,
    `elements`  TEXT NOT NULL,
    `user_id`   INT(11) UNSIGNED NOT NULL,
    `params`    TEXT NOT NULL,
    `context`   VARCHAR(50) NULL DEFAULT NULL,
    `created`   DATETIME NOT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 2
;

--
-- Create compatibilities records table.
--
CREATE TABLE `#__hp_compatibilities` (
    `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`              VARCHAR(100) NULL DEFAULT NULL,
    `name`              VARCHAR(100) NOT NULL,
    `alias`             VARCHAR(45) NOT NULL,
    `description`       TEXT NOT NULL,
    `published`         TINYINT(1) NOT NULL DEFAULT '0',
    `params`            MEDIUMTEXT NOT NULL,
    `created_user_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `created_time`      DATETIME NULL DEFAULT NULL,
    `modified_user_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `modified_time`     DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
COLLATE         = 'utf8_general_ci'
ENGINE          = InnoDB
AUTO_INCREMENT  = 2
;

--
-- Create reviews records table.
--
CREATE TABLE `#__hp_reviews` (
    `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `rating`            FLOAT NULL DEFAULT NULL,
    `order_id`          INT(10) NULL DEFAULT NULL,
    `item_id`           INT(10) NULL DEFAULT NULL,
    `context`           VARCHAR(100) NOT NULL,
    `file`              VARCHAR(100) NOT NULL,
    `params`            TEXT NULL,
    `anonymous`         TINYINT(1) NULL DEFAULT NULL,
    `published`         TINYINT(1) NULL DEFAULT NULL,
    `comment`           TEXT NULL,
    `limitations`       TEXT NULL,
    `virtues`           TEXT NULL,
    `created_time`      DATETIME NULL DEFAULT NULL,
    `created_user_id`   INT(10) NULL DEFAULT NULL,
    `modified_time`     DATETIME NULL DEFAULT NULL,
    `modified_user_id`  INT(10) NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
-- Create user code records table.
--
CREATE TABLE `#__hp_user_codes` (
    `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`          VARCHAR(50) NULL DEFAULT NULL,
    `token`         VARCHAR(150) NULL DEFAULT NULL,
    `count`         INT(10) NULL DEFAULT 0,
    `user_id`       INT(10) NULL DEFAULT NULL,
    `created_time`  DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
-- Create note records table.
--
CREATE TABLE `#__hp_notes` (
    `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_id`           INT(11) NOT NULL,
    `context`           VARCHAR(100) NOT NULL DEFAULT '',
    `note`              TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `created_time`      DATETIME NOT NULL DEFAULT '2017-01-01 00:01:01',
    `created_user_id`   INT(11) NULL DEFAULT NULL,
    `modified_time`     DATETIME NULL DEFAULT NULL,
    `modified_user_id`  INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create order logs table.
--
CREATE TABLE `#__hp_order_logs` (
    `id`            INT(10) NOT NULL AUTO_INCREMENT,
    `order_id`      INT(10) NULL DEFAULT NULL,
    `type`          VARCHAR(100) NULL DEFAULT NULL,
    `content`       MEDIUMTEXT NULL DEFAULT NULL,
    `created_time`  DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE     = 'utf8_general_ci'
    ENGINE      = InnoDB
    ROW_FORMAT  = DYNAMIC
;

--
-- Create form counter table.
--
CREATE TABLE `#__hp_form_counter` (
    `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `context`       VARCHAR(100) NULL DEFAULT NULL,
    `count`         INT(10) NULL DEFAULT 0,
    `value`         VARCHAR(50) NULL DEFAULT NULL,
    `created_time`  DATETIME NULL DEFAULT NULL,
    `updated_time`  DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
-- Create banned ids table.
--
CREATE TABLE `#__hp_banned_ids` (
    `id`            INT(11) NOT NULL AUTO_INCREMENT,
    `ip`            VARCHAR(100) NOT NULL DEFAULT '',
    `banned_up`     DATETIME NULL DEFAULT NULL,
    `banned_down`   DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
--  Create order log table.
--
CREATE TABLE `#__hp_order_logs` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT(10) NULL DEFAULT NULL,
    `type` VARCHAR(100) NULL DEFAULT NULL,
    `content` MEDIUMTEXT NULL DEFAULT NULL,
    `created_time` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
-- Create product folders table.
--
CREATE TABLE `#__hp_product_folders` (
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`                  VARCHAR(36) NOT NULL,
    `lft`                   INT(11) NOT NULL DEFAULT 0,
    `rgt`                   INT(11) NOT NULL DEFAULT 0,
    `level`                 INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `path`                  VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
    `title`                 VARCHAR(100) NOT NULL,
    `alias`                 VARCHAR(45) NOT NULL,
    `parent_id`             INT(10) NOT NULL,
    `published`             TINYINT(1) NOT NULL DEFAULT 0,
    `params`                MEDIUMTEXT NOT NULL,
    `created_user_id`       INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_time`          DATETIME NULL DEFAULT NULL,
    `modified_user_id`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `modified_time`         DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`uuid`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create root folder.
--
INSERT INTO `#__hp_product_folders` (
    `id`,
    `uuid`,
    `lft`,
    `rgt`,
    `level`,
    `path`,
    `title`,
    `alias`,
    `parent_id`,
    `published`,
    `params`
) VALUES (
    1, '', 0, 1, 0, '', 'ROOT', 'root', 0, 1, '{}'
);

--
-- Create product folders translations table.
--
CREATE TABLE `#__hp_product_folders_translations` (
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_id`             INT(10) UNSIGNED NOT NULL,
    `lang_code`             VARCHAR(5) NOT NULL,
    `description`           MEDIUMTEXT NOT NULL,
    `translatable_params`   MEDIUMTEXT NOT NULL,
    `metadata`              MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_entity_id` (`entity_id`),
    CONSTRAINT `fk_product_folders_id` FOREIGN KEY (`entity_id`) REFERENCES `#__hp_product_folders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create position types table.
--
CREATE TABLE `#__hp_position_types` (
    `id`                    TINYINT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
    `alias`                 VARCHAR(45) NOT NULL,
    `name`                  VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create types.
--
INSERT INTO `#__hp_position_types` (
    `id`,
    `alias`,
    `name`
) VALUES
    (1, 'service', 'Услуга'),
    (2, 'part', 'Комплектующая'),
    (3, 'product', 'Продукт');

--
-- Create positions table.
--
CREATE TABLE `#__hp_positions` (
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`                  VARCHAR(36) NOT NULL,
    `product_folder_id`     INT(10) UNSIGNED NOT NULL,
    `type_id`               TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `alias`                 VARCHAR(45) NOT NULL,
    `name`                  VARCHAR(100) NOT NULL,
    `state`                 TINYINT(1) NOT NULL DEFAULT 0,
    `ordering`              INT(10) NOT NULL DEFAULT 10,
    `list_price`            FLOAT(12,2) NOT NULL DEFAULT 0.00,
    `sale_price`            FLOAT(12,2) NOT NULL DEFAULT 0.00,
    `vat`                   TINYINT(2) UNSIGNED NOT NULL DEFAULT 20,
    `images`                MEDIUMTEXT NOT NULL,
    `barcodes`              TEXT NOT NULL,
    `params`                MEDIUMTEXT NOT NULL,
    `created_user_id`       INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_time`          DATETIME NULL DEFAULT NULL,
    `modified_user_id`      INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `modified_time`         DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`uuid`),
    FOREIGN KEY (`product_folder_id`) REFERENCES `#__hp_product_folders`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`type_id`) REFERENCES `#__hp_position_types`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create positions translations table.
--
CREATE TABLE `#__hp_positions_translations` (
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_id`             INT(10) UNSIGNED NOT NULL,
    `lang_code`             VARCHAR(5) NOT NULL,
    `description`           MEDIUMTEXT NOT NULL,
    `translatable_params`   MEDIUMTEXT NOT NULL,
    `metadata`              MEDIUMTEXT NOT NULL,
    `review`                MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_entity_id` (`entity_id`),
    CONSTRAINT `fk_positions_id` FOREIGN KEY (`entity_id`) REFERENCES `#__hp_positions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create services table.
--
CREATE TABLE `#__hp_moysklad_services` (
    `id`                    INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id`) REFERENCES `#__hp_positions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE = 'utf8_general_ci'
    ENGINE  = InnoDB
;

--
-- Create parts table.
--
CREATE TABLE `#__hp_moysklad_parts` (
    `id`                    INT(10) UNSIGNED NOT NULL,
    `balance`               MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `options_count`         TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
    `vendor_code`           VARCHAR(70) NULL DEFAULT NULL,
    `retail`                TINYINT(1) NOT NULL DEFAULT -1,
    `preorder`              TINYINT(1) NOT NULL DEFAULT -1,
    `width`                 FLOAT(12,2) NULL DEFAULT 0.00,
    `height`                FLOAT(12,2) NULL DEFAULT 0.00,
    `length`                FLOAT(12,2) NULL DEFAULT 0.00,
    `weight`                FLOAT(12,2) NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id`) REFERENCES `#__hp_positions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE = 'utf8_general_ci'
    ENGINE  = InnoDB
;

--
-- Create variants table.
--
CREATE TABLE `#__hp_moysklad_variants` (
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`                  VARCHAR(36) NOT NULL,
    `part_id`               INT(10) UNSIGNED NOT NULL,
    `alias`                 VARCHAR(45) NOT NULL,
    `name`                  VARCHAR(100) NOT NULL,
    `state`                 TINYINT(1) NOT NULL DEFAULT 0,
    `ordering`              INT(10) NOT NULL DEFAULT 10,
    `balance`               MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `vendor_code`           VARCHAR(70) NULL DEFAULT NULL,
    `list_price`            FLOAT(12,2) NOT NULL DEFAULT 0.00,
    `sale_price`            FLOAT(12,2) NOT NULL DEFAULT 0.00,
    `images`                MEDIUMTEXT NOT NULL,
    `description`           TEXT NOT NULL,
    `review`                MEDIUMTEXT NOT NULL,
    `params`                MEDIUMTEXT NOT NULL,
    `metadata`              MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`uuid`),
    FOREIGN KEY (`part_id`) REFERENCES `#__hp_moysklad_parts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE = 'utf8_general_ci'
    ENGINE  = InnoDB
;

--
-- Create moysklad variants translations table.
--
CREATE TABLE `#__hp_moysklad_variants_translations` (
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_id`             INT(10) UNSIGNED NOT NULL,
    `lang_code`             VARCHAR(5) NOT NULL,
    `description`           MEDIUMTEXT NOT NULL,
    `translatable_params`   MEDIUMTEXT NOT NULL,
    `metadata`              MEDIUMTEXT NOT NULL,
    `review`                MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_entity_id` (`entity_id`),
    CONSTRAINT `fk_moysklad_variants_id` FOREIGN KEY (`entity_id`) REFERENCES `#__hp_moysklad_variants`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create moysklad stores table.
--
CREATE TABLE `#__hp_moysklad_stores` (
    `id`                    SMALLINT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`                  VARCHAR(36) NOT NULL,
    `lft`                   INT(11) NOT NULL DEFAULT 0,
    `rgt`                   INT(11) NOT NULL DEFAULT 0,
    `level`                 INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `path`                  VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
    `name`                  VARCHAR(100) NOT NULL,
    `alias`                 VARCHAR(45) NOT NULL,
    `parent_id`             SMALLINT(1) UNSIGNED NOT NULL,
    `geoid`                 INT(10) UNSIGNED NULL DEFAULT NULL,
    `published`             TINYINT(1) NOT NULL DEFAULT 0,
    `params`                MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`uuid`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    AUTO_INCREMENT  = 1
;

--
-- Create root store.
--
INSERT INTO `#__hp_moysklad_stores` (
    `id`,
    `uuid`,
    `lft`,
    `rgt`,
    `level`,
    `path`,
    `name`,
    `alias`,
    `parent_id`,
    `published`,
    `params`
) VALUES (
    1, '', 0, 1, 0, '', 'ROOT', 'root', 0, 1, '{}'
);

--
-- Create moysklad store items table.
--
CREATE TABLE `#__hp_moysklad_store_items` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `store_id`  SMALLINT(1) UNSIGNED NOT NULL,
    `item_id`   INT(10) UNSIGNED NOT NULL,
    `option_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `balance`   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`store_id`) REFERENCES `#__hp_moysklad_stores`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `#__hp_positions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
-- Create microtransactions table.
--
CREATE TABLE `#__hp_microtransactions` (
    `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `purchase_key`          VARCHAR(100) NOT NULL,
    `description`           TEXT NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
    `total`                 FLOAT(12,2) NOT NULL DEFAULT 0.00,
    `player`                VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `paid`                  TINYINT(1) NOT NULL DEFAULT '0',
    `module_id`             INT(11) UNSIGNED NOT NULL,
    `activated`             TINYINT(1) NOT NULL DEFAULT '0',
    `created_user_id`       INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_time`          DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE         = 'utf8_general_ci'
    ENGINE          = InnoDB
    ROW_FORMAT      = DYNAMIC
    AUTO_INCREMENT  = 1
;

--
-- Create moysklad_products table.
--
CREATE TABLE `#__hp_moysklad_products` (
    `id`                INT(10) UNSIGNED NOT NULL,
    `on_sale`           TINYINT(1) NOT NULL DEFAULT 0,
    `vendor_code`       VARCHAR (70) NULL DEFAULT NULL,
    `configuration`     MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id`) REFERENCES `#__hp_positions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE    = 'utf8_general_ci'
    ENGINE     = InnoDB
    ROW_FORMAT = DYNAMIC
;

--
-- Create product variants table.
--
CREATE TABLE `#__hp_moysklad_product_variants` (
    `id`                    INT(10) UNSIGNED NOT NULL,
    `uuid`                  VARCHAR(36) NOT NULL,
    `context`               VARCHAR(15) NOT NULL DEFAULT 'variant',
    `product_id`            INT(10) UNSIGNED NOT NULL,
    `name`                  VARCHAR(100) NOT NULL,
    `list_price`            FLOAT(12,2) NOT NULL DEFAULT 0.00,
    `sale_price`            FLOAT(12,2) NOT NULL DEFAULT 0.00,
    `created_time`          DATETIME NULL DEFAULT NULL,
    `modified_time`         DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`name`),
    FOREIGN KEY (`product_id`) REFERENCES `#__hp_moysklad_products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE     = 'utf8_general_ci'
    ENGINE      = InnoDB
    ROW_FORMAT  = DYNAMIC
;

--
-- Create processingplans table.
--
CREATE TABLE `#__hp_processingplans` (
    `id`                    INT(10) UNSIGNED NOT NULL,
    `uuid`                  VARCHAR(36) NOT NULL,
    `name`                  VARCHAR(100) NOT NULL,
    `parts`                 TEXT NOT NULL,
    `created_time`          DATETIME NULL DEFAULT NULL,
    `modified_time`         DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id`) REFERENCES `#__hp_moysklad_product_variants`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    COLLATE     = 'utf8_general_ci'
    ENGINE      = InnoDB
    ROW_FORMAT  = DYNAMIC
;

--
-- Create price recount queue table.
--
CREATE TABLE `#__hp_price_recount_queue` (
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `part_id`               INT(10) UNSIGNED NOT NULL,
    `option_id`             INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`)
)
    COLLATE     = 'utf8_general_ci'
    ENGINE      = InnoDB
    ROW_FORMAT  = DYNAMIC
;

--
-- Create deal map table.
--
CREATE TABLE `#__hp_deal_map` (
    `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`              INT UNSIGNED NULL DEFAULT NULL,
    `moysklad_order_uuid`   CHAR(36) NULL DEFAULT NULL,
    `crm_lead_id`           INT UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `order_id` (`order_id`),
    UNIQUE `moysklad_order_uuid` (`moysklad_order_uuid`),
    UNIQUE `crm_lead_id` (`crm_lead_id`)
)
    COLLATE     = 'utf8_general_ci'
    ENGINE      = InnoDB
    ROW_FORMAT  = DYNAMIC
;

--
-- Create email template.
--
INSERT INTO `#__mail_templates` (
    `template_id`,
    `extension`,
    `language`,
    `subject`,
    `body`,
    `htmlbody`,
    `attachments`,
    `params`)
VALUES (
    'com_hyperpc.mail',
    'com_hyperpc',
    '',
    'COM_HYPERPC_EMAIL_TEMPLATE_SUBJECT',
    'COM_HYPERPC_EMAIL_TEMPLATE_BODY',
    '',
    '',
    '{\"tags\":[\"subject\",\"heading\",\"message\",\"year\",\"reason\",\"order\",\"ordernumber\",\"orderlink\",\"orderdate\",\"pickup\",\"storeaddress\",\"readydate\",\"shipping\",\"deliveryservice\",\"deliveryaddress\",\"price\",\"clientname\",\"clienttype\",\"clientphone\",\"clientemail\",\"payment\",\"positions\",\"title\",\"category\",\"image\",\"price\",\"quantity\",\"value\",\"unitprice\",\"positionscount\",\"productsprice\",\"servicesprice\",\"discount\",\"ordertotal\",\"configuration\",\"number\",\"date\",\"specification\",\"sectiontitle\",\"sectionprice\",\"items\",\"category\",\"itemname\",\"total\"]}'
);

--
-- Table structure for table #__hp_moysklad_characteristics.
--
CREATE TABLE `#__hp_moysklad_characteristics` (
    `uuid`      CHAR(36) NOT NULL,
    `name`      VARCHAR(255) NOT NULL,
    `params`    MEDIUMTEXT NULL DEFAULT NULL,
    PRIMARY KEY (`uuid`)
)
    ENGINE          = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
;

--
-- Table structure for table #__hp_moysklad_characteristics_values.
--
CREATE TABLE `#__hp_moysklad_characteristics_values` (
    `variant_id`        INT UNSIGNED NOT NULL,
    `characteristic`    CHAR(36) NOT NULL,
    `value`             VARCHAR(255) NOT NULL,
    `ordering`          INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`variant_id`, `characteristic`),
    CONSTRAINT `fk_variant_id` FOREIGN KEY (`variant_id`) REFERENCES `#__hp_moysklad_variants`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
    ENGINE          = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
;