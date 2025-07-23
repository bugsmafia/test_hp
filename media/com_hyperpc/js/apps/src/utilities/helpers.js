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

/**
 * Register window event.
 *
 * @param eventKey
 * @param callback
 */
export function registerLocalStorageEvent(eventKey, callback) {
    window.addEventListener('storage', (e) => {
        if (e.key === eventKey) {
            callback(e)
        }
    });
}

/**
 * Register document event.
 *
 * @param event
 * @param callback
 */
export function registerDocumentEvent(event, callback) {
    document.addEventListener(event, (e) => {
        const data = e.detail || {};
        callback(e, data);
    });
}

/**
 * Trigger event
 *
 * @param eventKey
 * @param element
 */
export function triggerEvent(eventKey, element) {
    const event = new Event(eventKey, {
        bubbles: true,
        cancelable: true
    });

    element.dispatchEvent(event);
}