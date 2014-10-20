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
            <h3>Profile</h3>
            <p>As BARTER is about encouraging local spend, you can add any employee to your business to accululate their total spend to your BARTER account (this also includes yourself as a customer to other businesses).</p>
            
            <div class="col-xs-12 col-md-8"> 
    
                <form class="form-horizontal"  style="float:left !important" id="add_assoc_form" name="add_assoc_form" action="php/add_associations.php" method="post">
                <fieldset>
                
                <!-- Form Name -->
                <legend>Link Cards</legend>
      
                <!-- Text input-->
                <div class="form-group control-group">
                  <div class="col-lg-10">
                        <label for="card_id">Please enter the card IDs you would like to associate with your business.<br />For multiple users seperate by a comma (,) <a class="hover_span"> why
                        <div class="hover_content"><p>You can associate your personal card ID and any employeess cards with your business. This association helps boost your business profile of spending local.</p></div></a>
                            
                            <textarea id="associations" name="ids" cols="80" rows="4" placeholder="card ID,card ID" class="form-control"></textarea>
                            <input type="hidden" name="trader_id" id="trader_id" value="<?=$_SESSION['cid']?>" />
                            <br />
                            <button type="submit" class="btn btn-success  btn-lg btn-block">Link Cards</button>
                        </label>
                    <p class="help-block"></p>
                  </div>
                </div>
                </fieldset>
                </form>
                <br />
            </div>
                
                </fieldset>
                </form>
           </div>
            <div class="col-xs-6 col-md-4">
                   <div id="association_list" style="float:left;"><?=get_associations($_SESSION['cid']);?></div>         
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
		$(function(){
				function LinkedinSync()
				{
					var syncWindow = window.open('linkedin/get_linkedin_token.php', '', 'width=400,height=400,location=0,menubar=0,resizable=1,scrollbars=1,status=0,titlebar=0,toolbar=0');
				  	
					$(syncWindow).unload(function() {
					 window.location.reload(true);
					});
 					
					 
					 
					 return false;
				}
			  
			   $("#syncwithlinkedin_expired_token").click(LinkedinSync);
			   $("#syncwithlinkedin_no_token").click(LinkedinSync);
			   
			   
			   $("input,select,textarea,button").not("[type=submit]").jqBootstrapValidation({
			
			preventSubmit: true,
				submitError: function($form, event, errors) {
					
					console.log("submitError");
					alert("There seems to be an error submitting your details, please check you have filled in all the required fields.");
				},
				
			submitSuccess: function ($form, event) {
				

				var form = $('#add_assoc_form').find('input, textarea, select, button')
				   .not(':checkbox')
				   .serialize()   
				
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
					alert(received_data.message);
					
					$('#association_list').html(received_data.assocs);
					$('#add_assoc_form')[0].reset();
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