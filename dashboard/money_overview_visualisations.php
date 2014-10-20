<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("../config/session_check.php");
require_once("../config/utils.php");
require_once("../config/linkedin_config.php");
require("php/fetch_barter_profile.php");

$data = user_accounts($_SESSION['e'],$_SESSION['p']);
$snapshotData = get_snapshot_data($_SESSION['cid']);
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

	<style>
    #content_progress {
        width:100%;
		clear:both;
		min-width:400px;
    }
    
    #barter_thermometer {
         
         height:500px; 
		
    }
    
    #barter_thermometer .track {
        height:280px;
        width:100px;
        border: 1px solid #aaa;
        position: relative;
        margin:0 auto;
		bottom:0;
    }
    
    #barter_thermometer .progress {
        height:0%;
        width:100%;
        background: rgb(141,186,0);
        background: rgba(141,186,0,0.6);
        position: absolute;
        bottom:0;
        left:0;
		margin: 0px;
    }
    
    #barter_thermometer .goal {
        position:absolute;
        top:0;
    }
    
    #barter_thermometer .amount {
		width: 100%;
		font-family: Trebuchet MS;
		font-weight: bold;
		color:#333;
		font-size:20px;
		text-align:center;
	
		color:#060;
    }
    
	 #local_thermometer {
       
          height:500px; 
    
    }
	
	 #local_thermometer .track {
        height:280px;
        width:100px;
        border: 1px solid #aaa;
        position: relative;
        margin:0 auto;
		bottom:0;
    }
    
    #local_thermometer .progress {
        height:0%;
        width:100%;
        background: rgb(247,201,11);
        background: rgba(247,201,11,0.6);
        position: absolute;
        bottom:0;
        left:0;
		margin: 0px;
    }
    
    #local_thermometer .goal {
        position:absolute;
        top:0;
    }
    
    #local_thermometer .amount {
		width: 100%;
		font-family: Trebuchet MS;
		font-weight: bold;
		color:#333;
		font-size:20px;
		text-align:center;
		color:#060;
    }
	
	 #non_local_thermometer {
        
         height:500px; 
      
    }
	
	#non_local_thermometer .track {
        height:280px;
        width:100px;
        border: 1px solid #aaa;
        position: relative;
        margin:0 auto;
		bottom:0;
    }
    
    #non_local_thermometer .progress {
        height:0%;
        width:100%;
        background: rgb(222,112,71);
        background: rgba(222,112,71,0.6);
        position: absolute;
        bottom:0;
        left:0;
		margin: 0px;
    }
    
    #non_local_thermometer .goal {
        position:absolute;
        top:0;
    }
    
    #non_local_thermometer .amount {
		width: 100%;
		font-family: Trebuchet MS;
		font-weight: bold;
		color:#333;
		font-size:20px;
		text-align:center;
	
		color:#060;
    }
	
	.container
	{
		clear:both;
		bottom:0;
	}
    </style>

    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
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
   
        
        <div id="pad-wrapper" class="gallery">
            <div class="row header">
                <div class="col-md-12">
                    <h3>My Spending Overview Meter</h3>
                </div>                
            </div>

            <!-- gallery wrapper -->
            <div class="gallery-wrapper">
                <div class="row gallery-row">
                    <!-- single image -->
                    <div class="col-xs-6 col-md-4"><div class="img-box">
                        <div id="barter_thermometer">
                        <h3 id="barter_community_spend" class="heading_grey" align="center"></h3>
                        <h4 align="center">BARTER Community</h4>
                            <div class="track">
                                <div class="goal">
                                    <!--<div class="amount"></div>-->
                                </div>
                                <div class="progress"
                                </div>
                            </div>
                           
                        </div>
                        <div class="container">
                            <div class="amount">0.00%</div>
                            <br />
                            <h4 align="center">Your Impact</h4>
                            <h2 id="my_barter_spend" class="heading_grey" align="center"></h2>
                        </div>
                    </div>
                   </div></div>
                    <!-- single image -->
                    <div class="col-xs-6 col-md-4"><div class="img-box">
                     <div id="local_thermometer">
                        <h3 id="local_community_spend" class="heading_grey" align="center"></h3>
                        <h4 align="center">Local Community</h4>
                            <div class="track">
                                <div class="goal">
                                    <!--<div class="amount"></div>-->
                                </div>
                                <div class="progress"
                                </div>
                            </div>
                        </div>
                         <div class="container">
                             <div class="amount">0.00%</div>
                            <br />
                            <h4 align="center">Your Impact</h4>
                            <h2 id="my_local_spend" class="heading_grey" align="center"></h2>
                         </div>
                    </div>
                   </div></div>
                    <!-- single image -->
                    <div class="col-xs-6 col-md-4"><div class="img-box">
                    <div id="non_local_thermometer">
                        <h3 id="barter_community_spend" class="heading_grey" align="center"></h3>
                        <h4 align="center">Non Local Community</h4>
                            <div class="track">
                                <div class="goal">
                                    <!--<div class="amount"></div>-->
                                </div>
                                <div class="progress"
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="amount">0.00%</div>
                            <br />
                            <h4 align="center">Your Impact</h4>
                            <h2 id="my_non_local_spend" class="heading_grey" align="center"></h2>
                        </div>
                    </div>
       				</div></div>
       
                  
                </div>
                  

                                 
                </div>
            </div>
            
    </div>
       
	<!-- scripts -->
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-ui-1.10.2.custom.min.js"></script>
    <!-- knob -->
    <script src="js/jquery.knob.js"></script>
    <!-- flot charts -->
    <script src="js/jquery.flot.js"></script>
    <script src="js/jquery.flot.stack.js"></script>
    <script src="js/jquery.flot.resize.js"></script>
    <script src="js/theme.js"></script>


	<script type="text/javascript" src="../config/js/utils.js"></script>
	
    <script>
	
	
	
	$(function() {
				
			var cid = "<?=$_SESSION['cid']?>";

			  
			  $.ajax({
				type: 'POST',
				url: "../config/utils.php",
				data: { action: "getCommunitySpend", tid: cid },
				success:function(data)
				{
				
					console.log(data);
					var received_data = JSON.parse(data);
					
					
					thermometer( 100, received_data.barter_total, "#barter_thermometer", true );
					thermometer( 100, received_data.local_total, "#local_thermometer", true );
					thermometer( 100, received_data.non_local_total, "#non_local_thermometer", true );
					
					$("#my_barter_spend").html("&pound;"+received_data.my_barter);
					$("#my_local_spend").html("&pound;"+received_data.my_local);
					$("#my_non_local_spend").html("&pound;"+received_data.my_non_local);
				}
			});
			  
			
		  
		function thermometer(goalAmount, progressAmount, type, animate) {
			"use strict";
		
		
			var $thermo = $(type),
				$progress = $(".progress", $thermo),
				$goal = $(".goal", $thermo),
				percentageAmount;
		
			goalAmount = goalAmount || parseFloat( $goal.text() ),
			progressAmount = progressAmount || parseFloat( $progress.text() ),
			percentageAmount =  Math.min( Math.round(progressAmount / goalAmount * 1000) / 10, 100); //make sure we have 1 decimal point
		
			if (isNaN(percentageAmount) == true)
			{
				percentageAmount = 0;
				$thermo.find(".amount").text(0 + "%");
			}
			else
			{
				$thermo.find(".amount").text(progressAmount.toFixed(2) + "%");
			}
			
			
		
		
			//let's set the progress indicator
			$thermo.find(".amount").hide();
			if (animate !== false) {
				$progress.animate({
					"height": percentageAmount + "%"
				}, 1200, function(){
					$thermo.find(".amount").fadeIn(500);
				});
			}
			else {
				$progress.css({
					"height": percentageAmount + "%"
				});
				$thermo.find(".amount").fadeIn(500);
			}
		}
		});
    </script>
</body>
</html>