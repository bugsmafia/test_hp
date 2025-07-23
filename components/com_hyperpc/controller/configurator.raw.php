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

defined('_JEXEC') or die('Restricted access');

use JBZoo\Data\Data;
use JBZoo\Data\JSON;
use Cake\Utility\Hash;
use Joomla\Event\Event;
use Joomla\CMS\Factory;
use Cake\Utility\Inflector;
use HYPERPC\ORM\Entity\User;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Elements\Manager;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CartHelper;
use Joomla\Utilities\IpHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Helper\ConfigurationHelper;
use HYPERPC\Printer\Configurator\Printer;
use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class HyperPcControllerConfigurator
 *
 * @since 2.0
 */
class HyperPcControllerConfigurator extends ControllerLegacy
{
    /**
     * Hold configurator helper object.
     *
     * @var     ConfiguratorHelper
     *
     * @since   2.0
     */
    protected $_configuratorHelper;

    /**
     * Hold configuration helper object.
     *
     * @var     ConfigurationHelper
     *
     * @since   2.0
     */
    protected $_configurationHelper;

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        $this
            ->registerTask('copy', 'copy')
            ->registerTask('reset', 'reset')
            ->registerTask('create', 'create')
            ->registerTask('update', 'update')

            ->registerTask('build_pdf', 'buildPdf')

            ->registerTask('send_by_email', 'sendByEmail')
            ->registerTask('remove_user_config', 'removeUserConfig')
            ->registerTask('check_configuration', 'checkConfiguration');

