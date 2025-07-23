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

import { defineConfig } from 'vite'

export default defineConfig(() => ({
    build: {
        outDir: './media/',
        emptyOutDir: false,
        assetsDir: '',
        rollupOptions: {
            input: {
                'templates/site/hyperpc/js/vendor/jquery-nice-select.min.js': './bower_components/jquery-nice-select/js/jquery.nice-select.js'
            },
            output: {
                entryFileNames: '[name]'
            }
        },
    }
}))