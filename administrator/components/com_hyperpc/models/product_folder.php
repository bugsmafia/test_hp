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

use Joomla\Event\Event;
use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Helper\ProductFolderHelper;
use Joomla\CMS\Language\LanguageHelper;

/**
 * Class HyperPcModelProduct_Folder
 *
 * @method  ProductFolderHelper getHelper()
 *
 * @since   2.0
 */
class HyperPcModelProduct_Folder extends ModelAdmin
{
    /**
     * Initialize model hook method.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setHelper($this->hyper['helper']['productFolder']);
    }

    /**
     * Get table object.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     *
     * @return  \HyperPcTableProduct_Folders
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Product_Folders', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        $item = clone $this->getItem();

        unset($item->app, $item->hyper);

        if (property_exists($item, 'published')) {
            $item->set('published', (int) $item->published);
        }

        $parentId = $this->hyper['input']->get('parent_id', 0, 'int');
        if ($parentId !== 0) {
            $item->set('parent_id', $parentId);
        }

        if ($item instanceof Entity) {
            $item = $item->getArray();
        } elseif (!\is_array($item)) {
            $item = (array) $item;
        }

        $id = $item['id'] ?? 0;

        $entityClass = $this->getTable()->getEntity();

        $translationTable = $this->getHelper()->getTranslationsTable();
        $translatableFields = $this->getHelper()->getTranslatableFields();
        $db = $translationTable->getDbo();

        $query = $db
            ->getQuery(true)
            ->from($db->qn($translationTable->getTableName()))
            ->select($db->qn('lang_code'))
            ->select(array_map(function ($column) use ($db) {
                return $db->qn($column);
            }, $translatableFields))
            ->where($db->qn('entity_id') . ' = :entityid')
            ->bind(':entityid', $id, ParameterType::INTEGER);

        $db->setQuery($query);

        $translations = $db->loadAssocList('lang_code');

        $contentLangs = LanguageHelper::getContentLanguages();
        foreach ($contentLangs as $langCode => $langData) {
            if (!key_exists($langCode, $translations)) {
                continue;
            }

            $translation = $translations[$langCode];

            $translatedItem = $item;
            foreach ($translatableFields as $column) {
                $translatedItem[$column] = $translation[$column];
            }

            $entity = (new $entityClass($translatedItem))->getArray();
            foreach ($translatableFields as $column) {
                $prop = $entity[$column];
                if (\is_array($prop)) {
                    foreach ($prop as $paramKey => $value) {
                        $item[$column][$paramKey][$langData->sef] = $value;
                    }
                } else {
                    $item[$column][$langData->sef] = $prop;
                }
            }
        }

        return $item;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  bool  True on success, False on error.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function save($data)
    {
        $pk = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        if ($pk < 1) {
            // Abort saving if folder id is empty
            return false;
        }

        $isNew = false;

        $dispatcher = Factory::getApplication()->getDispatcher();

        $table = $this->getTable();
        $context = $this->option . '.' . $this->name;

        // Include the plugins for the save events.
        PluginHelper::importPlugin($this->events_map['save']);

        $table->load($pk);

        //  Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());
            return false;
        }

        //  Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }

        //  Trigger the before save event.
        $event  = new Event($this->event_before_save, [$context, &$table, $isNew, $data]);
        $result = $dispatcher->dispatch($this->event_before_save, $event);
        if (in_array(false, $result->getArgument('result'), true)) {
            $this->setError($table->getError());
            return false;
        }

        //  Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        // Save translatable fields
        $translatableFields = $this->getHelper()->getTranslatableFields();
        $translationsTable = $this->getHelper()->getTranslationsTable();

        $contentLangs = LanguageHelper::getContentLanguages();

        $langSefs = array_map(function ($langData) {
            return $langData->sef;
        }, $contentLangs);

        foreach ($contentLangs as $langCode => $langData) {
            $db = $this->getDatabase();
            $entityId = (int) $table->id;

            $transalatableData = [
                'entity_id' => $entityId,
                'lang_code' => $langCode,
            ];

            $query = $db->getQuery(true)
                ->select('id')
                ->from($db->quoteName($translationsTable->getTableName()))
                ->where($db->quoteName('lang_code') . ' = :languagecode')
                ->where($db->quoteName('entity_id') . ' = :entityid')
                ->bind(':languagecode', $langCode)
                ->bind(':entityid', $entityId, ParameterType::INTEGER);
            $db->setQuery($query);

            $rowId = $db->loadResult();

            $langSef = $langData->sef;

            foreach ($translatableFields as $fieldName) {
                $fieldData = $data[$fieldName] ?? [];

                if (!\is_array($fieldData) || empty($fieldData)) {
                    $transalatableData[$fieldName] = '';
                } elseif (array_intersect($langSefs, array_keys($fieldData))) { // keys are lang sefs
                    $filedValue = $fieldData[$langSef] ?? '';
                    $transalatableData[$fieldName] = $filedValue;
                } else { // array like field
                    foreach ($fieldData as $paramKey => $paramData) {
                        $paramValue = $paramData[$langSef] ?? '';
                        $transalatableData[$fieldName][$paramKey] = $paramValue;
                    }
                }
            }

            $translationsTable->reset();
            $translationsTable->id = $rowId;

            if (!$translationsTable->save($transalatableData)) {
                return false;
            }
        }

        //  Trigger the after save event.
        $event  = new Event($this->event_after_save, [$context, &$table, $isNew, $data]);
        $dispatcher->dispatch($this->event_after_save, $event);

        // Rebuild the path for the category.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());
            return false;
        }

        //  Rebuild the paths of the category's children.
        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
            $this->setError($table->getError());
            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        //   Clear the cache.
        $this->cleanCache();

        return true;
    }

    /**
     * Get global fields for form render.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getGlobalFields()
    {
        $fields = [
            'parent_id',
            'published',
            'uuid',
        ];

        return $fields;
    }

    /**
     * Method to save the reordered nested set tree.
     *
     * @param   array $idArray
     * @param   int $lft_array
     * @return  bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function saveorder($idArray = null, $lft_array = null)
    {
        //  Get an instance of the table object.
        $table = $this->getTable();
        if (!$table->saveorder($idArray, $lft_array)) {
            $this->setError($table->getError());
            return false;
        }

        //  Clear the cache
        $this->cleanCache();

        return true;
    }
}
