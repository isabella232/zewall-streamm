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
#
# Convert script
# --------------
# Created on Wed, 23 May 2012 19:30:02 +0200
# by Arnaud Morin <arnaud1.morin@orange.com>
#
#
# Revision notes:
# ---------------
# R.1: Initial release
# R.2: Now use VPXENC
# R.3: Remove usage of VPXENC again. Only FFMPEG with option setpts
# R.4: Now with options for both Video and Audio as we separate streams before making a new webm file
#      This hack is needed to have both Video and Audio synchronized. TODO: try to avoid this hack
# R.5: Add check arguments before converting
# R.6: Add keep arg to check if we need to keep all webm files or only last x
# R.7: Add onlyvideo arg
# R.8: Move to getopt argument management
# R.9: Make the script add automatically onlyvideo to yes if only one stream in input file
#      I don't really know if it's a good choice but it's very usefull for now.
# R.10: Add Log file option and mecanism
#       Add options to optimize ffmpeg -threads 2 -quality realtime -cpu-used 5
#
#
#
# Description:
# ------------
# TODO
#
# To be done:
# -----------
# Write Description
#
# Usage:
# ------
# see: convert.sh --help
#

########################################################################
# Variables
########################################################################
readonly STARTTIME=$(date +%s%N)
readonly PRGNAME=$(basename $0)
readonly FFMPEG="/usr/local/bin/ffmpeg"
readonly FFMPEG_LOG_LEVEL="-loglevel warning"
readonly FFMPEG_VIDEO_OPT="-s 320x240 -filter setpts=PTS-STARTPTS -y -an -vcodec libvpx -threads 2 -quality realtime -cpu-used 5 -f webm"
readonly FFMPEG_AUDIO_OPT="-y -vn -f ogg -acodec libvorbis -ar 16000 -aq 1 -ac 1"
readonly FFMPEG_REMIX_OPT="-y -acodec copy -vcodec copy"
# No trailing slash needed
readonly TMPFOLDER="/tmp"
readonly WEBFOLDER="/var/www"

# Default config - might be overwritten by command line arguments
LOGFILE="/var/log/convert.log"
ONLYVIDEO="no"
KEEP="3"

########################################################################
# Help Function
########################################################################
function help () {

  cat << EOF >&1
Usage: $PRGNAME [options] <trace file>

Options:
  --help
        Print this help and exit.
  
  --onlyvideo
        Convert & keep only video for this stream instead of having both
        audio and video converted into webm (VP8+vorbis) format.
  
  --keep <n|all>
        Keep last n or all chunks after converting this stream to webm.
        Useful to remove video chunks as soon as received and to avoid
        having disk space full. It will keep a playlist file with only
        n last chunks. Default is $KEEP.
        
  --outputfilename <filename>
        This is the desired file name this stream will be converted to.
        The file name must be given without extension. This script will
        add .webm to the file name automatically.
        This is a MANDATORY arg.
        
  --outputfolder <foldername>
        The file will be created in this folder. This is a relative
        folder that will be created in $WEBFOLDER. If you want to update
        $WEBFOLDER, you'll need to edit this script.
        This is a MANDATORY arg.
        
  --inputfile <filename>
        This is the file that contained the stream we must convert.
        When conversion is done, input file is removed.
        This is a MANDATORY arg.
  
  --logfile <filename>
        This is the file that contained the log of the conversion.
        By default we will use $LOGFILE

EOF
}

########################################################################
# Command line argument parsing
########################################################################
LOPT="inputfile:,outputfolder:,outputfilename:,keep:,logfile:,onlyvideo,help"

# Note that we use `"$@"' to let each command-line parameter expand to a
# separate word. The quotes around `$@' are essential!
# We need TEMP as the `eval set --' would nuke the return value of getopt.
TEMP=$(getopt --options=dfhorv --long $LOPT -n $PRGNAME -- "$@")

if [[ $? -ne 0 ]]; then
  echo "Error in Arguments parsing. Terminating..."
  exit 2
fi

# Note the quotes around `$TEMP': they are essential!
eval set -- "$TEMP"

while true; do
  case $1 in
  --help)                help; exit ;;

  --onlyvideo)
                         ONLYVIDEO="yes"
                         ;;
  --keep)
                         KEEP=$2; shift
                         ;;
  --outputfilename)
                         OUTPUTFILE=$2.webm; shift
                         ;;
  --outputfolder)
                         OUTPUTFOLDER="$WEBFOLDER/$2"; shift
                         ;;
  --inputfile)
                         INPUTFILE=$2; shift
                         ;;
  --logfile)
                         LOGFILE=$2; shift
                         ;;
  --)                    shift; break;;
   *)                    echo "unknow argument \"$1\""; exit 2;;
  esac
  shift
done


