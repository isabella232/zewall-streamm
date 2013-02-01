#!/bin/bash
# Copyright Arnaud Morin <arnaud1.morin@orange.com>
# 
# This file is part of Zewall by Orange.
# 
# This script is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
# 
# This script is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this script.  If not, see <http://www.gnu.org/licenses/>.

BIN_FOLDER="/usr/local/bin"
STREAMM_FOLDER="/usr/local/stream-m"
CONF_FOLDER="/etc/stream-m"
WEB_FOLDER="/var/www"


echo "-----------------------------------------------------"
echo "Installing stream-m"


if [ ! -d "$BIN_FOLDER" ]; then
    mkdir -p $BIN_FOLDER
fi

if [ ! -d "$STREAMM_FOLDER" ]; then
    mkdir -p $STREAMM_FOLDER
fi

if [ ! -d "$CONF_FOLDER" ]; then
    mkdir -p $CONF_FOLDER
fi

if [ ! -d "$WEB_FOLDER" ]; then
    mkdir -p $WEB_FOLDER
fi



cp -ar * $STREAMM_FOLDER/
chmod +x $STREAMM_FOLDER/bin/convert.sh

cp -ar $STREAMM_FOLDER/webclient/* $WEB_FOLDER


if [ -e $BIN_FOLDER/convert.sh ]; then
    rm -f $BIN_FOLDER/convert.sh
fi

ln -s $STREAMM_FOLDER/bin/convert.sh $BIN_FOLDER/convert.sh


if [ ! -e "$CONF_FOLDER/server.conf" ]; then
    cp $STREAMM_FOLDER/server.conf.sample $CONF_FOLDER/server.conf
fi

echo "Done!"
echo ""
echo "Please, edit $CONF_FOLDER/server.conf before running stream-m"
echo "To run stream-m, you can use: "
echo ""
echo "java -cp $STREAMM_FOLDER/lib/stream-m.jar StreamingServer $CONF_FOLDER/server.conf"
echo ""
echo "-----------------------------------------------------"
