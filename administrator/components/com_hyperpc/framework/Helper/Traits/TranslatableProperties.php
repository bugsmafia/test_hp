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

namespace HYPERPC\Helper\Traits;

use Joomla\CMS\Factory;
use HYPERPC\ORM\Table\Table;
use Joomla\Database\DatabaseQuery;
use Joomla\CMS\Table\Table as JTable;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Trait EntitySubtype
 *
 * @package HYPERPC\Helper\Traits
 */
trait TranslatableProperties
{
    /**
     * Get table.
     *
     * @return  JTable
     *
     * @throws  \Exception
     */
    abstract public function getTable(): JTable;

    /**
     * Get translations table name.
     *
     * @return  string
     */
    abstract public function getTranslationsTableName(): string;

    /**
     * Get array of translatable fields.
     *
     * @return  array
     */
    abstract public function getTranslatableFields(): array;

    /**
     * Get translations table.
     *
     * @return  Table
     *
     * @throws  \Exception
     */
    public function getTranslationsTable(): Table
    {
        $tableName = $this->getTranslationsTableName();
        $table = Table::getInstance($tableName);
        if (!($table instanceof Table)) {
            throw new \Exception($tableName . ' table not found');
        }

        return $table;
    }

    /**
     * Get language code.
     *
     * @return  string
     */
    private function getLanguageCode(): string
    {
        return Factory::getApplication()->getLanguage()->getTag();
    }

    /**
     * Get default code.
     *
     * @return  string
     */
    private function getDefaultLanguageCode(): string
    {
        $params = ComponentHelper::getParams('com_languages');
        return $params->get('site', 'en-GB');
    }

    /**
     * Get query
     *
     * @return  DatabaseQuery
     */
    protected function _getTraitQuery(): DatabaseQuery
    {
        $table = $this->getTable();
        $translationsTable = $this->getTranslationsTable();

        $db = $table->getDbo();

        $langTag = $this->getLanguageCode();
        $defaultLangTag = $this->getDefaultLanguageCode();

        $query = $db
            ->getQuery(true)
            ->select('a.*')
            ->from($db->qn($table->getTableName(), 'a'))
            ->join(
                'LEFT',
                $db->qn($translationsTable->getTableName(), 'tl1'),
                'a.id = tl1.entity_id AND tl1.lang_code = ' . $db->q($langTag)
            );

        if ($langTag === $defaultLangTag) {
            $query->select($this->getTranslatableFields());
        } else {
            $query
                ->select(array_map(function ($fieldName) {
                    return "COALESCE(tl1.{$fieldName}, tl2.{$fieldName}) AS {$fieldName}";
                }, $this->getTranslatableFields()))
                ->join(
                    'LEFT',
                    $db->qn($translationsTable->getTableName(), 'tl2'),
                    'a.id = tl2.entity_id AND tl2.lang_code = ' . $db->q($defaultLangTag)
                );
        }

        return $query;
    }

    /**
     * Get query for from condition
     *
     * @return  string
     */
    protected function _getFromQuery()
    {
        return '(' . $this->_getTraitQuery() . ') AS a';
    }
}
