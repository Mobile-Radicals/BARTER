<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("../config/session_check.php");
require_once("../config/linkedin_config.php");
require_once("php/fetch_barter_profile.php");
require_once("php/fetch_barter_connections.php");
require_once("../config/utils.php");
?>
<!DOCTYPE html>
<html>
<head>
	<title>BARTER - Dashboard</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    
   <!-- bootstrap -->
    <link href="css/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="css/bootstrap/bootstrap-overrides.css" type="text/css" rel="stylesheet" />
	<link href="../config/css/bootstrap-select.min.css" rel="stylesheet">
	<link rel="icon" type="image/png" href="../images/fav.png">
    
    <!-- libraries -->
    <link href="css/lib/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />
    <link href="css/lib/font-awesome.css" type="text/css" rel="stylesheet" />
    
    <!-- global styles -->
    <link rel="stylesheet" type="text/css" href="css/compiled/layout.css" />
    <link rel="stylesheet" type="text/css" href="css/compiled/elements.css" />
    <link rel="stylesheet" type="text/css" href="css/compiled/icons.css" />

    <!-- this page specific styles -->
    <link rel="stylesheet" href="css/compiled/index.css" type="text/css" media="screen" />
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <!-- scripts -->
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-ui-1.10.2.custom.min.js"></script>
    <script src="js/theme.js"></script>

	
    <script src="../config/js/jqBootstrapValidation.js"></script>
	<script src="../config/js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="../config/js/utils.js"></script>

	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDp2D1QzFdiVeLtsOQdzSc0rJQBx9HID9k&sensor=false"></script>
	<script type="text/javascript" src="../config/js/utils.js"></script>
	

	<script type="text/javascript">
    var infowindow;
    var map;
    var moneyPath = [];
    var MY_MAPTYPE_ID = 'custom_style';
    var moneyFlow;
    var bater_pins = [];
    
    function initialize(){
        
          var featureOpts=[
                {
                  stylers: [
                    { hue: '#f2f2f2' },
                    { visibility: 'simplified' },
                    { gamma: 0.1 },
                    { weight: 0.5 }
                  ]
                },
                {
                  elementType: 'labels',
                  stylers: [
                    { visibility: 'off' }
                  ]
                },
                {
                  featureType: 'water',
                  stylers: [
                    { color: '#8cba3e' }
                  ]
                },
                {
                  featureType: 'lancscape',
                  stylers: [
                    { color: '#8cba3e' }
                  ]
                },
                {
                  featureType: 'road',
                  stylers: [
                    { color: '#f2f2f2' },
                    { weight: 0.6 }
                  ]
                }
              ];
              
        var mapOptions = {
            zoom: 15,
            center: new google.maps.LatLng(54.046575001475865, -2.800739901722409),
            zoomControl: true,
            scaleControl: false,
            scrollwheel: true,
            disableDoubleClickZoom: true,
            disableDefaultUI: true,
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.ROADMAP, MY_MAPTYPE_ID]
            },
            mapTypeId: MY_MAPTYPE_ID
        };
        
        var directionSymbol = {
            path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW
        };
      
        /*var homeSymbol = {
            path: 'M -2,0 0,-2 2,0 0,2 z',
            strokeColor: '#8cba3e',
            fillColor: '#8cba3e',
            fillOpacity: 1
        };*/
      
        map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
        
        var styledMapOptions = {
            name: 'Custom Style'
        };
    
        var customMapType = new google.maps.StyledMapType(featureOpts, styledMapOptions);
      
        downloadUrl("php/get_transactions_points.php", function(data) 
        {
            var markers = data.documentElement.getElementsByTagName("marker");
            //console.log(markers.length);
            for (var i = 0; i < markers.length; i++) 
            {
                var latlng = new google.maps.LatLng(parseFloat(markers[i].getAttribute("lat")), parseFloat(markers[i].getAttribute("lng")));
                var marker = createMarker(markers[i].getAttribute("name"), markers[i].getAttribute("number_of_connections"),latlng, markers[i].getAttribute("icon"));
                
                if (markers[i].getAttribute("connections") == "true")
                {
                    var connnections = markers[i].getElementsByTagName("connection")
        
                    for (j = 0; j < connnections.length; j++)
                    {
                        var latlng1 = new google.maps.LatLng(parseFloat(connnections[j].getAttribute('lat')), parseFloat(connnections[j].getAttribute('lng')));
                        //console.log(latlng1);
                        moneyFlow = new google.maps.Polyline({
                        path: [latlng, latlng1],
                        geodesic: false,
                        //strokeColor: markers[i].getAttribute("color"),
                        strokeOpacity: 0,
                        strokeWeight: 3,
                        clickable: true,
                        polylineID: i,
                        icons: 
                            [{
                                icon: directionSymbol,
                                offset: '100%'
                            }],
                            map: map
                        });
                    }
                }
                else
                {
                    //console.log("no connections");
                }
            }
        });
        
             
        map.mapTypes.set(MY_MAPTYPE_ID, customMapType);
        
        google.maps.event.addListener(map, 'click', function() {
            if (infowindow) infowindow.close();
        });
    
    }

    function createMarker(name, noConnections, latlng, markerIcon) {
        
        
        var image = {
            url: markerIcon,
            // This marker is 20 pixels wide by 32 pixels tall.
            size: new google.maps.Size(30, 30),
            // The origin for this image is 0,0.
            origin: new google.maps.Point(0,0),
            // The anchor for this image is the base of the flagpole at 0,32.
            anchor: new google.maps.Point(15, 30)
        };
      
        var marker = new google.maps.Marker({position: latlng, map: map, icon:image, animation: google.maps.Animation.DROP,draggable: false});
        google.maps.event.addListener(marker, "click", function() {
            if (infowindow) infowindow.close();
                var contentString = '<div id="content">'+
                  '<div id="siteNotice">'+
                  '</div>'+
                  '<img src="'+markerIcon+'"/>'+
                  '<h1 id="firstHeading" class="firstHeading">'+name+'</h1>'+
                  '<div id="bodyContent">'+
                  '<p> Number of BARTER Connections <b>'+noConnections+'</b></p>'+
                  /*'<p><b>Uluru</b>, also referred to as <b>Ayers Rock</b>, is a large ' +
                  'sandstone rock formation in the southern part of the '+
                  'Northern Territory, central Australia. It lies 335&#160;km (208&#160;mi) '+
                  'south west of the nearest large town, Alice Springs; 450&#160;km '+
                  '(280&#160;mi) by road. Kata Tjuta and Uluru are the two major '+
                  'features of the Uluru - Kata Tjuta National Park. Uluru is '+
                  'sacred to the Pitjantjatjara and Yankunytjatjara, the '+
                  'Aboriginal people of the area. It has many springs, waterholes, '+
                  'rock caves and ancient paintings. Uluru is listed as a World '+
                  'Heritage Site.</p>'+
                  '<p>Attribution: Uluru, <a href="http://en.wikipedia.org/w/index.php?title=Uluru&oldid=297882194">'+
                  'http://en.wikipedia.org/w/index.php?title=Uluru</a> '+
                  '(last visited June 22, 2009).</p>'+*/
                  '</div>'+
                  '</div>';
            infowindow = new google.maps.InfoWindow({content: contentString});
            infowindow.open(map, marker);
        });
        return marker;
        
    }
 
    google.maps.event.addDomListener(window, 'load', initialize);
    
    </script>

</head>
<body>

    <!-- navbar -->
    <header class="navbar navbar-inverse" role="banner">
       <?php require_once('../config/header3.php');?>
    </header>
    <!-- end navbar -->

    <!-- sidebar -->
    <div id="sidebar-nav">
       <?php require_once('../config/sidebar.php');?>
    </div>
    <!-- end sidebar -->
 
	<!-- main container -->
    <div class="content">
    
 
      <h3 style="position:absolute !important; z-index:9999; color:#404040 !important; margin:10px 0 0 30px;">BARTER Traders</h3>
        <div id="map-canvas"></div>
    </div>
    <!-- end main container -->
 
</body>
</html>