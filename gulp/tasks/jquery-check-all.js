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
    gulp   = require('gulp'),
    config = require('../config'),
    source = config.path.bower + '/jquery-check-all/jquery-check-all.min.js';

gulp.task('update:jquery-check-all', function () {
    return gulp
        .src(source)
        .pipe(gulp.dest(config.path.vendor_js));
});
