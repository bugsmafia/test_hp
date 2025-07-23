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
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

'use strict';

const
    gulp     = require('gulp'),
    config   = require('../config'),
    source   = config.path.bower + '/fancybox/dist/';

function copyJs(cb) {
    gulp
        .src(source + 'jquery.fancybox.min.js')
        .pipe(gulp.dest(config.path.vendor_js));

    cb();
}

function copyCss(cb) {
    gulp
        .src(source + 'jquery.fancybox.min.css')
        .pipe(gulp.dest(config.path.vendor_css));

    cb();
}

gulp.task('update:jquery-fancybox', gulp.parallel(
    copyJs,
    copyCss
));
