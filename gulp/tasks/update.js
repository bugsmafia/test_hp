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

var gulp = require('gulp');

gulp.task('update', gulp.parallel(

    //  jQuery libs
    'update:jquery-ui',
    'update:jquery-raty',
    'update:jquery-fancybox',
    'update:jquery-check-all',
    'update:jquery-ui-draggable',
    'update:jquery-ui-droppable',
    'update:jquery-sticky-sidebar',

    //  Others libs
    'update:open-sans-fontface',
    'update:jbzoo-jquery-factory'
));
