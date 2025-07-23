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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use HYPERPC\App;
use JBZoo\Data\JSON;
use Joomla\CMS\Plugin\CMSPlugin;

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Class PlgExtensionHyperPC
 */
class PlgExtensionHyperPC extends CMSPlugin
{

    /**
     * Hold HYPERPC Application object.
     *
     * @var App
     */
    public $hp;

    /**
     * PlgContentHyperPC constructor.
     *
     * @param object $subject
     * @param array $config
     */
    public function __construct($subject, array $config = [])
    {
        parent::__construct($subject, $config);
        $this->hp = App::getInstance();
    }

    /**
     * Joomla callback event after save component global configuration.
     *
     * @param string $context
     * @param \Joomla\CMS\Table\Table $table
     * @return bool
     */
    public function onExtensionBeforeSave($context, $table)
    {
        if ($context === 'com_config.component') {
            $params = new JSON($table->params);
            $cartParams = (array) $params->get('cart', []);

            if (count($cartParams)) {
                $newParams = [];
                foreach ($cartParams as $type => $elements) {
                    if (count($elements)) {
                        foreach ((array) $elements as $i => $data) {
                            if (array_key_exists('alias', $data)) {
                                $alias = $data['alias'];
                                unset($data['alias']);

                                if (array_key_exists('related', $data)) {
                                    $related = [];
                                    foreach ((array) $data['related'] as $j => $rData) {
                                        if (array_key_exists('alias', $rData)) {
                                            $rAlias = $rData['alias'];
                                            unset($rData['alias']);
                                            $related[$rAlias] = $rData;
                                        }
                                    }
                                    $data['related'] = $related;
                                }

                                $newParams[$data['position']][$alias] = $data;
                            }
                        }
                    }
                }

                $params->set('cart', $newParams);
                $table->params = $params->write();
            }
        }

        return true;
    }
}
