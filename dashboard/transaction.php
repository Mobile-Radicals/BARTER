<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("../config/session_check.php");
require_once("../config/linkedin_config.php");
require_once("php/fetch_barter_profile.php");
require_once("php/fetch_barter_connections.php");
require_once("../config/utils.php");
$trades = get_overview_data($_SESSION['cid']);
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
	<!--<link href="../config/css/bootstrap-select.min.css" rel="stylesheet">-->
	<link rel="icon" type="image/png" href="../images/fav.png">
        <link href="css/lib/uniform.default.css" type="text/css" rel="stylesheet" />
    <link href="css/lib/select2.css" type="text/css" rel="stylesheet" />
    <link href="css/lib/bootstrap.datepicker.css" type="text/css" rel="stylesheet" />

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
    <script src="js/latest_jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-ui-1.10.2.custom.min.js"></script>
	<script src="js/jquery.uniform.min.js"></script>
    <script src="js/select2.min.js"></script>
    <script src="js/theme.js"></script>

    <!-- call this page plugins -->
    <script type="text/javascript">
        $(function () {

            // select2 plugin for select elements
            $(".select2").select2({
                placeholder: "Select a State"
            });

     
        });
    </script>
   
    <script src="../config/js/jqBootstrapValidation.js"></script>
	
	
     <script>
	
	var customer_type = 0;
	
	$(function() {
	
		//$('select').selectpicker();
		
		var numStars = 0;
		
		$("div.star-rating > s").on("click", function(e) {
			numStars = $(e.target).parentsUntil("div").length+1;
			//alert(numStars + (numStars == 1 ? " star" : " stars!"));
			
			for (var i=0;i<=5;i++)
			{
				if (i <= numStars)
				{
					//console.log(i);
					$('#star'+i).addClass("rated");
				}
				else
				{
					//console.log(" x "+i);
					$('#star'+i).removeClass("rated");
				}
			}
		});
		
		var is_goods = 0;
		var is_service = 0;
		var counter = 1;
		
		$("#cust_type").on("change", function(e) {
	
			var t = $("#cust_type :selected").val();
			if (t == 'business')
			{
				$('#local_customer').hide();
				$('#external_customer').hide();	
				$('#barter_trader_customer').show();
				$('#barter_customer').hide();
				$('#barter_traders_dropdown').show();
				//$('#barter_traders_manual_input').show();
				$('#non_barter_input').hide();
				$('#points').show();
				customer_type = 0;
				counter = 1;
				$('#trans_type').text(": trader (B2B)");
				
			}
			else if (t == 'customer')
			{
				$('#local_customer').hide();
				$('#external_customer').hide();	
				$('#barter_trader_customer').hide();
				$('#barter_customer').show();
				$('#barter_traders_dropdown').hide();
				//$('#barter_traders_manual_input').hide();
				$('#non_barter_input').show();
				$('#consumer_name').text("Card ID:");
				$('#points').show();
				customer_type  = 1;
				counter = 2;
				$('#trans_type').text(": customer (B2C)");
				
			}
			else if (t == 'local')
			{
				$('#local_customer').show();
				$('#external_customer').hide();	
				$('#barter_trader_customer').hide();
				$('#barter_customer').hide();
				$('#barter_traders_dropdown').hide();
				//$('#barter_traders_manual_input').hide();
				$('#non_barter_input').show();
				$('#consumer_name').text("Name: ");
				$('#points').hide();
				numStars = 0;
				customer_type  = 2;
				counter = 3;
				$('#trans_type').text(": local non-barter");
			}
			else if (t == 'non_local')
			{
				$('#local_customer').hide();
				$('#external_customer').show();	
				$('#barter_trader_customer').hide();
				$('#barter_customer').hide();
				$('#barter_traders_dropdown').hide();
				//$('#barter_traders_manual_input').hide();
				$('#non_barter_input').show();
				$('#consumer_name').text("Name: ");
				$('#points').hide();
				numStars = 0;
				customer_type  = 3;
				counter = 0;
				$('#trans_type').text(": non-local");
			}
			/*if (counter == 0)
			{
				$('#local_customer').hide();
				$('#external_customer').hide();	
				$('#barter_trader_customer').show();
				$('#barter_customer').hide();
				$('#barter_traders_dropdown').show();
				//$('#barter_traders_manual_input').show();
				$('#non_barter_input').hide();
				$('#points').show();
				customer_type = 0;
				counter = 1;
				$('#trans_type').text(": trader (B2B)");
				
			}
			else if (counter == 1)
			{
				$('#local_customer').hide();
				$('#external_customer').hide();	
				$('#barter_trader_customer').hide();
				$('#barter_customer').show();
				$('#barter_traders_dropdown').hide();
				//$('#barter_traders_manual_input').hide();
				$('#non_barter_input').show();
				$('#consumer_name').text("Card ID:");
				$('#points').show();
				customer_type  = 1;
				counter = 2;
				$('#trans_type').text(": customer (B2C)");
				
			}
			else if (counter == 2)
			{
				$('#local_customer').show();
				$('#external_customer').hide();	
				$('#barter_trader_customer').hide();
				$('#barter_customer').hide();
				$('#barter_traders_dropdown').hide();
				//$('#barter_traders_manual_input').hide();
				$('#non_barter_input').show();
				$('#consumer_name').text("Name: ");
				$('#points').hide();
				numStars = 0;
				customer_type  = 2;
				counter = 3;
				$('#trans_type').text(": local non-barter");
			}
			else if (counter == 3)
			{
				$('#local_customer').hide();
				$('#external_customer').show();	
				$('#barter_trader_customer').hide();
				$('#barter_customer').hide();
				$('#barter_traders_dropdown').hide();
				//$('#barter_traders_manual_input').hide();
				$('#non_barter_input').show();
				$('#consumer_name').text("Name: ");
				$('#points').hide();
				numStars = 0;
				customer_type  = 3;
				counter = 0;
				$('#trans_type').text(": non-local");
			}*/
		});
		
		$("#goods_icon").on("click", function(e) {
	
			if ($('#goods_icon_on').is(":hidden")) 
			{
				
				$('#goods_icon_off').hide();	
				$('#goods_icon_on').show();
				is_goods = 1;
				if (($('#goods_icon_on').is(":visible")) && ($('#service_icon_on').is(":visible")))
					$('#trans_type_title').text("Transaction Type: Goods & Service");
				else
					$('#trans_type_title').text("Transaction Type: Goods");
			}
			else
			{
				$('#goods_icon_off').show();	
				$('#goods_icon_on').hide();
				is_goods = 0;
				if ($('#service_icon_on').is(":visible")) 
					$('#trans_type_title').text("Transaction Type: Service");
				else
					$('#trans_type_title').text("Transaction Type: ");
			}
		});
		
		$("#service_icon").on("click", function(e) {
	
			if ($('#service_icon_on').is(":hidden")) 
			{
				
				$('#service_icon_off').hide();	
				$('#service_icon_on').show();
				$('#service_icon').val('service_off');
				is_service = 1;
				if (($('#goods_icon_on').is(":visible")) && ($('#service_icon_on').is(":visible")))
					$('#trans_type_title').text("Transaction Type: Goods & Service");
				else
					$('#trans_type_title').text("Transaction Type: Service");
			}
			else
			{
				$('#service_icon_off').show();	
				$('#service_icon_on').hide();
				$('#service_icon').val('service_on');
				is_service = 0;
				if ($('#goods_icon_on').is(":visible")) 
					$('#trans_type_title').text("Transaction Type: Goods");
				else
					$('#trans_type_title').text("Transaction Type: ");
			}
		});
		
		if ($('#service_icon_on').is(":visible")) 
		{
			is_service = 1;
			$('#trans_type_title').text("Transaction Type: Service");
		}
		
		if ($('#goods_icon_on').is(":visible")) 
		{
			is_goods = 1;
			$('#trans_type_title').text("Transaction Type: Goods");
		}
		
		if (($('#goods_icon_on').is(":visible")) && ($('#service_icon_on').is(":visible")))
		{
			$('#trans_type_title').text("Transaction Type: Goods & Service");
		}

		$("input,select,textarea,button").not("[type=submit]").jqBootstrapValidation({
			
			preventSubmit: true,
				submitError: function($form, event, errors) {
					
					console.log("submitError");
					alert("There seems to be an error submitting your details, please check you have filled in all the required fields.");
				},
				
			submitSuccess: function ($form, event) {
				

				var form = $('#add_transaction').find('input, textarea, select, button')
				   .not(':checkbox')
				   .serialize()
				
				var cid = $("#consumder_id :selected").val();
				var cname = $("#customer_id1").val();
				
				form += "&consumer_id="+cid+"&customer_name="+cname+"&customer_type="+customer_type+"&points="+numStars+"&is_goods="+is_goods+"&is_service="+is_service;
				console.log(form);   
				
			  $.ajax({
				type: 'POST',
				url: $form.attr('action'),
				data: form,
				success: function(data)
				{ 
					// just to confirm ajax submission
					console.log('submitted successfully!');
					var received_data = JSON.parse(data);
					//alert(received_data.message);
					
					$('#add_transaction')[0].reset();
					
					for (var i=0;i<=5;i++)
					{
						//console.log(" x "+i);
						$('#star'+i).removeClass("rated");			
					}
					
					$("#community_spend").html("&pound;"+received_data.community_spend);
					$("#my_spend").html("&pound;"+received_data.my_contributions);
					$("#barter_total_spend").html("&pound;"+received_data.my_contributions);
					$("#total_spend").html("&pound;"+received_data.my_local_contributions);
					$("#non_total_spend").html("&pound;"+received_data.my_non_local_contributions);
					 //call without the parameters to have it read from the DOM
					//thermometer();
					// or with parameters if you want to update it using JavaScript.
					// you can update it live, and choose whether to show the animation
					// (which you might not if the updates are relatively small)
					thermometer( 100, received_data.contributions, true );
				}
			  });
			  
			  event.preventDefault();
			},
			
			filter: function() {
					return $(this).is(":visible");
				}
				
		
		  });
		  
		function thermometer(goalAmount, progressAmount, animate) {
			"use strict";
		
			var $thermo = $("#thermometer"),
				$progress = $(".progress", $thermo),
				$goal = $(".goal", $thermo),
				percentageAmount;
		
			goalAmount = goalAmount || parseFloat( $goal.text() ),
			progressAmount = progressAmount || parseFloat( $progress.text() ),
			percentageAmount =  Math.min( Math.round(progressAmount / goalAmount * 1000) / 10, 100); //make sure we have 1 decimal point
		
			$thermo.find(".amount").text(progressAmount.toFixed(2) + "%");
		
		
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
    
    	 <!-- upper main stats -->
        <div id="main-stats">
            <div class="row stats-row">
            	 <div class="col-md-4 stat">
                    <div class="data" >
                        <span class="number" id="barter_total_spend">£<?=$trades['my_trades'];?></span>
                        my barter sales
                    </div>
                   <span class="date">since joining</span>
                </div>
               
                <div class="col-md-4 stat">
                    <div class="data">
                        <span class="number" id="total_spend">£<?=$trades['my_local_total'];?></span>
                        my local sales
                    </div>
                    <span class="date">since joining</span>
                </div>
                
                 <div class="col-md-4 stat">
                    <div class="data">
                        <span class="number" id="non_total_spend">£<?=$trades['my_non_local_total'];?></span>
                       my non-local sales
                    </div>
                    <span class="date">since joining</span>
                </div>
            </div>
        </div>
        <!-- end upper main stats -->
        
          <div id="pad-wrapper">
                  <div>
                  
                  <h3>Transaction <span id="trans_type">: trader (B2B)</span></h3>
     
     				<h4>Input your sale transactions below</h4>
                    <h4 style="color:#E90307 !important; background-color:#FCDC02; font-weight:bold; padding:10px; width:360px">* ONLY THE SELLER CAN INPUT DATA HERE *</h4>
            			<div class="col-xs-12 col-md-6"> 
                    
                    <form class="form-horizontal"  id="add_transaction" name="add_transaction" action="php/add_transaction.php" method="post">
                     
                     <div class="field-box">
                        <label  for="cust_type">Transaction Type: </label>
                            <select id="cust_type" name="dob" data-size="auto" style="width:250px" class="select3">
                            <option value="business">BARTER B2B</option>
                            <option value="customer">BARTER B2C</option>
                            <option value="local">Local</option>
                            <option value="non_local">Non Local</option>
                            </select>
                      </div>
                 
                      <div class="field-box" id="barter_traders_dropdown" style="max-height:200px !important">
              				  <div class="field-box">
                           <label>Trader:</label>
                          <select id="consumder_id" data-size="auto" style="width:250px" class="select2">
                          <?php
                                $data = get_traders($_SESSION['uid'], 1);
                                //print_r($data);
                                $obj = query_user($_SESSION['uid']);
                          
                                $linkedin_data = (fetch_barter_connections($obj[0]));
                                
                                $frequent_traders_data = get_traders_frequent_transactions($_SESSION['cid']);
                                
                                
                                echo "<optgroup label='Frequent/Social'>";
                                
                                $traders = array();
                                
                                if (sizeof($frequent_traders_data) > 0)
                                {
                                    foreach ($frequent_traders_data as $item) 
                                    {
                                        echo "<option data-subtext='$item[1]' value='$item[1]'>$item[2]</option>";
                                        array_push($traders, $item[1]);
                                    }
                                }
                                
                                if (sizeof($linkedin_data) > 0)
                                {
                                    foreach ($linkedin_data as $item) 
                                    {
                                        if (in_array( $item[1], $traders ))
                                        {
                                        }
                                        else
                                        {
                                            echo "<option class='.pull-right' data-subtext='$item[1]' value='$item[1]'>$item[2]</option>";
                                            array_push($traders, $item[1]);
                                        }
                                    }
                                }
                            
                                echo "<optgroup label='BARTER members'>";
                                
                                if (sizeof($data) > 0)
                                {
                                    foreach ($data as $item) 
                                    {
                                        if (in_array( $item[1], $traders ) )
                                        {
                                        }
                                        else
                                        {
                                            echo "<option data-subtext='$item[1]' value='$item[1]'>$item[2]</option>";
                                        }
                                    }
                                }
                                ?>
                           </select> 
        			 </div>
              </div> 
                      
               <div class="field-box col-lg-10 controls" id="non_barter_input" hidden="true">
                   
                    <div class="input-group col-lg-8">
                     <label id="consumer_name" for="consumder_id">Name / Email:</label>
                        <input id="customer_id1" name="customer_id1"  type="text" placeholder="" class="form-control input-large">
                    </div>
                   
                </div>
         	
                 <div class="field-box col-lg-10 controls" id="points">
                 
                  
                  <div class="col-lg-10 controls">
                  <label>Points Awarded: <a class="hover_span">help
                    <div class="hover_content">
                    	<p class="notice_small" >If you woud like to reward your customer with some points you can specify upto 5 points per transaction. These can be used as you wish (for example: for loyalty or rating)</p>
                    </div></a></label>
                    <div class="star-rating"><s id="star1"><s id="star2"><s id="star3"><s id="star4"><s id="star5"></s></s></s></s></s></div> 
                  </div>
                </div>
              
                 <div class="field-box col-lg-10 controls">
                 
                      <div class="input-group col-lg-3" style="width:200px !important;">
                     
                      <span class="input-group-addon"><b>&pound;</b></span>
                         <input id="price" name="price" type="text" class="form-control input-large" required>
                      </div>
                 </div>
          
                 <div class="field-box col-lg-10 controls">
                 <label id="trans_type_title">Transaction Type:</label>
                      <div class="controls controls-row"> <div class="col-lg-10 controls">
                      
                       <button id="goods_icon" type="button" data-toggle="button">
                        <?php
                            $userObj = get_user($_SESSION['cid']);
                            if ($userObj[9] == '1' || $userObj[10] == '1' || $userObj[11] == '1'){
                            ?>
                        <img id="goods_icon_off" src="../images/goods_icon_off.png" width="80" hidden="true"/>
                            <img id="goods_icon_on" src="../images/goods_icon_on.png" width="80"  />
                          <?php }else{?>
                          <img id="goods_icon_off" src="../images/goods_icon_off.png" width="80" />
                            <img id="goods_icon_on" src="../images/goods_icon_on.png" width="80"  hidden="true"/>
                          <?php }?>
                          <p>Goods</p>
                        </button> 
                        
                        <button id="service_icon" type="button" data-toggle="button" style="margin:0 0 0 20px;">
                        <?php
                            if ($userObj[12] == '1'){
                            ?>
                            <img id="service_icon_off" src="../images/service_trans_icon_off.png" width="80" hidden="true"/>
                            <img id="service_icon_on" src="../images/service_trans_icon_on.png" width="80"  />
                          <?php }else{?>
                          <img id="service_icon_off" src="../images/service_trans_icon_off.png" width="80" />
                            <img id="service_icon_on" src="../images/service_trans_icon_on.png" width="80"  hidden="true"/>
                            <?php }?>
                          <p>Service</p>
                        </button>
                      </div></div>
                 </div>
                 
                  
                    <div class="controls controls-row" style="clear:both !important">
                        <input type="hidden" name="trader_id" id="trader_id" value="<?=$_SESSION['cid']?>" />
                        <input type="hidden" name="lat" id="lat" value="<?=$_SESSION['lat']?>" />
                        <input type="hidden" name="lon" id="lon" value="<?=$_SESSION['lon']?>" />
                        <button type="submit" class="btn btn-success  btn-lg btn-block">Record Sale</button>
                    </div>
                    
                    </form>
                    </div>
                </div>
                
                
               <div class="col-xs-6 col-md-4" >
                
                <div id="content_progress">
                	<div id="thermometer"> 
   		             <h3 id="community_spend" class="heading_grey" align="center"></h3>
       		         <h4 align="center">BARTER Community</h4>
           		     <div class="track">
                			<div class="goal">
                        	<!--<div class="amount"></div>-->
                       	</div>
                        	<div class="progress">
                			</div>
                		</div>
                	<div class="amount">0.00%</div>
                    <br />
                    <h4 align="center">Your Impact</h4>
                    <h2 id="my_spend" class="heading_grey" align="center"></h2>
                    <h4 align="center"><a href="money_overview_visualisations.php">see more</a></h4>
                    </div>
                	</div>
			</div>
    </div>
    <!-- end main container -->
</body>
</html>