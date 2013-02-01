<?php
/*
    Copyright Arnaud Morin <arnaud1.morin@orange.com>
   
    This file is part of Zewall by Orange.

    This script is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This script is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this script.  If not, see <http://www.gnu.org/licenses/>.

*/


$stream = "first";
if (isset($_REQUEST['stream'])) $stream = $_REQUEST['stream'];

// Open playlist file
$file = file("./$stream/playlist", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
header('Content-type: text/plain');
echo json_encode(array("folder" => "./$stream/", "playlist" => $file));
// 
?>
