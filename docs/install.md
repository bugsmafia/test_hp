# 1 . Установка для разработки
1. Сделайте checkout репозитория
2. Перейдите в папку репозитория и выполните `composer update`
3. Создайте Joomla пакет для установки выполнив: `./scripts/package.sh`
4. Через менеджер установки Joomla установите пакет **pkg_hyperpc**
5. После успешной установки всех пакетов необходимо выставить аналогичные сим линки на папки из репозитория в проект с Joomla! Для этого можно использовать [FAR менеджер](http://www.farmanager.com/download.php?l=ru)
```
repository/src/com_hyperpc/admin    ->  site.my/administrator/components/com_hyperpc
repository/src/com_hyperpc/site     ->  site.my/components/com_hyperpc
repository/assets/hyperpc           ->  site.my/media/hyperpc
repository/libraries/hyperpc        ->  site.my/libraries/hyperpc
repository/src/plg_sys_hyperpc      ->  site.my/plugins/system/hyperpc
repository/src/plg_cont_hyperpc     ->  site.my/plugins/content/hyperpc
```
6. Выполните запрос в базу данных (Установка типа контента для использования тегов в товарах)
```
INSERT INTO `p6wjk_content_types` (`rules`, `type_title`, `type_alias`, `table`, `field_mappings`) VALUES ('', 'Product', 'com_hyperpc.product', '{"special": {"dbtable": "#__products", "key": "id", "type": "Product", "prefix": "HyperPcTable", "config": "array()"}, "common": {"dbtable": "#__ucm_content", "key": "ucm_id", "type": "Corecontent", "prefix": "JTable", "config": "array()"}}', '{"common": {"core_content_item_id": "id", "core_title": "name", "core_state": "published", "core_alias": "alias", "core_created_time": "created_time", "core_modified_time": "modified_time", "core_body": "description", "core_hits": "hits", "core_publish_up": "null", "core_publish_down": "null", "core_access": "access", "core_params": "params", "core_featured": "null", "core_metadata": "metadata", "core_language": "language", "core_images": "null", "core_urls": "null", "core_version": "version", "core_ordering": "null", "core_metakey": "metakey", "core_metadesc": "metadesc", "core_catid": "parent_id", "core_xreference": "null", "asset_id": "asset_id"}, "special": {"parent_id": "parent_id", "lft": "lft", "rgt": "rgt", "level": "level", "path": "path", "extension": "extension", "note": "note"}}');
```
7. Включите плагины Joomla и проверьте работу компанента