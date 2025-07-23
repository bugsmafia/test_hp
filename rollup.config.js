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

import vue from 'rollup-plugin-vue';
import terser from '@rollup/plugin-terser';
import postcss from 'rollup-plugin-postcss';
import nodeResolve from '@rollup/plugin-node-resolve';

const commonConfig = {
    output: {
        format: 'iife',
        globals: {
            vue: 'Vue'
        }
    },
    plugins: [
        vue(),
        nodeResolve(),
        postcss(),
        terser({
            output: {
                comments: false,
            }
        })
    ],
    external: 'vue',
}

const srcPath = 'media/com_hyperpc/js/apps/src';
const distPath = 'media/com_hyperpc/js/apps/dist';

export default [
    mergeDeep(commonConfig, {
        input: srcPath + '/modules/hp_navbar_user/index.js',
        output: {
            file: distPath + '/modules/navbar-user.js'
        }
    }),
    mergeDeep(commonConfig, {
        input: srcPath + '/views/profile_menu/index.js',
        output: {
            file: distPath + '/views/profile-menu.js'
        }
    }),
    mergeDeep(commonConfig, {
        input: srcPath + '/common/auth/index.js',
        output: {
            file: distPath + '/common/auth/auth-form.js'
        }
    }),
    mergeDeep(commonConfig, {
        input: srcPath + '/common/product/teaser/index.js',
        output: {
            file: distPath + '/common/product/teaser/product-teaser.js'
        }
    }),
];

function mergeDeep(target, source) {
    let output = Array.isArray(target) ? [...target] : { ...target };

    for (const key in source) {
        if (source[key] instanceof Object && key in target) {
            output[key] = mergeDeep(target[key], source[key]);
        } else {
            output[key] = source[key];
        }
    }

    return output;
}
