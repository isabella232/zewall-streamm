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
// Declare all streams - old way
$streams = array();
$streamsURL = array();
/*
$streams[] = "first";
$streams[] = "second";
*/

// Get all streams for WS
$json = json_decode(file_get_contents("http://admin@zewall.org:zewall@172.20.180.172:8080/zewall/services/flux/list"));


foreach($json as $streamObject){
    // Hack parce que l'API renvoi des doublons
    // Hack encore pour ne choper que les H264-VORBIS
    if (!in_array($streamObject->nom,$streams) && $streamObject->encodage == "VP8-VORBIS"){
        $streams[] = $streamObject->nom;
        $streamsURL[] = $streamObject->liveURL;
    }
}


// Current playing stream is
$streamName=$streams[0];
if (isset($_REQUEST['streamName'])){
    $streamName=$_REQUEST['streamName'];
}


// Build players
// Players are always working 2 by 2 (twins) and corresponding to a stream
$players = array();
foreach ($streams as $stream){
    $players["$stream"][] = "video1_$stream";
    $players["$stream"][] = "video2_$stream";
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Zewall Portal - Tests Arnaud</title>
    <link rel="stylesheet" media="screen" type="text/css" title="Design" href="css/zewall.css"/>
    <link rel="stylesheet" media="screen" type="text/css" title="Design" href="css/video-js.css"/>
    <!-- <link href="http://vjs.zencdn.net/c/video-js.css" rel="stylesheet"> -->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="js/video.js"></script>
    <!--
    <script src="http://vjs.zencdn.net/c/video.js"></script>
    <script src="http://www.openlayers.org/api/OpenLayers.js"></script>
    <script>
      function init() {
        map = new OpenLayers.Map("basicMap");
        var mapnik = new OpenLayers.Layer.OSM();
        map.addLayer(mapnik);
        map.setCenter(new OpenLayers.LonLat(-3.4690022563934,48.743951568603) // Center of the map
          .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            new OpenLayers.Projection("EPSG:900913") // to Spherical Mercator Projection
          ), 12 // Zoom level
        );
      }
    </script>
    -->
