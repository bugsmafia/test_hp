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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Form;
use HYPERPC\Helper\PositionHelper;
use Joomla\Database\ParameterType;
use HYPERPC\Joomla\Model\ModelAdmin;
use Joomla\CMS\Language\LanguageHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Class HyperPcModelPosition
 *
 * @method   PositionHelper getHelper()
 * @method   \HyperPcTablePositions getTable()
 *
 * @since   2.0
 */
class HyperPcModelPosition extends ModelAdmin
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
        return ['state', 'list_price', 'sale_price', 'image', 'image_gallery'];
    }

    public function getEntityFields()
    {
        return [];
    }

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->setHelper($this->hyper['helper']['position']);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  Position
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var Position $item */
        $item = clone $this->getItem();

        $item = $item->getArray();
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

        return new $entityClass($item);
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
        $pk   = $this->getState($name . '.id');
        $form = $this->loadForm(HP_OPTION . '.' . $name, $name, [
            'control'   => 'jform',
            'load_data' => $loadData
        ]);

        $position = $this->_helper->findById($pk);
        $position->set('fieldscatid', $position->product_folder_id);
        FieldsHelper::prepareForm('com_hyperpc.position', $form, $position);

        return $form;
    }

    /**
     * Get position object by id.
     *
     * @param   int $id
     *
     * @return  Position
     *
     * @throws  RuntimeException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getItem($id = null)
    {
        $position = parent::getItem($id);
        if (!($position instanceof Position) || !$position->id) {
            return new Position();
        }

        return $this->_helper->expandToSubtype($position);
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

        $positionData = $data;

        foreach ($this->getEntityFields() as $field) {
            unset($positionData[$field]);
        }

        $fields = FieldsHelper::getFields('com_hyperpc.position');

        if (count($fields)) {
            JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');
            $fieldModel = JModelLegacy::getInstance('Field', 'FieldsModel', array('ignore_request' => true));

            foreach ($fields as $field) {
                $fieldModel->setFieldValue($field->id, $positionData['id'], $positionData['com_fields'][$field->name]);
            }
        }

        /** @var HyperPcTablePositions */
        $positionsTable = $this->getTable('Positions', HP_TABLE_CLASS_PREFIX, []);

        $positionsTable->load($pk);
        $positionsTable->setProperties($positionData);
        if (!$positionsTable->save($positionData)) {
            $this->setError($positionsTable->getError());
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
            $entityId = (int) $positionsTable->id;

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
}
