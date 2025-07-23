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
 */

namespace HYPERPC\Printer\Configurator;

use Dompdf\Dompdf;
use HYPERPC\Data\JSON;
use JBZoo\Utils\FS;
use JBZoo\Utils\Slug;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use HYPERPC\Printer\PrinterAbstract;
use HYPERPC\Joomla\Model\Entity\Requisite;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * Class Printer
 *
 * @package HYPERPC\Printer\Configurator
 *
 * @since   2.0
 */
class Printer extends PrinterAbstract
{

    /**
     * Name of printer.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_name = 'configurator';

    /**
     * Hold configuration data.
     *
     * @var     SaveConfiguration
     *
     * @since   2.0
     */
    protected static $_configuration;

    /**
     * Printer params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    protected $_params;

    /**
     * Printer constructor.
     *
     * @param   array $values
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $this->_params = new JSON([
            'template' => 'default.php'
        ]);
    }

    /**
     * Setup params.
     *
     * @param   array $params
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setParams(array $params)
    {
        $this->_params = new JSON($params);
        return $this;
    }

    /**
     * Get param.
     *
     * @param   string|null $key
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getParam($key = null)
    {
        return ($key) ? $this->_params->find($key) : $this->_params;
    }

    /**
     * Setup configuration.
     *
     * @param   SaveConfiguration $configuration
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setConfiguration(SaveConfiguration $configuration)
    {
        self::$_configuration = $configuration;
        $configuration->product->set('saved_configuration', self::$_configuration->id);
        return $this;
    }

    /**
     * Build PDF.
     *
     * @throws  \Exception
     *
     * @return  void
     *
     * @since   2.0
     */
    public function build()
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/pdf');

        $PDF = new Dompdf();

        $PDF->getOptions()->setChroot([
            realpath(JPATH_ROOT . '/media/hyperpc'),
            realpath(JPATH_ROOT . '/administrator/components/com_hyperpc/framework/Printer/'),
            realpath(JPATH_ROOT . '/images'),
            realpath(JPATH_ROOT . '/cache')
        ]);
        $PDF->setPaper('A4', 'portrait');
        $PDF->loadHtml($this->html());
        $PDF->render();

        $PDF->stream($this->getPageTitle(), [
            'Attachment' => 0
        ]);

        $this->hyper['cms']->close();
    }

    /**
     * Save file on server.
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function save()
    {
        $PDF = new Dompdf();

        $PDF->getOptions()->setChroot([
            realpath(JPATH_ROOT . '/media/hyperpc'),
            realpath(JPATH_ROOT . '/administrator/components/com_hyperpc/framework/Printer/'),
            realpath(JPATH_ROOT . '/images'),
            realpath(JPATH_ROOT . '/cache')
        ]);
        $PDF->loadHtml($this->html());
        $PDF->setPaper('A4', 'portrait');
        $PDF->render();

        $output = $PDF->output();

        if (!$this->hyper['path']->get('cache:pdf')) {
            Folder::create($this->hyper['path']->get('cache:') . '/hp_pdf') ;
        }

        $filePath = $this->hyper['path']->get('cache:hp_pdf') . '/' . $this->getAliasName() . '.pdf';
        file_put_contents($filePath, $output);
        return $filePath;
    }

    /**
     * Get page title.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPageTitle()
    {
        $product = self::$_configuration->getProduct();
        return $product->name . ' (' . Text::sprintf(
            'COM_HYPERPC_CONFIGURATION_PDF_NUMBER',
            self::$_configuration->getName()
        ) . ')';
    }

    /**
     * Get product alias name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAliasName()
    {
        $product = self::$_configuration->getProduct();
        return Slug::filter(implode('-', [
            $product->name,
            'specification',
            self::$_configuration->getName()
        ]));
    }

    /**
     * Get configuration full url.
     *
     * @throws  \Exception
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getConfigLink()
    {
        $product = self::$_configuration->getProduct();
        return Uri::root() . trim($product->getConfigUrl(self::$_configuration->id), '/');
    }

    /**
     * Render output html.
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function html()
    {
        $requisite = new Requisite();
        $template  = $this->_params->get('template', 'default.php');

        if (empty(FS::ext($template))) {
            $template .= '.php';
        }

        return $this->hyper['helper']['render']->render('Configurator/tmpl/' . $template, [
            'printer'       => $this,
            'requisite'     => $requisite,
            'configuration' => self::$_configuration
        ], 'printer');
    }
}
