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
    rename   = require('gulp-rename'),
    uglify   = require('gulp-uglify'),
    source   = config.path.bower + '/jquery-ui/ui/widgets/droppable.js';

gulp.task('update:jquery-ui-droppable', function () {
    return gulp
        .src(source)
        .pipe(uglify())
        .pipe(rename({
            basename : 'droppable',
            suffix   : '.min'
        }))
        .pipe(gulp.dest(config.path.js + '/libs/ui'));
});
