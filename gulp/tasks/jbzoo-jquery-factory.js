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
    gulp    = require('gulp'),
    config  = require('../config'),
    uglify  = require('gulp-uglify'),
    concate = require('gulp-concat'),
    source  = config.path.bower + '/jbzoo-jquery-factory/src',
    hyperpc = config.path.bower + '/hyperpc-js-widget/src',
    js      = [
        config.path.bower + '/jbzoo-utils/src/helper.js',
        source + '/widget.js',
        hyperpc + '/hyperpc.js'
    ];

gulp.task('update:jbzoo-jquery-factory', function () {
    return gulp
        .src(js)
        .pipe(concate('jquery-factory.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(config.path.vendor_js));
});