        $this->_configuratorHelper  = $this->hyper['helper']['configurator'];
        $this->_configurationHelper = $this->hyper['helper']['configuration'];
    }

    /**
     * Build configuration pdf.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function buildPdf()
    {
        $configId = $this->hyper['input']->get('configuration_id', 0);

        if (empty($configId)) {
            throw new \Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        /** @var SaveConfiguration $configuration */
        $configuration = $this->_configurationHelper->findById($configId);

        if (empty($configuration->id)) {
            throw new \Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        if ($configuration->created_user_id !== $this->hyper['user']->id) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $printer = new Printer();

        $printer
            ->setParams([
                'template' => strtolower($this->hyper['input']->get('pdf_layout', 'default'))
            ])
            ->setConfiguration($configuration)
            ->build();

        $this->hyper['cms']->close();
    }

    /**
     * Action copy configuration by id.
     *
     * @return  void
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function copy()
    {
        $output = new JSON([
            'id'      => null,
            'result'  => false,
            'message' => Text::_('COM_HYPERPC_CONFIGURATOR_COPY_ERROR')
        ]);

        if (strlen($this->hyper['input']->get('token')) > 10) {
            $this->input->post->set($this->hyper['input']->get('token'), 1, 'alnum');
        }

        if (!Session::checkToken()) {
            $output->set('message', Text::_('COM_HYPERPC_CONFIGURATOR_HTTP_X_CSRF_ERROR'));
            $this->hyper['cms']->close($output->write());
        }

        $configurationId = $this->hyper['input']->get('configuration_id');
        $createdUserId   = $this->hyper['input']->get('created_user_id', 0, 'int');

        $configuration = $this->_configurationHelper->findById($configurationId);
        if ($configuration->id) {
            $savedResult = $this->_configurationHelper->copy($configuration->id, $createdUserId);
            if ($savedResult) {
                $output
                    ->set('result', true)
                    ->set('message', null)
                    ->set('id', $savedResult);
            }
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Remove user configuration from personal cabinet.
     *
     * @return  void
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function removeUserConfig()
    {
        $table = $this->_configurationHelper->getTable();
        $db    = $table->getDbo();
        $id    = $this->hyper['input']->get('config_id');

        $query = $db
            ->getQuery(true)->select(['a.*'])
            ->from($db->quoteName($table->getTableName(), 'a'))
            ->where([
                $db->quoteName('a.id')              . ' = ' . $db->quote($id),
                $db->quoteName('a.created_user_id') . ' = ' . $db->quote($this->hyper['user']->id)
            ]);

        $class = $table->getEntity();

        /** @var SaveConfiguration $configuration */
        $configuration = $db->setQuery($query)->loadAssoc();
        $configuration = new $class($configuration);

        $output = new JSON(['result' => false]);
        if ($configuration->id) {
            $configuration->set('deleted', HP_STATUS_PUBLISHED);
            if ($table->save($configuration->getArray())) {
                $output->set('result', true);
            }
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Check configuration.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function checkConfiguration()
    {
        $output          = new JSON(['result' => 'error']);
        $configurationId = $this->hyper['input']->get('configuration_id');
        $configuration   = $this->_configurationHelper->findById($configurationId);

        if ($configuration->id && $configuration->getProduct()->id) {
            $output->set('result', 'success');
        } else {
            $output->set('msg', Text::sprintf('COM_HYPERPC_CONFIGURATOR_CONFIGURATION_NOT_EXIST', $configurationId));
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Reset configurator.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function reset()
    {
        $output = new JSON([
            'price'  => 0,
            'parts'  => [],
            'result' => 'success'
        ]);

        $productId = $this->hyper['input']->get('productId', 0);
        $context   = $this->hyper['input']->get('context', SaveConfiguration::CONTEXT_MOYSKLAD);

        $product = $this->_getProduct($productId, $context);
        if (empty($product->id)) {
            $output->set('msg', Text::sprintf('COM_HYPERPC_CONFIGURATOR_PRODUCT_NOT_EXIST', $productId));
            $this->hyper['cms']->close($output->write());
        }

        $_parts          = [];
        $cleanParts      = [];
        $configurationId = $this->hyper['input']->get('configurationId', 0, 'int');

        if ($configurationId) {
            $configuration = $this->_configurationHelper->findById($configurationId);
            if ($configuration->product->get('id', 0, 'int') === $product->id) {
                $product->set('saved_configuration', $configuration->id);
            }
        }

        if ($product->id > 0) {
            $parts = $product->getConfigParts(
                false,
                'a.product_folder_id ASC',
                false,
                ($configurationId > 0)
            );

            $output->set('price', $product->getConfigPrice(true)->val());

            foreach ($parts as $part) {
                $data = [
                    'option'   => 0,
                    'partId'   => $part->id,
                    'quantity' => $part->quantity ?? 1,
                    'groupId'  => $part->getFolderId()
                ];

                if ($part instanceof PartMarker && $part->option instanceof OptionMarker && $part->option->id) {
                    $data['option'] = $part->option->id;
                }

                $_parts[$part->id] = $data;
            }
        }

        foreach ($_parts as $data) {
            $cleanParts[] = $data;
        }

        $output->set('parts', $cleanParts);

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Configurator ajax update action.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function update()
    {
        $output = new JSON([
            'savedConfiguration' => 0,
            'msg'                => null,
            'result'             => 'error'
        ]);

        $productId = $this->hyper['input']->get('productId', 0);
        $context   = $this->hyper['input']->get('context', SaveConfiguration::CONTEXT_MOYSKLAD);

        $product = $this->_getProduct($productId, $context);
        if (empty($product->id)) {
            $output->set('msg', Text::sprintf('COM_HYPERPC_CONFIGURATOR_PRODUCT_NOT_EXIST', $productId));
            $this->hyper['cms']->close($output->write());
        }

        $parts              = $this->hyper['input']->get('parts', [], 'array');
        $options            = $this->hyper['input']->get('options', [], 'array');
        $partsQuantity      = $this->hyper['input']->get('part_quantity', [], 'array');
        $savedConfiguration = $this->hyper['input']->get('saved_configuration', 0, 'int');

        $configuration = $this->_configurationHelper->findById($savedConfiguration);
        if (!$configuration->id) {
            $output->set('msg', Text::sprintf('COM_HYPERPC_CONFIGURATOR_CONFIGURATION_NOT_EXIST', $savedConfiguration));
            $this->hyper['cms']->close($output->write());
        }

        if ($configuration->isReadonly() ||
            ($configuration->created_user_id && $configuration->created_user_id !== $this->hyper['user']->id)
        ) {
            $this->create();
        }

        $partIds      = [];
        $optionsSaved = [];

        foreach ($parts as $part) {
            if (!is_array($part)) {
                $partIds[] = $part;
            } else {
                foreach ($part as $pId) {
                    $partIds[] = $pId;
                }
            }
        }

        if (count($options)) {
            $partHelper = $this->hyper['helper']['moyskladPart'];

            foreach ($options as $partId => $optionId) {
                /** @var PartMarker $part */
                $part = $partHelper->findById($partId);
                if (!empty($part->id)) {
                    $partOptions = $part->getOptions();
                    if (array_key_exists($optionId, $partOptions)) {
                        $data = new Data($partOptions[$optionId]);
                        $optionsSaved[$part->id] = $data->getArrayCopy();
                    }
                }
            }
        }

        $configurationId = $this->_configurationHelper->update(
            $savedConfiguration,
            $partIds,
            $optionsSaved,
            $partsQuantity
        );

        /** @var ElementConfigurationActionsAmoCrm $element */
        $elementAmoCrm = Manager::getInstance()->getElement('configuration_actions', 'amo_crm');
        if ($elementAmoCrm instanceof ElementConfigurationActionsAmoCrm) {
            if ($elementAmoCrm->canDo()) {
                $this->hyper['input']->set('id', $configurationId);
                $elementAmoCrm->actionUpdateLead();
            }
        }

        if ($configurationId) {
            if ($configuration->isInCart()) {
                /** @var CartHelper */
                $cartHelper = $this->hyper['helper']['cart'];
                $cartHelper->updateConfiguration($configurationId);
                $cartItems = $cartHelper->getItemsShortList();

                $output->set('cartItems', [
                    'items' => $cartItems,
                    'count' => count($cartItems)
                ]);
            }

            $output
                ->set('result', 'success')
                ->set('msg', Text::sprintf('COM_HYPERPC_CONFIGURATOR_SUCCESS_UPDATE_CONFIGURATION', $configurationId))
                ->set('savedConfiguration', $configurationId);
        } else {
            $output
                ->set('msg', Text::sprintf('COM_HYPERPC_CONFIGURATOR_FAILED_UPDATE_CONFIGURATION', $configuration->id));
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Create configuration action.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function create()
    {
        $productId = $this->hyper['input']->get('productId', 0);
        $context = $this->hyper['input']->get('context', SaveConfiguration::CONTEXT_MOYSKLAD);

        $product = $this->_getProduct($productId, $context);
        if (empty($product->id)) {
            $this->hyper['cms']->close((new JSON([
                'savedConfiguration' => 0,
                'result'             => 'error',
                'msg'                => Text::sprintf('COM_HYPERPC_CONFIGURATOR_PRODUCT_NOT_EXIST', $productId)
            ]))->write());
        }

        $partIds       = $this->hyper['input']->get('parts', [], 'array');
        $optionIds     = $this->hyper['input']->get('options', [], 'array');
        $partsQuantity = $this->hyper['input']->get('part_quantity', [], 'array');
        $result        = $this->hyper['helper']['configuration']->save($product, $partIds, $optionIds, $partsQuantity);

        $output = new JSON([
            'result' => 'error',
            'msg'    => Text::_('COM_HYPERPC_CONFIGURATOR_SAVE_ERROR')
        ]);

        if ($result) {
            $product->set('saved_configuration', $result);

            /** @var User $user */
            $user = $this->hyper['helper']['user']->findById($this->hyper['user']->id, ['load_fields' => true]);

            if ($user->id) {
                PluginHelper::importPlugin('content');

                $dispatcher = Factory::getApplication()->getDispatcher();
                $event      = new Event('onConfiguratorAfterAuthSave', [
                    'context'       => Inflector::underscore(__FUNCTION__),
                    'configuration' => $product->getConfiguration(),
                    'data'          => [
                        'product'  => $product,
                        'email'    => $user->email,
                        'username' => $user->username,
                        'phone'    => $user->getField('phone')->get('value')
                    ],
                    'output' => $output
                ]);
                $dispatcher->dispatch('onConfiguratorAfterAuthSave', $event);
            }

            $output
                ->set('result', 'success')
                ->set('savedConfiguration', $product->saved_configuration)
                ->set('msg', Text::sprintf(
                    'COM_HYPERPC_CONFIGURATOR_SUCCESS_SAVE_CONFIGURATION',
                    $product->saved_configuration
                ));
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Send configuration by email.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function sendByEmail()
    {
        $form = new JSON($this->hyper['input']->get('jform', [], 'array'));

        $output = $this->_validSaveForm($form);

        if (!$output->get('result')) {
            $output->set('message', $this->hyper['helper']['html']->arrayToList((array) $output->get('message')));
            $this->hyper['cms']->close($output->write());
        }

        $productId = $form->get('product_id', 0);
        $context   = $form->get('context', SaveConfiguration::CONTEXT_MOYSKLAD);

        $product = $this->_getProduct($productId, $context);
        if (!$product->id) {
            $this->hyper['cms']->close(json_encode([
                'result'  => false,
                'message' => Text::_('COM_HYPERPC_ERROR_PRODUCT_NOT_FOUND')
            ]));
        }

        $partIds       = $this->hyper['input']->get('parts', [], 'array');
        $optionIds     = $this->hyper['input']->get('options', [], 'array');
        $partsQuantity = $this->hyper['input']->get('part_quantity', [], 'array');
        $savedConfigId = $this->_configurationHelper->save($product, $partIds, $optionIds, $partsQuantity);

        if (!$savedConfigId) {
            $this->hyper['cms']->close(json_encode([
                'result'  => false,
                'message' => Text::_('COM_HYPERPC_CONFIGURATOR_SAVE_ERROR')
            ]));
        }

        $configuration = $this->_configurationHelper->findById($savedConfigId);
        $configuration->setReadonly();
        $this->_configurationHelper->getTable()->save($configuration);

        $product->set('saved_configuration', $savedConfigId);

        $price = $this->hyper['input']->get('totalPrice', 0, 'int');
        $product->setListPrice($this->hyper['helper']['money']->get($price));

        $newDefParts = [];
        $newQuantity = Hash::merge($partsQuantity, $product->configuration->get('quantity'));

        foreach ($partIds as $groupId => $_partData) {
            $isMultiply = (bool) $product->configuration->find('multiply.' . $groupId);
            if (is_string($_partData)) {
                if (!array_key_exists($_partData, $optionIds)) {
                    $newDefParts[] = $_partData;
                }
            } elseif (is_array($_partData) && $isMultiply) {
                foreach ($_partData as $_mPartId) {
                    $newDefParts[] = $_mPartId;
                }
            }
        }

        asort($newDefParts);

        $product->configuration
            ->set('option', $optionIds)
            ->set('default', $newDefParts)
            ->set('quantity', $newQuantity);

        $form['product'] = $product;

        PluginHelper::importPlugin('content');

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new Event('onConfiguratorAfterNativeSave', [
            'context'       => Inflector::underscore(__FUNCTION__),
            'configuration' => $product->getConfiguration(),
            'data'          => $form,
            'output'        => $output
        ]);
        $dispatcher->dispatch('onConfiguratorAfterNativeSave', $event);

        $output->set('message', Text::_('COM_HYPERPC_CONFIGURATION_SUCCESS_SEND_EMAIL'));

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Get product
     *
     * @param   int $productId
     * @param   string $context
     *
     * @return  ProductMarker
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _getProduct($productId, $context)
    {
        /** @var ProductMarker $product */
        return $this->hyper['helper']['moyskladProduct']->findById($productId);
    }

    /**
     * Check validation save form.
     *
     * @param   Data $data
     *
     * @return  JSON
     *
     * @since   2.0
     */
    protected function _validSaveForm(Data $data)
    {
        $output = new JSON([
            'result'  => false,
            'message' => []
        ]);

        $messages = [];

        $email = $data->get('email', '');
        if (!empty($email)) {
            $blacklist = explode(' ', preg_replace('/\s+/', ' ', $this->hyper['params']->get('conf_save_email_blacklist', '', 'trim')));

            if (in_array($email, $blacklist)) {
                /** @var \HyperPcTableBanned_Ids $table */
                $table = Table::getInstance('Banned_Ids');

                $table->save([
                    'ip' => IpHelper::getIp(),
                    'banned_down' => Factory::getDate('now + ' . 1 . 'year')->toSql()
                ]);

                $messages[] = Text::_('COM_HYPERPC_CONFIGURATOR_FAILED_SAVE_CONFIGURATION');
                $output->set('message', $messages);

                return $output;
            }
        }

        Form::addFormPath(JPATH_COMPONENT . '/models/forms');

        $form = Form::getInstance(
            HP_OPTION . '.configurator_email_form',
            'configurator_email_form',
            ['control' => 'jform']
        );

        if ($this->hyper['user']->id) {
            $form->removeField('captcha');
        }

        if (!$form->validate($data->getArrayCopy())) {
            /** @var Exception $error */
            foreach ($form->getErrors() as $error) {
                $message = $error->getMessage();
                if ($message === 'timeout-or-duplicate') {
                    $message = Text::_('COM_HYPERPC_ERROR_RECAPTCHA_TIMEOUT');
                }

                $messages[] = $message;
            }
        } else {
            $output->set('result', true);
        }

        $output->set('message', $messages);

        return $output;
    }
}
