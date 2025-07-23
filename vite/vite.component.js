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
import { viteStaticCopy } from '../node_modules/vite-plugin-static-copy'

export default defineConfig({
    plugins: [
        viteStaticCopy({
            targets: [
                {
                    src: './bower_components/jquery-mask-plugin/dist/jquery.mask.min.js',
                    rename: 'jquery-mask.min.js',
                    dest: './vendor'
                },
                {
                    src: './node_modules/vue/dist/vue.runtime.global.prod.js',
                    rename: 'vue.prod.js',
                    dest: './vendor'
                }
            ]
        })
    ],
    build: {
        outDir: './media/com_hyperpc/js',
        emptyOutDir: false
    }
});