</head>
<body>
    <div class="main_frame">
    
    
    
        <!-- --------------------------------------------------------------- -->
        <!-- TITLE AND EVENT SELECTOR -->
        <!-- --------------------------------------------------------------- -->
        <div class="header">
            <div class="header_logo"></div>
            <div class="header_title">ZeWall</div>
            <div class="header_selector">
                <div class="selector_title">choose event</div>
                <div class="selector">
                    <select>
                        <option value="Adafil_test">Bateau en chocolat</option>
                        <option value="AGTestProject">AGTestProject</option>
                        <option value="Amasoo">Amasoo</option>
                        <option value="annoncesAMEA">annoncesAMEA</option>
                        <option value="AnnuaireCI">AnnuaireCI</option>
                        <option value="API_OrangeMoney">API_OrangeMoney</option>
                        <option value="assistance_tech">assistance_tech</option>
                        <option value="Boomer">Boomer</option>
                        <option value="Breizh_pors">Breizh_pors</option>
                        <option value="buddy_locator">buddy_locator</option>
                        <option value="burrito_project">burrito_project</option>
                        <option value="CAN_betting">CAN_betting</option>
                    </select>
                </div>
            </div>
        </div>
        
        
        
        
        
        <div class="main_content">
            <!-- --------------------------------------------------------------- -->
            <!-- VIGNETTES -->
            <!-- --------------------------------------------------------------- -->
            <div class="vignettes">
                <div class="vignettes_video">
                    <?php
                    // Foreach stream define in streams array
                    foreach ($streams as $i => $stream){
                        if ($streamName != $stream){
                        ?>
                            <div>
                                <a href="<?php echo $streamsURL[$i] ?>">
                                    <video id="video1_<?php echo $stream ?>" class="video-js vjs-default-skin video-vignette-1" preload="auto" autoplay width="176" height="132"
                                      data-setup="{}">
                                    </video>
                                    <video id="video2_<?php echo $stream ?>" class="video-js vjs-default-skin video-vignette-2" preload="auto" width="176" height="132"
                                      data-setup="{}">
                                    </video>
                                </a>
                            </div>
                        <?php
                        }
                    }
                    ?>
                </div>
            </div>
            
            
            
            
            
            <!-- --------------------------------------------------------------- -->
            <!-- VIGNETTES SCROLLER -->
            <!-- --------------------------------------------------------------- -->
            <div class="vignettes_scroller">
                <div class="point_gris"></div>
                <div class="point_gris"></div>
                <div class="point_orange"></div>
                <div class="point_gris"></div>
                <div class="point_gris"></div>
            </div>
            
            
            
            
            
            <!-- --------------------------------------------------------------- -->
            <!-- MAIN VIDEO -->
            <!-- --------------------------------------------------------------- -->
            <div class="video_container">
                <div class="video_title">
                
                </div>
                <div class="video_player">
                    <video id="<?php echo $players["$streamName"][0]; ?>" class="video-js vjs-default-skin video-1" preload="auto" autoplay width="480" height="360"
                      data-setup="{}">
                    </video>
                    <video id="<?php echo $players["$streamName"][1]; ?>" class="video-js vjs-default-skin video-2" preload="auto" width="480" height="360"
                      data-setup="{}">
                    </video>
                </div>
            </div>
            
            
            
            
            <!-- --------------------------------------------------------------- -->
            <!-- OPENSTREETMAP -->
            <!-- --------------------------------------------------------------- -->
            <!--
            <div class="map_container">
                <div class="map_tabs">
                
                </div>
                <div class="map_map" id="basicMap">
                    
                </div>
                <div class="map_comments">
                
                </div>
                <div class="map_video_info">
                
                </div>
            </div>
            -->
            
            
        </div>
        
        
        
        
        
        
        
        
        <!-- --------------------------------------------------------------- -->
        <!-- FOOTER AND LOGO -->
        <!-- --------------------------------------------------------------- -->
        <div class="footer">
            <div class="footer_text">help | about</div>
            <div class="footer_logo"></div>
        </div>
    </div>


    <!-- --------------------------------------------------------------- -->
    <!-- LOGS -->
    <!-- --------------------------------------------------------------- -->
    <div id="log"></div>
    
    
    <!-- --------------------------------------------------------------- -->
    <!-- JAVASCRIPT VIDEO SWITCHER MECANISM -->
    <!-- --------------------------------------------------------------- -->
    <script>
        // Variables
        var folders = {};
        var playlists = {};
        var initDone = {};
        
        
        
        <?php
        // Now foreach players couple, build a javascript
        foreach ($streams as $stream){
            $player1 = $players[$stream][0];
            $player2 = $players[$stream][1];
            $isVignette = "false";
            
            if ($streamName != $stream){
                echo <<<EOF
                // Shut up vignettes videos
                _V_("$player1").volume(0);
                _V_("$player2").volume(0);
EOF;
                $isVignette = "true";
            }
            
            echo <<<EOF
                // Init variables
                folders["$stream"] = [];
                playlists["$stream"] = [];
                initDone["$stream"] = 0;
            
                // Update the playlists
                updatePlaylists(_V_("$player1"), _V_("$player2"), "$stream");
        
                _V_("$player1").ready(function(){
                    // Add event on video Ended
                    _V_("$player1").addEvent("ended",function (){
                        videoPlayerSwitch(_V_("$player1"), _V_("$player2"), "$stream", $isVignette);
                    });
                    
                    // Add event on error - usually playlist has been updated just after we red it.
                    _V_("$player1").addEvent("error",function (){
                        console.log("Error occured, maybe network, switching to next video");
                        videoPlayerSwitch(_V_("$player1"), _V_("$player2"), "$stream", $isVignette);
                    });
                });
                
                _V_("$player2").ready(function(){
                    // Add event on video Ended
                    _V_("$player2").addEvent("ended",function (){
                        videoPlayerSwitch(_V_("$player2"), _V_("$player1"), "$stream", $isVignette);
                    });
                    
                    // Add event on error - usually playlist has been updated just after we red it.
                    _V_("$player2").addEvent("error",function (){
                        console.log("Error occured, maybe network, switching to next video");
                        videoPlayerSwitch(_V_("$player2"), _V_("$player1"), "$stream", $isVignette);
                    });
                });
                
        
EOF;

        }
        ?>
    
        function doinit(playerFrom, playerTo, stream){
            playerFrom.src({ type: "video/webm", src: folders[stream] + playlists[stream][0] });
            playerTo.src({ type: "video/webm", src: folders[stream] + playlists[stream][1] });
            initDone[stream] = 1;
        }
        
        

        /* 
         * Video switcher
         * 
         * 
         */
        function videoPlayerSwitch(playerFrom, playerTo, stream, isVignette){
            $("#log").prepend(playerFrom.id + " ended... <br/>");
            
            // Update the playlist
            updatePlaylists(playerFrom, playerTo, stream);
            playlists[stream].shift();
            
            // Start playing next video
            // Show playerTo
            $("#"+playerTo.id).css("visibility", "visible");
            
            // Hide playerFrom
            $("#"+playerFrom.id).css("visibility", "hidden");
            
            // Set volume
            if (!isVignette) playerTo.volume(1);
            // Play
            playerTo.play();
            
            //$("#log").prepend(" --> " + playerFrom.id + " started... <br/>");
            
            // Switch to next video on playerFrom
            playerFrom.src({ type: "video/webm", src: folders[stream] + playlists[stream][1] });
            playerFrom.volume(0);
            playerFrom.play();
            //$("#log").prepend(" --> " + playlists[stream][0] + " loading on " + playerFrom.id + "... <br/>");
            playerFrom.pause();
            playerFrom.currentTime(0);
        }
        
        
        /* 
         * Update playlist
         * This function will update the playlist object with last values
         * taken from getPlaylist web service
         * 
         */
        function updatePlaylists(playerFrom, playerTo, stream){
            $.getJSON('getPlaylist.php?stream=' + stream, function(data) {
                // For each key
                $.each(data, function(key, val) {
                    if (key == "folder") folders[stream] = val;
                    if (key == "playlist"){
                        $.each(val, function(key2, val2) {
                            if ($.inArray(val2, playlists[stream]) == -1){
                                playlists[stream].push(val2);
                                console.log(val2 + " has been added in playlist!");
                            }
                            else {
                                //console.log(val2 + " was already in playlist!");
                            }
                        });
                    }
                });
                
                console.log("New playlist is ");
                console.log(playlists[stream]);
                console.log("New folder is " + folders[stream]);
                
                if (initDone[stream] == 0){
                    doinit(playerFrom, playerTo, stream);
                }
            });
        }
    </script>
    
    
</body>
</html>
