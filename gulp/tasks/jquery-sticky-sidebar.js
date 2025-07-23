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
 * @author     Artem Vyshnevskiy
 */

'use strict';

const
    {task, src, dest} = require('gulp'),
    config = require('../config'),
    concate = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    sticky = config.path.bower + '/sticky-sidebar/dist/',
    resize = config.path.bower + '/css-element-queries/src/';

task('update:jquery-sticky-sidebar', function(cb) {
    src([
        resize      + 'ResizeSensor.js',
        sticky      + 'jquery.sticky-sidebar.min.js',
    ])
    .pipe(concate('jquery-sticky-sidebar.min.js'))
    .pipe(uglify())
    .pipe(dest(config.path.vendor_js));

    cb();
});