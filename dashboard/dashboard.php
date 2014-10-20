<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("../config/session_check.php");
require_once("../config/utils.php");
require_once("../config/linkedin_config.php");
require("php/fetch_barter_profile.php");

$data = user_accounts($_SESSION['e'],$_SESSION['p']);
$snapshotData = get_snapshot_data($_SESSION['cid']);
$barter_trades = get_overview_data($_SESSION['cid']);
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
        <link href="css/lib/font-awesome.css" type="text/css" rel="stylesheet" />
    <!-- libraries -->
    <link href="css/lib/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />
    
    <!-- global styles -->
    <link rel="stylesheet" type="text/css" href="css/compiled/layout.css" />
    <link rel="stylesheet" type="text/css" href="css/compiled/elements.css" />
    <link rel="stylesheet" type="text/css" href="css/compiled/icons.css" />
	<link href="css/lib/morris.css" type="text/css" rel="stylesheet" />
    <!-- this page specific styles -->
    <link rel="stylesheet" href="css/compiled/index.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/compiled/chart-showcase.css" type="text/css" media="screen" />

	<!-- scripts -->
    <script src="js/latest_jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-ui-1.10.2.custom.min.js"></script>
    <!-- knob -->
    <!--<script src="js/jquery.knob.js"></script>-->
    <!-- flot charts -->
    <script src="js/jquery.flot.js"></script>
    <script src="js/jquery.flot.stack.js"></script>
    <script src="js/jquery.flot.resize.js"></script>
    <script src="js/theme.js"></script>
	 <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
	 <script src="js/morris.min.js"></script>

	<script type="text/javascript" src="../config/js/utils.js"></script>


	 <script>
	 var cid = "<?=$_SESSION['cid']?>";
	 
	
		$(function(){
			
			
			$.ajax({
			type: 'POST',
			data:{action: "checkSnapshotTimestamp", cid: cid},
			url:"../config/utils.php",
			success:function(data){
				var received_data = JSON.parse(data);
				//alert(received_data.checkStatus);
				
				//dont show notice
				if (received_data.checkStatus == false)
				{
					$( "#notice" ).hide();
				}
				else
				{
					//$( "#notice" ).show();
					 //$( "#notice" ).css("visibility", "visible");
					 $("#notice").removeClass().addClass("show_notice_notification");
					//$('#notice').attr('visibility', 'visible !important');
				}
			}});

			var localValue = 0;
			var nonLocalValue = 0;
			var newLocalValue = 0;
			var newNonLocalValue = 0;
			 
			$("#local_slider").change(function () {                    
			   localValue = $('#local_slider').val();
			   $("#local_percentage").html(localValue+"%");
			   
			   	newLocalValue =  100 - localValue;
				$('#non_local_slider').val(newLocalValue);
				$("#non_local_percentage").html(newLocalValue+"%");
			});
			
			$("#non_local_slider").change(function () {  	                  
			   nonLocalValue = $('#non_local_slider').val();
			   $("#non_local_percentage").html(nonLocalValue+"%");
			   
				newNonLocalValue =  100 - nonLocalValue;
				$('#local_slider').val(newNonLocalValue);
				$("#local_percentage").html(newNonLocalValue+"%");
			});
			
			$("#update_spending").on("click", function(e) {
				
				var trader_id = $('#trader_id').val();
				var form = "&trader_id="+trader_id+"&local_spend="+$('#local_slider').val()+"&non_local_spend="+$('#non_local_slider').val();
				//alert(form);
				$.ajax({
				type: 'POST',
				url: "php/update_trader_spend.php",
				data: form,
				success: function(data)
				{ 
					var received_data = JSON.parse(data);
					//alert(received_data.message);
					$("#update_spending").html("saved");
					$('#snapshot_date').html("<strong>"+received_data.data['timestamp']+"</strong>");
					setInterval(function(){$("#update_spending").html("update");},1000);
					$( "#notice" ).hide();
				}
			  });
			  
			  event.preventDefault();
			});
		});


	</script>
        
    <script type="text/javascript">
        $(function () {

            // jQuery Knobs
           /* $(".knob").knob();



            // jQuery UI Sliders
            $(".slider-sample1").slider({
                value: 100,
                min: 1,
                max: 500
            });
            $(".slider-sample2").slider({
                range: "min",
                value: 130,
                min: 1,
                max: 500
            });
            $(".slider-sample3").slider({
                range: true,
                min: 0,
                max: 500,
                values: [ 40, 170 ],
            });*/

            
		
				$.ajax({
				type: 'POST',
				url: "../config/utils.php",
				data:{action: "getSystemSpend", tid: cid},
				success: function(data)
				{ 
					var received_data = JSON.parse(data);
					
					console.log(received_data);
					var my_barter_trades = received_data.my_barter_output;
					var barter_trades = received_data.barter_output;
					var total_wealth = received_data.total_wealth;
					var als =  parseFloat(received_data.average_local_spend);
					var anls =  parseFloat(received_data.average_non_local_spend);
					
			
		
					  var plot = $.plot($("#statsChart"),
						[ 
							{ data: my_barter_trades, label: "My Trades"},
							{ data: barter_trades, label: "BARTER Trades"},
							{ data: total_wealth, label: "Community Trades"}
						],
						 {
							series: {
								lines: { show: true,
										lineWidth: 1,
										fill: true, 
										fillColor: { colors: [ { opacity: 0.1 }, { opacity: 0.13 } ] }
									 },
								points: { show: true, 
										 lineWidth: 2,
										 radius: 3
									 },
								shadowSize: 0,
								stack: false
							},
							grid: { hoverable: true, 
								   clickable: true, 
								   tickColor: "#f9f9f9",
								   borderWidth: 0
								},
							legend: {
									// show: false
									labelBoxBorderColor: "#fff"
								},  
							colors: ["#8dba23", "#86b4cc", "#f1c359"],
							xaxis: {
								ticks: [[1, "JAN"], [2, "FEB"], [3, "MAR"], [4,"APR"], [5,"MAY"], [6,"JUN"], 
									   [7,"JUL"], [8,"AUG"], [9,"SEP"], [10,"OCT"], [11,"NOV"], [12,"DEC"]],
								font: {
									size: 12,
									family: "Open Sans, Arial",
									variant: "small-caps",
									color: "#697695"
								}
							},
							yaxis: {
								ticks:3, 
								tickDecimals: 0,
								font: {size:12, color: "#9da3a9"}
							}
						 });
						 
						// Morris Donut Chart
						Morris.Donut({
							element: 'hero-donut',
							data: [
								{label: 'Local Spend', value: als },
								{label: 'Non Local Spend', value: anls }
							],
							colors: ["#86b4cc", "#cc8686"],
							formatter: function (y) { return y.toFixed(2) + "%" }
						});
					}});
           

            function showTooltip(x, y, contents) {
                $('<div id="tooltip">' + contents + '</div>').css( {
                    position: 'absolute',
                    display: 'none',
                    top: y - 30,
                    left: x - 50,
                    color: "#fff",
                    padding: '2px 5px',
                    'border-radius': '6px',
                    'background-color': '#000',
                    opacity: 0.80
                }).appendTo("body").fadeIn(200);
            }

            var previousPoint = null;
            $("#statsChart").bind("plothover", function (event, pos, item) {
                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0].toFixed(0),
                            y = item.datapoint[1].toFixed(0);

                        var month = item.series.xaxis.ticks[item.dataIndex].label;

                        showTooltip(item.pageX, item.pageY,
                                    item.series.label + " of " + month + ": " + y);
                    }
                }
                else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
        });
    </script>
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>
<div id="notice" class="notice_notification"><p>Please update your spending snapshot</p></div>
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

        <!-- upper main stats -->
        <div id="main-stats">
            <div class="row stats-row">
            	 <div class="col-md-4 stat">
                    <div class="data">
                        <span class="number"><?=$barter_trades['count'];?></span>
                        No. BARTER Trades
                    </div>
                    <span class="date">Total</span>
                </div>
               
                <div class="col-md-4 stat">
                    <div class="data">
                        <span class="number">£<?=$barter_trades['trades'];?></span>
                        BARTER SALES
                    </div>
                    <span class="date">Total</span>
                </div>
                
                 <div class="col-md-4 stat">
                    <div class="data">
                        <span class="number">£<?=$barter_trades['total_wealth'];?></span>
                        PROJECT WEALTH
                    </div>
                    <span class="date">Total</span>
                </div>
            </div>
        </div>
        <!-- end upper main stats -->




        <div id="pad-wrapper">
        
        <!-- UI Elements section -->
            <div class="row">
                               
                <div class="col-md-5 knobs">
                     <h3>Welcome,</h3>
                   <h4>to your BARTER dashboard, here you can create and view transactions, visualise; the flow of money, your customer demographic and community connections and activity.</h4>                     
                </div>
                <div class="col-md-6 showcase">
            
                    <div class="ui-group">
                      <h3>Spending Snapshot Update <a class="hover_span">why?
                <div class="hover_content">
                <p class="notice_small">As BARTER relies on transactions recorded using the web and mobile systems, we do require extra estimates from our users to work out how much money is leaking out of the community. So we kindly ask you to regulary enter a snapshot of how much your business spends outside of BARTER.</p>
                </div></a></h3>
                 <?php if($snapshotData['timestamp'] != 0){ echo "<p class='notice_small left'> Snapshot updated <span id='snapshot_date' class='notice_small'><strong>".convert_to_date($snapshotData['timestamp'])."</strong></span></p>";}?>
               <h4>Please give us an indication in terms of a percentage the amount of money your business has currently spent on goods/services since your last update</h4>     
                <table width="100%" border="0">
                <tr>
                <td align="left"><h4 class="heading_grey">local non-barter</h4></td>
                <td align="left"><input type="range" id="local_slider" value="<?=$snapshotData['local_spend']?>" name="local_slider" min="0" max="100"></td>
                <td align="left" ><p id="local_percentage" class="spacer"><?=$snapshotData['local_spend']?>%</p></td>
                </tr>
                <tr>
                <td align="left"><h4 class="heading_grey">non-local</h4></td>
                <td align="left"><input type="range" id="non_local_slider" value="<?=$snapshotData['non_local_spend']?>"  name="non_local_slider" min="0" max="100"></td>
                <td align="left"><p id="non_local_percentage"  class="spacer"><?=$snapshotData['non_local_spend']?>%</p></td>
                </tr>
                <tr>
                <td colspan="3" align="right">
              
                <input type="hidden" name="trader_id" id="trader_id" value="<?=$_SESSION['cid']?>" />
                <button  id="update_spending" type="button" class="btn btn-success-yellow  btn-lg btn-block ">update</button></td>
                </tr>
                </table>
                
                    </div>                        

           
                </div>
            </div>
            <!-- end UI elements section -->

 
            <br />
            <!-- statistics chart built with jQuery Flot -->
            <div class="row chart">
                <div class="col-md-12">
                    <h1 class="clearfix pull-left">
                        BARTER Trades Statistics                         
                    </h1>
                    <!--<div class="btn-group pull-right">
                        <button class="glow left">DAY</button>
                        <button class="glow middle active">MONTH</button>
                        <button class="glow right">YEAR</button>
                    </div>-->
                </div>
                <div class="col-md-12">
                    <div id="statsChart"></div>
                </div>
            </div>
            <!-- end statistics chart -->


			<!-- morris bar & donut charts -->
            <div class="row section">
                <div class="col-md-12">
                    <h3>Spending Overview</h3>
                </div>
                <div class="col-md-6 chart">
                    <h5>Local v Non Local</h5>
                  <h4>As BARTER can only capture trades that are made using it's system, we ask our users to provide an estimate their current spending habbits . The "spending trend" chart highlights how the community spends their money</h4>
                </div>
                <div class="col-md-5 chart">
                    <h5>Spending Trends</h5>
                    <div id="hero-donut" style="height: 250px;"></div>    
                </div>
            </div>
            <!-- UI Elements section -->
            <!--<div class="row section ui-elements">
                <div class="col-md-12">
                    <h4>UI Elements</h4>
                </div>                
                <div class="col-md-5 knobs">
                    <div class="knob-wrapper">
                        <input type="text" value="50" class="knob" data-thickness=".3" data-inputColor="#333" data-fgColor="#30a1ec" data-bgColor="#d4ecfd" data-width="150">
                        <div class="info">
                            <div class="param">
                                <span class="line blue"></span>
                                Active users
                            </div>
                        </div>
                    </div>
                    <div class="knob-wrapper">
                        <input type="text" value="75" class="knob second" data-thickness=".3" data-inputColor="#333" data-fgColor="#3d88ba" data-bgColor="#d4ecfd" data-width="150">
                        <div class="info">
                            <div class="param">
                                <span class="line blue"></span>
                                % disk space usage
                            </div>
                        </div>
                    </div>                        
                </div>
                <div class="col-md-6 showcase">
                    <div class="ui-sliders">
                        <div class="slider slider-sample1 vertical-handler"></div>
                        <div class="slider slider-sample2"></div>
                        <div class="slider slider-sample3"></div>
                    </div>
                    <div class="ui-group">
                        <a class="btn-flat inverse">Large Button</a>
                        <a class="btn-flat gray">Large Button</a>
                        <a class="btn-flat default">Large Button</a>
                        <a class="btn-flat primary">Large Button</a>
                    </div>                        

                    <div class="ui-group">
                        <a class="btn-flat icon">
                            <i class="tool"></i> Icon button
                        </a>
                        <a class="btn-glow small inverse">
                            <i class="shuffle"></i>
                        </a>
                        <a class="btn-glow small primary">
                            <i class="setting"></i>
                        </a>
                        <a class="btn-glow small default">
                            <i class="attach"></i>
                        </a>
                        <div class="ui-select">
                            <select>
                                <option selected>Dropdown</option>
                                <option>Custom selects</option>
                                <option>Pure css styles</option>
                            </select>
                        </div>

                        <div class="btn-group">
                            <button class="glow left">LEFT</button>
                            <button class="glow right">RIGHT</button>
                        </div>
                    </div>
                </div>
            </div>-->
            <!-- end UI elements section -->

            <!-- table sample -->
            <!-- the script for the toggle all checkboxes from header is located in js/theme.js -->
            <!--<div class="table-products section">
                <div class="row head">
                    <div class="col-md-12">
                        <h4>Products <small>Table sample</small></h4>
                    </div>
                </div>

                <div class="row filter-block">
                    <div class="col-md-8 col-md-offset-5">
                        <div class="ui-select">
                            <select>
                              <option>Filter users</option>
                              <option>Signed last 30 days</option>
                              <option>Active users</option>
                            </select>
                        </div>
                        <input type="text" class="search">
                        <a class="btn-flat new-product">+ Add product</a>
                    </div>
                </div>

                <div class="row">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="col-md-3">
                                    <input type="checkbox">
                                    Product
                                </th>
                                <th class="col-md-3">
                                    <span class="line"></span>Description
                                </th>
                                <th class="col-md-3">
                                    <span class="line"></span>Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                         
                            <tr class="first">
                                <td>
                                    <input type="checkbox">
                                    <div class="img">
                                        <img src="img/table-img.png" alt="pic" />
                                    </div>
                                    <a href="#">There are many variations </a>
                                </td>
                                <td class="description">
                                    if you are going to use a passage of Lorem Ipsum.
                                </td>
                                <td>
                                    <span class="label label-success">Active</span>
                                    <ul class="actions">
                                        <li><i class="table-edit"></i></li>
                                        <li><i class="table-settings"></i></li>
                                        <li class="last"><i class="table-delete"></i></li>
                                    </ul>
                                </td>
                            </tr>
                      
                            <tr>
                                <td>
                                    <input type="checkbox">
                                    <div class="img">
                                        <img src="img/table-img.png" alt="pic" />
                                    </div>
                                    <a href="#">Internet tend</a>
                                </td>
                                <td class="description">
                                    There are many variations of passages.
                                </td>
                                <td>
                                    <ul class="actions">
                                        <li><i class="table-edit"></i></li>
                                        <li><i class="table-settings"></i></li>
                                        <li class="last"><i class="table-delete"></i></li>
                                    </ul>
                                </td>
                            </tr>
                            
                            <tr>
                                <td>
                                    <input type="checkbox">
                                    <div class="img">
                                        <img src="img/table-img.png" alt="pic" />
                                    </div>
                                    <a href="#">Many desktop publishing </a>
                                </td>
                                <td class="description">
                                    if you are going to use a passage of Lorem Ipsum.
                                </td>
                                <td>
                                    <ul class="actions">
                                        <li><i class="table-edit"></i></li>
                                        <li><i class="table-settings"></i></li>
                                        <li class="last"><i class="table-delete"></i></li>
                                    </ul>
                                </td>
                            </tr>
                      
                            <tr>
                                <td>
                                    <input type="checkbox">
                                    <div class="img">
                                        <img src="img/table-img.png" alt="pic" />
                                    </div>
                                    <a href="#">Generate Lorem </a>
                                </td>
                                <td class="description">
                                    There are many variations of passages.
                                </td>
                                <td>
                                    <span class="label label-info">Standby</span>
                                    <ul class="actions">
                                        <li><i class="table-edit"></i></li>
                                        <li><i class="table-settings"></i></li>
                                        <li class="last"><i class="table-delete"></i></li>
                                    </ul>
                                </td>
                            </tr>
                         
                            <tr>
                                <td>
                                    <input type="checkbox">
                                    <div class="img">
                                        <img src="img/table-img.png" alt="pic" />
                                    </div>
                                    <a href="#">Internet tend</a>
                                </td>
                                <td class="description">
                                    There are many variations of passages.
                                </td>
                                <td>                                        
                                    <ul class="actions">
                                        <li><i class="table-edit"></i></li>
                                        <li><i class="table-settings"></i></li>
                                        <li class="last"><i class="table-delete"></i></li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <ul class="pagination">
                    <li><a href="#">&laquo;</a></li>
                    <li class="active"><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">4</a></li>
                    <li><a href="#">&raquo;</a></li>
                </ul>
            </div>
            -->
            
        </div>
    </div>


	
</body>
</html>