########################################################################
# Sanity checks
########################################################################
# Check folders
if [ -z "$OUTPUTFOLDER" ]; then
    echo "Error, --outputfolder must not be empty!"
    echo "Try '$PRGNAME --help' for more information on how to use this script"
    exit 2
fi
if [ ! -d "$OUTPUTFOLDER" ]; then
    mkdir "$OUTPUTFOLDER"
fi

# Check input file
if [ -z "$INPUTFILE" ]; then
    echo "Error, --inputfile must not be empty!"
    echo "Try '$PRGNAME --help' for more information on how to use this script"
    exit 2
fi
if [ ! -e "$INPUTFILE" ]; then
    echo "Error, $INPUTFILE does not exist!"
    echo "Try '$PRGNAME --help' for more information on how to use this script"
    exit 2
fi

# Check if input file has two streams at least
TEMP=$($FFMPEG -i $INPUTFILE 2>&1 | grep -c Stream)
if [ $TEMP -lt 2 ]; then
    ONLYVIDEO="yes"
fi

# Check output file
if [ -z "$OUTPUTFILE" ]; then
    echo "Error, --outputfilename must not be empty!"
    echo "Try '$PRGNAME --help' for more information on how to use this script"
    exit 2
fi

########################################################################
# Conversion
########################################################################
echo "-$$-----------------------------------------------------------------" >>$LOGFILE 2>&1
echo "-$$-----------------------------------------------------------------" >>$LOGFILE 2>&1
echo "-$$-> Starting conversion of $INPUTFILE to $OUTPUTFOLDER/$OUTPUTFILE" >>$LOGFILE 2>&1
if [ $ONLYVIDEO = "yes" ]; then
    echo "-$$-> Only Video" >>$LOGFILE 2>&1
    # Convert Video
    $FFMPEG $FFMPEG_LOG_LEVEL -i $INPUTFILE $FFMPEG_VIDEO_OPT $TMPFOLDER/$$.webm >>$LOGFILE 2>&1
    cp $TMPFOLDER/$$.webm $OUTPUTFOLDER/$OUTPUTFILE >>$LOGFILE 2>&1
else
    # Convert & separate Video and Audio (this is necessary to get sync between a & v)
    $FFMPEG $FFMPEG_LOG_LEVEL -i $INPUTFILE $FFMPEG_VIDEO_OPT $TMPFOLDER/$$.webm $FFMPEG_AUDIO_OPT $TMPFOLDER/$$.ogg >>$LOGFILE 2>&1
    # Mix to have both audio and video in the same file
    $FFMPEG $FFMPEG_LOG_LEVEL -i $TMPFOLDER/$$.webm -i $TMPFOLDER/$$.ogg $FFMPEG_REMIX_OPT $OUTPUTFOLDER/$OUTPUTFILE >>$LOGFILE 2>&1
fi
echo "-$$-> Done!" >>$LOGFILE 2>&1

# Now update playlist file
echo "-$$-> Updating playlist" >>$LOGFILE 2>&1
if [ $KEEP = "all" ]; then
    echo $OUTPUTFILE >> $OUTPUTFOLDER/playlist
else
    if [ ! -e $OUTPUTFOLDER/playlist ]; then
        touch $OUTPUTFOLDER/playlist
    fi
    tail $OUTPUTFOLDER/playlist -n $KEEP > $TMPFOLDER/playlist.$$
    echo $OUTPUTFILE >> $TMPFOLDER/playlist.$$
    mv $TMPFOLDER/playlist.$$ $OUTPUTFOLDER/playlist >>$LOGFILE 2>&1
    # We need to remove all files except those in playlist file
    for FULLFILE in $(ls $OUTPUTFOLDER/*.webm); do
        FILE=$(basename $FULLFILE)
        RESULT=$(cat $OUTPUTFOLDER/playlist | grep -c "$FILE")
        if [ ! $RESULT -ge 1 ]; then
            #echo "removing $FILE"
            rm $OUTPUTFOLDER/$FILE >>$LOGFILE 2>&1
        fi
    done
fi

########################################################################
# End of conversion / garbage machine
########################################################################
# Remove temp file
echo "-$$-> Purging temp files" >>$LOGFILE 2>&1
rm $INPUTFILE
rm $TMPFOLDER/$$.webm >>$LOGFILE 2>&1
if [ ! $ONLYVIDEO = "yes" ]; then
    rm $TMPFOLDER/$$.ogg >>$LOGFILE 2>&1
fi

# Compute time spent to perform this conversion
readonly ENDTIME=$(date +%s%N)

TOTALTIME=$(echo "scale=2;($ENDTIME - $STARTTIME) / 1000000000" | bc)

echo "-$$-> End of script. Executed in $TOTALTIME secs." >>$LOGFILE 2>&1
echo "-$$-----------------------------------------------------------------" >>$LOGFILE 2>&1
echo "-$$-----------------------------------------------------------------" >>$LOGFILE 2>&1
