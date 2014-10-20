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

	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDp2D1QzFdiVeLtsOQdzSc0rJQBx9HID9k&sensor=false&libraries=visualization"></script>
	<script type="text/javascript" src="../config/js/utils.js"></script>
	

	 <script>
// Adding 500 Data Points
var map, pointarray, heatmap;

var transData = [];
	
function initialize() {
 
  var mapOptions = {
		zoom: 11,
		center: new google.maps.LatLng(54.046575001475865, -2.800739901722409),
		zoomControl: true,
		scaleControl: false,
		scrollwheel: false,
		disableDoubleClickZoom: false,
		disableDefaultUI: true,
	};
	

  map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);

downloadUrl("php/get_all_transactions_points.php", function(data) 
	{
		var markers = data.documentElement.getElementsByTagName("marker");
		//console.log(markers.length);
		for (var i = 0; i < markers.length; i++) 
		{
			var latlng = new google.maps.LatLng(parseFloat(markers[i].getAttribute("lat")), parseFloat(markers[i].getAttribute("lng")),parseFloat(markers[i].getAttribute("weight")));
			var weight = parseFloat(markers[i].getAttribute("weight"));
			//console.log(weight);
			transData.push(latlng);
			
		}
		  var pointArray = new google.maps.MVCArray(transData);
		  heatmap = new google.maps.visualization.HeatmapLayer({
			data: pointArray
		  });
		  
		    heatmap.setMap(map);
  			 heatmap.set('radius', heatmap.get('radius') ? null : 20);
	});
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
    
 
      <h3 style="position:absolute !important; z-index:9999; color:#404040 !important; margin:10px 0 0 30px;"> BARTER Transactions</h3>
        <div id="map-canvas"></div>
    </div>
    <!-- end main container -->
 
</body>
</html>