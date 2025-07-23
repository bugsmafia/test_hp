/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    HYPERPC
 * @license    Proprietary
 * @copyright  Proprietary https://hyperpc.ru/license
 * @link       https://github.com/HYPER-PC/HYPERPC".
 * @author     Sergey Voronin
 */

import { helpers } from "@vuelidate/validators";

export const geoData = {
    RU: {
        mask: '+7 (000) 000-00-00',
        format: '+7 (9xx) xxx-xx-xx',
        default: '+7 (',
        ruleName: 'mobile_ru',
        regex: /^\+7\s?(\(?9\d{2}\)?\s?\d{3}-?\d{2}-?\d{2}|\(?123\)?\s?000-?00-?00)/
    },
    BY: {
        mask: '+375 00 000 00 00',
        format: '+375 xx xxx xx xx',
        default: '+375 ',
        ruleName: 'mobile_by',
        regex: /^\+375\s?\d{2}\s?\d{3}\s?\d{2}\s?\d{2}/
    },
    KZ: {
        mask: '+7 (000) 000-00-00',
        format: '+7 (7xx) xxx-xx-xx',
        default: '+7 (',
        ruleName: 'mobile_kz',
        regex: /^\+7\s?(\(?7\d{2}\)?\s?\d{3}-?\d{2}-?\d{2}|\(?123\)?\s?000-?00-?00)/
    },
    AE: {
        mask: '+971 00 000 0000',
        format: '+971 5x xxx xxxx',
        default: '+971 ',
        ruleName: 'mobile_ae',
        regex: /^\+971\s?5\d{1}\s?\d{3}\s?\d{4}/
    },
    BH: {
        mask: '+973 000 000 0000',
        format: '+973 xxx xxx xxxx',
        default: '+973 ',
        ruleName: 'mobile_bh',
        regex: /^\+973\s?\d{3}\s?\d{3}\s?\d{4}/
    },
    KW: {
        mask: '+965 0000 0000',
        format: '+965 xxxx xxxx',
        default: '+965 ',
        ruleName: 'mobile_kw',
        regex: /^\+965\s?\d{4}\s?\d{4}/
    },
    OM: {
        mask: '+968 0000 0000',
        format: '+968 xxxx xxxx',
        default: '+968 ',
        ruleName: 'mobile_om',
        regex: /^\+968\s?\d{4}\s?\d{4}/
    },
    QA: {
        mask: '+974 00 000 0000',
        format: '+974 xxx xxx xx',
        default: '+974 ',
        ruleName: 'mobile_qa',
        regex: /^\+974\s?\d{3}\s?\d{3}\s?\d{2}/
    },
    SA: {
        mask: '+966 000 000 0000',
        format: '+966 05x xxx xxxx',
        default: '+966 ',
        ruleName: 'mobile_sa',
        regex: /^\+966\s?05\d{1}\s?\d{3}\s?\d{4}/
    },
    default: {
        mask: '+000000000000000',
        default: '+',
        ruleName: 'mobile_default',
        regex: /^\+\d{10,18}/
    }
};

/**
 * Create custom validators based on geoData
 *
 */

export const createCustomValidators = () => {
    const validators = {}

    Object.keys(geoData).forEach((key) => {
        const {ruleName, regex, format} = geoData[key]
        validators[ruleName] = helpers.withMessage(
            Joomla.Text._(
                'TPL_HYPERPC_MOBILE_ERROR_MESSAGE',
                `Enter the number in the format ${format}`
            ).replace('%s', format),
            (value) => regex.test(value)
        )
    })

    return validators
}
