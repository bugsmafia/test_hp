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

use HYPERPC\Data\JSON;
use HYPERPC\Elements\Manager;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Language\Text;
use HYPERPC\Elements\ElementConfiguratorActions;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

defined('_JEXEC') or die('Restricted access');

/**
 * Class ElementConfigurationActionsData
 *
 * @since   2.0
 */
class ElementConfigurationActionsData extends ElementConfiguratorActions
{

    const FORM_FIELDSET = 'data';

    /**
     * Render form action.
     *
     * @return  string|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function actionRenderForm()
    {
        $form = $this->getRenderForm();
        $form->bind($this->_getRenderFormData());

        return $this->render([
            'layout' => 'form',
            'form'   => $form
        ]);
    }

    /**
     * Get render form.
     *
     * @return  Form|\Joomla\CMS\Form\Form
     *
     * @since   2.0
     */
    public function getRenderForm()
    {
        Form::addFormPath($this->getPath('forms'));
        return Form::getInstance($this->getIdentifier() . '.form', 'form', [
            'control' => JOOMLA_FORM_CONTROL
        ]);
    }

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this
            ->registerAction('saveForm')
            ->registerAction('renderForm');
    }

    /**
     * Render action button in profile account.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function renderActionButton()
    {
        $actionUrl = $this->hyper['route']->build([
            'tmpl'       => 'component',
            'action'     => 'renderForm',
            'task'       => 'elements.call',
            'group'      => $this->getGroup(),
            'identifier' => $this->getIdentifier(),
            'id'         => $this->getConfiguration()->id
        ]);

        return implode(null, [
            '<a href="' . $actionUrl . '" class="jsLoadIframe jsConfigDataForm">',
                $this->getAccountActionTile(),
            '</a>'
        ]);
    }

    /**
     * Load assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadAssets()
    {
        $this->hyper['helper']['assets']
            ->js('elements:' . $this->_group . '/' . $this->_type . '/assets/js/widget.js')
            ->widget('body', 'HyperPC.SiteConfigurationActionsData');
    }

    /**
     * Save form data.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since    2.0
     */
    public function saveForm()
    {
        $form = $this->getRenderForm();
        $data = new JSON((array) $this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));

        $arrayData = $data->getArrayCopy();
        $form->bind($arrayData);
        $filterData = $form->filter($arrayData);

        /** @var HyperPcTableSaved_Configurations $table */
        $table  = $this->helper->getTable();
        /** @var SaveConfiguration $config */
        $config = $this->getConfiguration();

        if (is_array($filterData) && $form->validate($filterData) && $config->id) {
            foreach ($filterData as $pKey => $pValue) {
                $config->params->set($pKey, $pValue);
            }

            if ($table->save($config->getArray())) {
                /** @var ElementConfigurationActionsAmoCrm $amoCrmElement */
                $amoCrmElement = $this->getManager()->getElement(
                    Manager::ELEMENT_TYPE_CONFIGURATION_ACTIONS,
                    'amo_crm'
                );

                $message = 'HYPER_ELEMENT_CONFIGURATION_ACTIONS_DATA_SUCCESS_SAVE';
                if ($amoCrmElement instanceof ElementConfigurationActionsAmoCrm) {
                    $amoCrmElement->setConfig([
                        'configuration' => $config
                    ]);

                    //  Update amo lead data.
                    if ($config->getAmoLeadId() > 0) {
                        $amoCrmElement->actionUpdateLead();
                    } else {
                        $return = $amoCrmElement->actionSaveLead();
                        if (is_string($return)) {
                            $message = $amoCrmElement->getMessage();
                        }
                    }
                }

                $this->hyper['cms']->enqueueMessage(Text::_($message));
            } else {
                $this->hyper['cms']->enqueueMessage(
                    Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_DATA_FAIL_SAVE')
                );
            }
        } else {
            $message = [];
            /** @var RuntimeException $error */
            foreach ($form->getErrors() as $error) {
                $message[] = $error->getMessage();
            }

            $this->hyper['cms']->enqueueMessage(implode(PHP_EOL, $message));
        }

        $fromElement = $this->hyper['input']->get('from');
        $this->hyper['doc']->addScriptDeclaration("
            let formElement = '" . $fromElement . "';
            let parent      = $(window.parent.document).find('.uk-lightbox.uk-open [uk-close]');
                        
            if (parent.length) {
                setTimeout(function () {
                    if (formElement === 'cp') {
                        window.parent.processKp = '#hp-configuration-" . $config->id . " .jsBuildKpPdf';
                    }
                    parent.trigger('click');
                }, 1500);
            }
        ");
    }

    /**
     * Get render form data.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getRenderFormData()
    {
        $formData = new JSON([]);
        $config   = $this->getConfiguration();
        if ($config->id) {
            $form = $this->getRenderForm();
            foreach ((array) $form->getFieldset(self::FORM_FIELDSET) as $controlName => $field) {
                $paramKey = str_replace(JOOMLA_FORM_CONTROL . '_', '', $controlName);
                $paramVal = $config->params->get($paramKey);
                if ($paramVal) {
                    $formData->set($paramKey, $paramVal);
                }
            }
        }

        return $formData->getArrayCopy();
    }
}
