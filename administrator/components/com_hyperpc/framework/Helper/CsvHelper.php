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
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

namespace HYPERPC\Helper;

use JBZoo\Utils\FS;
use Joomla\CMS\Filesystem\Folder;

/**
 * CsvHelper class.
 *
 * @package     HYPERPC\Helper
 *
 * @property    array   $_header
 *
 * @since       2.0
 */
class CsvHelper extends AppHelper
{

    const DELIMITER = ';';
    const ENCLOSURE = '"';

    /**
     * Access mode type
     *
     * @var string
     */
    protected string $_mode = 'a';

    /**
     * Get header line.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getHeader()
    {
        return $this->_header;
    }

    /**
     * Set header line.
     *
     * @param   array $header
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setHeader(array $header)
    {
        $this->_header = $header;
        return $this;
    }

    /**
     * Set access mode type.
     *
     * @param   string $mode
     *
     * @since   2.0
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
    }

    /**
     * Write to file.
     *
     * @param   string  $file
     * @param   array   $data
     * @param   bool    $addHeader
     *
     * @return  bool|string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function toFile($file, array $data, $addHeader = false)
    {
        if (count($data) === 0) {
            return false;
        }

        return $this->_createFile($file, $data, $addHeader);
    }

    /**
     * Create the file.
     *
     * @param   array       $data
     * @param   bool        $addHeader
     * @param   string      $file
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _createFile($file, array $data, $addHeader = false)
    {
        $file    = FS::clean(JPATH_ROOT . '/' . $file);
        $dirName = dirname($file);

        if (!Folder::exists($dirName)) {
            Folder::create($dirName);
        }

        if ($addHeader === true) {
            array_unshift($data, $this->_header);
        }

        if (($handle = fopen($file, $this->_mode)) !== false) {

            foreach ($data as $row) {
                fputcsv($handle, $row, self::DELIMITER, self::ENCLOSURE, "\\");
            }

            fclose($handle);

        } else {
            throw new \Exception(sprintf('Unable to write to file %s.', $file));
        }

        return $file;
    }
}
