#!/usr/bin/env sh

#
# HYPERPC - The shop of powerful computers.
#
# This file is part of the HYPERPC package.
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
# @package    HYPERPC
# @license    Proprietary
# @copyright  Proprietary https://hyperpc.ru/license
# @link       https://github.com/HYPER-PC/HYPERPC".
# @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
#

FILE=$(pwd)/scripts/vendor/ini-formatter/index.php
GITDIR="scripts/vendor"

if [ ! -f $FILE ]
    then
        echo -e "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Clone JBZoo INI - Formatter \033[0m"
        git clone --depth=50 --branch=master https://github.com/JBZoo/iniFormatter.git $GITDIR/ini-formatter

    else
        echo -e "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m JBZoo INI - Formatter all ready exists \033[0m"
    fi