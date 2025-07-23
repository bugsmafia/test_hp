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
 * @author     Roman Evsyukov
 */

'use strict';

const
    gulp     = require('gulp'),
    config   = require('../config'),
    rename   = require('gulp-rename'),
    uglify   = require('gulp-uglify'),
    source   = config.path.bower + '/jquery-ui/dist/jquery-ui.js';

gulp.task('update:jquery-ui', function () {
    return gulp
        .src(source)
        .pipe(uglify())
        .pipe(rename({
            basename : 'jquery.ui',
            suffix   : '.min'
        }))
        .pipe(gulp.dest(config.path.vendor_js));
});
