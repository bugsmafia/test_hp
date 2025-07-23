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
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

'use strict';

const
    name        = 'raty',
    gulp        = require('gulp'),
    config      = require('../config'),
    concat      = require('gulp-concat'),
    rename      = require('gulp-rename'),
    uglify      = require('gulp-uglify'),
    replace     = require('gulp-replace'),
    uglifyCss   = require('gulp-uglifycss'),
    source      = config.path.bower + '/raty/',
    mediaImg    = config.path.media + 'img/'   + name,
    mediaFonts  = config.path.media + 'fonts/' + name;

function processJs(cb) {
    gulp
        .src(source + 'lib/jquery.raty.js')
        .pipe(uglify())
        .pipe(rename({
            suffix      : '.min',
            basename    : 'jquery-raty'
        }))
        .pipe(gulp.dest(config.path.js + '/libs'));

    cb();
}

function processCss(cb) {
    const cssSource = '../../fonts/' + name + '/';

    gulp.src(source + 'lib/jquery.raty.css')
        .pipe(concat('raty.css'))
        .pipe(uglifyCss())
        .pipe(replace(/\.\/fonts\//g, cssSource))
        .pipe(gulp.dest(config.path.media + '/css/libs/'));

    cb();
}

function transportFonts(cb) {
    gulp.src(source + 'lib/fonts/*.*')
        .pipe(gulp.dest(mediaFonts));

    cb();
}

function transportImages(cb) {
    gulp.src(source + 'lib/images/*.*')
        .pipe(gulp.dest(mediaImg));

    cb();
}

gulp.task('update:jquery-raty', gulp.parallel(
    processJs,
    processCss,
    transportFonts,
    transportImages
));