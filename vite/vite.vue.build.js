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
 *
 * @author     Artem Vyshnevskiy
 */

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';
import createExternal from 'vite-plugin-external';

export default defineConfig({
    plugins: [
        vue(),
        createExternal({
            externals: {
                vue: 'Vue'
            }
        })
    ],
    build: {
        emptyOutDir: false,
        rollupOptions: {
            input: {
                hp_navbar_user: resolve(__dirname, '../media/com_hyperpc/js/apps/src/modules/hp_navbar_user/index.js')
            },
            output: {
                format: 'iife',
                dir: resolve(__dirname, '../media/com_hyperpc'),
                entryFileNames: ({ name }) => {
                    if (['hp_navbar_user'].includes(name)) {
                        return 'js/apps/dist/modules/[name].js';
                    }

                    return 'js/apps/dist/[name].js';
                },
                chunkFileNames: 'js/[name].js',
                assetFileNames: ({ name, ext }) => {
                    if (ext === '.css') {
                        return 'css/apps/[name][extname]';
                    } else if (['.png', '.jpg', '.jpeg', '.gif', '.svg'].includes(ext)) {
                        return 'images/apps/[name][extname]';
                    }

                    return 'js/apps/[name][extname]';
                }
            }
        }
    }
});
