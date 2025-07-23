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
    gulp      = require('gulp'),
    config    = require('../config'),
    concate   = require('gulp-concat'),
    uglifycss = require('gulp-uglifycss'),
    replace   = require('gulp-replace'),
    source    = config.path.bower + '/open-sans-fontface',
    dirName   = 'open-sans',
    fontsDir  = config.path.media + 'fonts/' + dirName;

function copyFonts(cb) {
    gulp.src(source + '/fonts/Bold/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/BoldItalic/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/ExtraBold/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/ExtraBoldItalic/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/Italic/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/Light/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/LightItalic/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/Regular/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/Semibold/*.*')
        .pipe(gulp.dest(fontsDir));

    gulp.src(source + '/fonts/SemiboldItalic/*.*')
        .pipe(gulp.dest(fontsDir));

    const cssSource = '../fonts/' + dirName + '/';

    gulp.src(source + '/open-sans.css')
        .pipe(concate('open-sans.min.css'))
        .pipe(replace(/\.\/fonts\/Bold\//g, cssSource))
        .pipe(replace(/\.\/fonts\/BoldItalic\//g, cssSource))
        .pipe(replace(/\.\/fonts\/ExtraBold\//g, cssSource))
        .pipe(replace(/\.\/fonts\/ExtraBoldItalic\//g, cssSource))
        .pipe(replace(/\.\/fonts\/Italic\//g, cssSource))
        .pipe(replace(/\.\/fonts\/Light\//g, cssSource))
        .pipe(replace(/\.\/fonts\/LightItalic\//g, cssSource))
        .pipe(replace(/\.\/fonts\/Regular\//g, cssSource))
        .pipe(replace(/\.\/fonts\/Semibold\//g, cssSource))
        .pipe(replace(/\.\/fonts\/SemiboldItalic\//g, cssSource))
        .pipe(uglifycss())
        .pipe(gulp.dest(config.path.media + '/css/'));

    cb();
}

gulp.task('update:open-sans-fontface', copyFonts);
