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

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailTemplate;
use HYPERPC\Object\Mail\TemplateData;
use HYPERPC\Printer\Configurator\Printer;
use HYPERPC\Elements\ElementConfigurationHook;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Object\Mail\Configuration\ConfigurationData;
use HYPERPC\Object\Mail\Configuration\Specification\ItemData;
use HYPERPC\Object\Mail\Configuration\Specification\SectionData;

/**
 * Class ElementConfiguratorHookMail
 */
class ElementConfiguratorHookMail extends ElementConfigurationHook
{
    /**
     * Hook action.
     */
    public function hook()
    {
        try {
            $product = $this->getProduct();
            if ($product->id &&
                $this->isContext('send_by_email') &&
                $this->getUserEmail()
            ) {
                $this->_getMailer()->send();
            }
        } catch (\Throwable $th) {
            $this->hyper->log(
                $th->getMessage() . ' in ' . $th->getFile() . ' on ' . $th->getLine(),
                Log::ERROR,
                $this->_group . '_' . $this->_type . '/' . date('Y/m/d') . '/errors.php'
            );
        }
    }

    /**
     * Get mailer.
     *
     * @return  MailTemplate
     *
     * @throws  \Exception
     */
    protected function _getMailer($tmpl = 'mail'): MailTemplate
    {
        $mailer = new MailTemplate('com_hyperpc.' . $tmpl, Factory::getApplication()->getLanguage()->getTag());

        $mailer->addRecipient($this->getUserEmail());

        $templateData = new TemplateData([
            'subject' => $this->_getSubject(),
            'heading' => $this->getConfiguration()->getProduct()->get('name', ''),
            'message' => Text::_('HYPER_ELEMENT_CONFIGURATOR_HOOK_MAIL_MAIL_MESSAGE'),
            'reason' => Text::sprintf(
                'HYPER_ELEMENT_CONFIGURATOR_HOOK_MAIL_MAIL_REASON',
                Factory::getApplication()->getConfig()->get('sitename', 'HYPERPC')
            )
        ]);

        $templateData->configuration[] = $this->_getConfigurationData();

        $mailer->addTemplateData($templateData->toArray());

        if ($this->getConfig('pdf_attach', false, 'bool')) {
            $printer = new Printer();

            $printer->setParams([
                'template' => $this->_config->get('pdf_layout', 'default')
            ]);

            $configuration = $this->getConfiguration();
            $pdfPath       = $printer->setConfiguration($configuration)->save();

            $mailer->addAttachment($printer->getAliasName(), $pdfPath);
        }

        return $mailer;
    }

    /**
     * Get configuration data object
     *
     * @return  ConfigurationData
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     */
    protected function _getConfigurationData(): ConfigurationData
    {
        $configuration = $this->getConfiguration();

        $totalPrice = $this->hyper['helper']['money']->get(0);
        $specification = [];

        $product = $configuration->getProduct();
        $parts = $product->getConfigParts(partFormConfig: true, loadUnavailableParts: true);

        $folders = $this->hyper['helper']['productFolder']->findById(array_keys($parts), ['order' => 'a.lft']);
        $rootIds = array_map(function ($folder) {
            return $folder->getParentId();
        }, $folders);

        $roots = $this->hyper['helper']['productFolder']->findById(array_unique($rootIds), ['order' => 'a.lft']);
        foreach ($roots as $rootId => $rootFolder) {
            $children = array_filter($folders, function ($folder) use ($rootId) {
                return $folder->getParentId() === $rootId;
            });

            $itemsData = [];
            $sectionPrice = $this->hyper['helper']['money']->get(0);

            foreach ($children as $childId => $child) {
                $childParts = $parts[$childId] ?? [];

                foreach ($childParts as $part) {
                    $itemsData[] = new ItemData([
                        'category' => $child->title,
                        'itemName' => $this->_getPartName($part, $product->id)
                    ]);

                    $sectionPrice->add($part->getQuantityPrice()->val());
                }
            }

            $sectionData = new SectionData([
                'sectionTitle' => $rootFolder->title,
                'sectionPrice' => $sectionPrice->text()
            ]);

            $sectionData->items = $itemsData;

            $specification[] = $sectionData;

            $totalPrice->add($sectionPrice->val());
        }

        $configurationData = new ConfigurationData([
            'number' => (string) $configuration->id,
            'link' => Uri::root() . ltrim($configuration->getViewUrl(), '/'),
            'date' => $configuration->getLastModifiedDate()->format(Text::_('DATE_FORMAT_LC5')),
            'total' => $totalPrice->text()
        ]);

        $configurationData->specification = $specification;

        return $configurationData;
    }

    /**
     * Get part name
     *
     * @param  PartMarker|MoyskladService $part
     * @param  int $productId
     *
     * @return string
     */
    protected function _getPartName($part, int $productId): string
    {
        $partName = $part->getConfiguratorName($productId);

        if ((int) $part->get('quantity', 1) > 1) {
            $partName = $part->quantity . ' x ' . $partName;
        }

        if ($part instanceof PartMarker && !$part->isReloadContentForProduct($productId)) {
            if ($part->option instanceof OptionMarker && !empty($part->option->id)) {
                $partName .= ' ' . Text::sprintf('COM_HYPERPC_PRODUCT_OPTION', $part->option->name);
            }
        }

        return $partName;
    }
}
