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
    
  
        
          <div id="pad-wrapper">
          
          <h3>My Activity</h3>
<h4>Click to filter your transaction data</h4>
 	<form class="form-horizontal" id="transaction_activity" name="transaction_activity" action="php/activity_query.php" method="post">
            <fieldset>
          
             <div class="form-group control-group">
         
         		  <button id="latest_icon" type="button" data-toggle="button">
               	<img id="latest_off" src="../images/latest_icon_off.png" width="80" />
                	<img id="latest_on" src="../images/latest_icon_on.png" width="80" hidden="true" />
                  <p>RECENT</p>
                </button> 
                
                 <button id="web_icon" type="button" data-toggle="button">
               	<img id="web_off" src="../images/web_icon_off.png" width="80" />
                	<img id="web_on" src="../images/web_icon_on.png" width="80" hidden="true" />
                  <p>WEB</p>
                </button> 
                
                 <button id="mobile_icon" type="button" data-toggle="button">
               	<img id="mobile_off" src="../images/mobile_icon_off.png" width="80" />
                	<img id="mobile_on" src="../images/mobile_icon_on.png" width="80" hidden="true" />
                  <p>MOBILE</p>
                </button> 
			 
               <button id="goods_icon" type="button" data-toggle="button">
               	<img id="goods_icon_off" src="../images/goods_icon_off.png" width="80" />
                	<img id="goods_icon_on" src="../images/goods_icon_on.png" width="80" hidden="true" />
                  <p>Goods</p>
                </button> 
                
                 <button id="service_icon" type="button" data-toggle="button">
               	<img id="service_icon_off" src="../images/service_trans_icon_off.png" width="80" />
                	<img id="service_icon_on" src="../images/service_trans_icon_on.png" width="80"  hidden="true"/>
                  <p>Service</p>
                </button> 
                
                 <button id="goods_service_icon" type="button" data-toggle="button">
               	<img id="goods_service_icon_off" src="../images/goods_service_icon_off.png" width="80"/>
                	<img id="goods_service_icon_on" src="../images/goods_service_icon_on.png" width="80"  hidden="true"/>
                  <p>Both</p>
                </button> 
                
                 <!--<button id="barter_trader_icon" type="button" data-toggle="button">
               	<img id="barter_trader_icon_off" src="../images/barter_trader_full_icon_off.png" width="80" />
                	<img id="barter_trader_icon_on" src="../images/barter_trader_full_icon_on.png" width="80"  hidden="true"/>
                  <p>BARTER</p>
                </button>-->
                
                <button id="barter_trader_icon" type="button" data-toggle="button">
               	<img id="barter_trader_icon_off" src="../images/barter_off.png" width="80" />
                	<img id="barter_trader_icon_on" src="../images/barter_on.png" width="80"  hidden="true"/>
                  <p>USER</p>
                </button> 
                
                 <!--<button id="barter_customer_icon" type="button" data-toggle="button">
               	<img id="barter_customer_icon_off" src="../images/barter_customer_full_off.png" width="80" >
                	<img id="barter_customer_icon_on" src="../images/barter_customer_full_on.png" width="80"  hidden="true"/>
                  <p>BARTER</p>
                </button>--> 
                
                 <button id="local_non_barter_icon" type="button" data-toggle="button">
               	<img id="local_non_barter_icon_off" src="../images/local_non_barter_icon_off.png" width="80"/>
                	<img id="local_non_barter_icon_on" src="../images/local_non_barter_icon_on.png" width="80" hidden="true" />
                  <p>LOCAL</p>
                </button> 
                
                 <button id="non_local_icon" type="button" data-toggle="button">
               	<img id="non_local_icon_off" src="../images/non_local_icon_off.png" width="80" />
                	<img id="non_local_icon_on" src="../images/non_local_icon_on.png" width="80"  hidden="true"/>
                  <p>NON-LOCAL</p>
                </button>
               
             <br /><br />
               <button id="submit_query" type="submit" class="btn btn-success btn-lg btn-block">Search</button>
                
        
			 
         </div>
             </fieldset></form>
   <!-- <div class="col- col-sm-8 col-lg-8">-->
    <?php
    	$data = get_activity($_SESSION['cid'],"t.trans_id","DESC", 0, 30);
		
		
		//	print_r($data);
		?>
		<h3>Total Transactions <?=get_number_transactions($_SESSION['cid']);?></h3>
        
		<div id="activity">

       
        </div>
        

            
          </div>
               
    </div>
    <!-- end main container -->


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

	
    <script src="../config/js/jqBootstrapValidation.js"></script>
	<script src="../config/js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="../config/js/utils.js"></script>

	<script>
	
	$(function() {
		$("#activity").delegate('tr', 'click', function() {
			$(".extra_content").hide();
			$(this).closest("tr").next().show();
			});
			
			var recent=0,web=0,mobile=0,goods=0, service=0,goods_service=0,trader=0,customer=0,local=0,non_local=0;
			 
			$("#latest_icon").on("click", function(e) {
			if ($('#latest_on').is(":hidden")) {
				$('#latest_off').hide();	
				$('#latest_on').show();
				recent = 1;
			}else{
				$('#latest_off').show();	
				$('#latest_on').hide();
				recent = 0
			}});
			
			$("#web_icon").on("click", function(e) {
			if ($('#web_on').is(":hidden")) {
				$('#web_off').hide();	
				$('#web_on').show();
				web = 1;
			}else{
				$('#web_off').show();	
				$('#web_on').hide();
				web = 0;
			}});
			
			$("#mobile_icon").on("click", function(e) {
			if ($('#mobile_on').is(":hidden")) {
				$('#mobile_off').hide();	
				$('#mobile_on').show();
				mobile = 1;
			}else{
				mobile = 0;
				$('#mobile_off').show();	
				$('#mobile_on').hide();
			}});
			
			$("#goods_icon").on("click", function(e) {
			if ($('#goods_icon_on').is(":hidden")) {
				$('#goods_icon_off').hide();	
				$('#goods_icon_on').show();
				goods = 1;
				
				/*if ($('#goods_service_icon_off').is(":hidden")) {
					$('#goods_service_icon_off').show();	
					$('#goods_service_icon_on').hide();
					goods_service =0;
				}*/
				
				/*if ($('#service_icon_off').is(":hidden")) {
					$('#goods_icon_off').show();	
					$('#goods_icon_on').hide();
					goods=0;
					$('#service_icon_off').show();	
					$('#service_icon_on').hide();
					service = 0;
					$('#goods_service_icon_off').hide();	
					$('#goods_service_icon_on').show();
					goods_service = 1;
				}*/
				
			}else{
				$('#goods_icon_off').show();	
				$('#goods_icon_on').hide();
				goods=0;
			}});
			
			$("#service_icon").on("click", function(e) {
			if ($('#service_icon_on').is(":hidden")) {
				$('#service_icon_off').hide();	
				$('#service_icon_on').show();
				service = 1;
				
				/*if ($('#goods_service_icon_off').is(":hidden")) {
					$('#goods_service_icon_off').show();	
					$('#goods_service_icon_on').hide();
					goods_service =0;
				}*/
				
				/*if ($('#goods_icon_off').is(":hidden")) {
					$('#goods_icon_off').show();	
					$('#goods_icon_on').hide();
					goods=0;
					$('#service_icon_off').show();	
					$('#service_icon_on').hide();
					service = 0;
					$('#goods_service_icon_off').hide();	
					$('#goods_service_icon_on').show();
					goods_service = 1;
				}*/
					
			}else{
				$('#service_icon_off').show();	
				$('#service_icon_on').hide();
				service = 0;
			}});
			
			$("#goods_service_icon").on("click", function(e) {
			if ($('#goods_service_icon_on').is(":hidden")) {
				$('#goods_service_icon_off').hide();	
				$('#goods_service_icon_on').show();
				goods_service = 1;
				
				/*$('#service_icon_off').show();	
				$('#service_icon_on').hide();
				service = 0;
				
				$('#goods_icon_off').show();	
				$('#goods_icon_on').hide();
				goods=0;*/
			}else{
				$('#goods_service_icon_off').show();	
				$('#goods_service_icon_on').hide();
				goods_service =0;
			}});
			
			$("#barter_trader_icon").on("click", function(e) {
			if ($('#barter_trader_icon_on').is(":hidden")) {
				$('#barter_trader_icon_off').hide();	
				$('#barter_trader_icon_on').show();
				trader =1;
			}else{
				$('#barter_trader_icon_off').show();	
				$('#barter_trader_icon_on').hide();
				trader=0;
			}});
			
			$("#barter_customer_icon").on("click", function(e) {
			if ($('#barter_customer_icon_on').is(":hidden")) {
				$('#barter_customer_icon_off').hide();	
				$('#barter_customer_icon_on').show();
				customer = 1;
			}else{
				$('#barter_customer_icon_off').show();	
				$('#barter_customer_icon_on').hide();
				customer = 0;
			}});
			
			$("#local_non_barter_icon").on("click", function(e) {
			if ($('#local_non_barter_icon_on').is(":hidden")) {
				$('#local_non_barter_icon_off').hide();	
				$('#local_non_barter_icon_on').show();
				local = 1;
			}else{
				$('#local_non_barter_icon_off').show();	
				$('#local_non_barter_icon_on').hide();
				local = 0;
			}});
			
			$("#non_local_icon").on("click", function(e) {
			if ($('#non_local_icon_on').is(":hidden")) {
				$('#non_local_icon_off').hide();	
				$('#non_local_icon_on').show();
				non_local = 1;
			}else{
				$('#non_local_icon_off').show();	
				$('#non_local_icon_on').hide();
				non_local = 0;
			}});
			
			$("input,select,textarea,button").not("[type=submit]").jqBootstrapValidation({
			
			preventSubmit: true,
				submitError: function($form, event, errors) {
					console.log("submitError");
				},
				
			submitSuccess: function ($form, event) {
				
			var form = $('#transaction_activity').find('input, textarea, select, button')
				   .not(':checkbox')
				   .serialize()
				   	
			form += "&recent="+recent+"&web="+web+"&mobile="+mobile+"&goods="+goods+"&service="+service+"&goods_service="+goods_service+"&trader="+trader+"&customer="+customer+"&local="+local+"&non_local="+non_local;
				
			console.log(form);   
				
			  $.ajax({
				type: 'POST',
				url: $form.attr('action'),
				data: form,
				success: function(data)
				{ 
					// just to confirm ajax submission
					console.log('submitted successfully!');
					//var received_data = JSON.parse(data);
					//alert(received_data.message);
					$('#activity').html(data);
					//alert(data);
				}
			  });
			  
			  event.preventDefault();
			},
			
			filter: function() {
					return $(this).is(":visible");
				}
				
		
		  });
	});
	
	
	</script>
    

</body>
</html>