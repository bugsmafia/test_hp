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
# @author     Sergey Kaslistratov <kalistratov.s.m@gmail.com>
#

echo -e "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Package Joomla \033[0m"

PACKAGES_DIR="./build/packages"
if [ ! -d "$PACKAGES_DIR" ]; then
    mkdir $PACKAGES_DIR
fi

COMPONENT_DIR="./build/packages/com_hyperpc"
if [ ! -d "$COMPONENT_DIR" ]; then
    mkdir $COMPONENT_DIR
fi

ADMIN_DIR="./build/packages/com_hyperpc/admin"
if [ ! -d "$ADMIN_DIR" ]; then
    mkdir $ADMIN_DIR
fi

SITE_DIR="./build/packages/com_hyperpc/site"
if [ ! -d "$SITE_DIR" ]; then
    mkdir $SITE_DIR
fi

CONT_PLG_DIR="./build/packages/plg_cont_hyperpc"
if [ ! -d "$CONT_PLG_DIR" ]; then
    mkdir $CONT_PLG_DIR
fi

SYS_PLG_DIR="./build/packages/plg_sys_hyperpc"
if [ ! -d "$SYS_PLG_DIR" ]; then
    mkdir $SYS_PLG_DIR
fi

cp -R ./administrator/components/com_hyperpc/* ./build/packages/com_hyperpc/admin
cp -R ./components/com_hyperpc/* ./build/packages/com_hyperpc/site

cp -R ./plugins/content/hyperpc/* ./build/packages/plg_cont_hyperpc
cp -R ./plugins/system/hyperpc/* ./build/packages/plg_sys_hyperpc

cp ./hyperpc.xml ./build/packages/com_hyperpc

zip -r ./build/packages/pkg_hyperpc.zip ./build/packages ./media ./libraries ./pkg_hyperpc.xml
