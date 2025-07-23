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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Fields\OptionFields;
use Joomla\CMS\Language\LanguageHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * Class HyperPcModelMoysklad_Variant
 *
 * @since   2.0
 */
class HyperPcModelMoysklad_Variant extends ModelAdmin
{

    /**
     * Get global fields for form render.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getGlobalFields()
    {
        return [
            'state', 'ordering', 'list_price', 'sale_price', 'moysklad_store_items', 'balance', 'image', 'image_gallery', 'vendor_code'
        ];
    }

    /**
     * Getting the form from the model.
     *
     * @param array $data
     * @param bool $loadData
     *
     * @return  bool|Form
     *
     * @throws Exception
     *
     * @since   2.0
     */
    public function getForm($data = [], $loadData = true)
    {
        $name = $this->getName();
        $form = $this->loadForm(HP_OPTION . '.' . $name, $name, [
            'control'   => 'jform',
            'load_data' => $loadData
        ]);

        $context  = HP_OPTION . '.position';
        $position = $this->hyper['helper']['moyskladPart']->findById($form->getData()->get('part_id'));

        OptionFields::prepareForm($context, $form, ['item' => $position]);

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  MoyskladVariant
     *
     * @throws  Exception
     * @throws  RuntimeException
     * @throws  JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var MoyskladVariant $item */
        $item = clone $this->getItem();

        $item = $item->getArray();
        $id = $item['id'] ?? 0;

        $helper = $this->hyper['helper']['moyskladVariant'];

        $entityClass = $this->getTable()->getEntity();

        $translationTable   = $helper->getTranslationsTable();
        $translatableFields = $helper->getTranslatableFields();
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
                if ($column === 'review') {
                    $item[$column][$langData->sef] = $prop;
                } elseif (\is_array($prop)) {
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
     * @param   array $data
     *
     * @return  bool
     *
     * @throws  Exception
     * @throws  RuntimeException
     * @throws  InvalidArgumentException
     * @throws  UnexpectedValueException
     *
     * @since   2.0
     */
    public function save($data)
    {
        $pk = $data['id'];

        $table = \HYPERPC\ORM\Table\Table::getInstance('Moysklad_Variants');

        $table->load($pk);
        $table->setProperties($data);
        if (!$table->save($data)) {
            $this->setError($table->getError());
            return false;
        }

        $helper = $this->hyper['helper']['moyskladVariant'];

        // Save translatable fields
        $translatableFields = $helper->getTranslatableFields();
        $translationsTable  = $helper->getTranslationsTable();

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

        return parent::save($data);
    }

    /**
     * Get table object.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     *
     * @return  Table
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Moysklad_Variants', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }
}
