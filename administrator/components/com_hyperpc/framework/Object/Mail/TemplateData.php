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

namespace HYPERPC\Object\Mail;

use Joomla\CMS\Date\Date;
use HYPERPC\Object\Mail\Order\OrderData;
use Spatie\DataTransferObject\DataTransferObject;
use HYPERPC\Object\Mail\Configuration\ConfigurationData;

class TemplateData extends DataTransferObject
{
    /**
     * Mail subject
     */
    public string $subject;

    /**
     * Mail heading
     */
    public string $heading;

    /**
     * Message
     */
    public string $message;

    /**
     * Order object
     *
     * @var OrderData[]
     */
    public array $order = [];

    /**
     * Configuration object
     *
     * @var ConfigurationData[]
     */
    public array $configuration = [];

    /**
     * Current year
     */
    public string $year;

    /**
     * Mailing reason
     */
    public string $reason = '';

    /**
     * Class constructor
     */
    public function __construct(array $parameters = [])
    {
        $parameters['year'] = $parameters['year'] ?? (Date::getInstance())->year;

        parent::__construct($parameters);
    }
}
