<?php
//include the database class
require '../config/con.php';

//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT user_id, user_name, user_card_id, user_pass FROM tbl_users WHERE pass_key=:id LIMIT 1");
$stmt->bindParam(':id', $_REQUEST["id"], PDO::PARAM_STR);
$stmt->execute();
	
//setting the fetch mode  
$stmt->setFetchMode(PDO::FETCH_ASSOC); 
$row = $stmt->rowCount();

/* Bind by column name */
$stmt->bindColumn('user_id', $id);
$stmt->bindColumn('user_name', $name);
$stmt->bindColumn('user_card_id', $card_id);
$stmt->bindColumn('user_pass', $password);
$stmt->fetch(PDO::FETCH_BOUND);

if ($row != 1)
{
	echo '<script>window.location = "http://barterproject.org"</script>';
}
/*while ($row = $stmt->fetch(PDO::FETCH_BOUND)) {
      $data = $name . "\t" . $colour . "\t" . $cals . "\n";
      print $data;
}*/
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Verify Account - BARTER</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="../config/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="../config/style.css" rel="stylesheet">
     <link href="../config/css/datepicker.css" rel="stylesheet">
     <link href="../config/css/bootstrap-glyphicons.css" rel="stylesheet">  
     <link rel="icon" type="image/png" href="../images/fav.png">
    <!-- JavaScript plugins (requires jQuery) -->
    <script src="http://code.jquery.com/jquery.js"></script>
       <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../config/js/bootstrap.min.js"></script>
   
 
    <script src="../config/js/jqBootstrapValidation.js"></script>
    


		
    
	<script>
		
		$(function(){
			    
		
			//$('form[name="add_user_form"]').find('input,select,textarea').not('[type=submit]').jqBootstrapValidation({
		 	$("input,select,textarea").not("[type=submit]").jqBootstrapValidation({
				preventSubmit: true,
					submitError: function($form, event, errors) {
						// Here I do nothing, but you could do something like display 
						// the error messages to the user, log, etc.
						alert("error");
					},
					
				submitSuccess: function ($form, event) {
					
					
					
					var form = $form.serialize()
					/*var form = $('#verify_account').find('input, textarea, select')
                       .not(':checkbox')
                       .serialize()
					   
					*/
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
						
						if(received_data.response == true){
							//alert('do some redirect here!');
							window.location = received_data.redirect;
						}
						else
						{
							window.location = received_data.redirect;
						}
					}
				  });
				  
				 
				  event.preventDefault();
				},
				
				filter: function() {
						return $(this).is(":visible");
					}
					
			
			  });
  
			/*$("input,textarea,select").jqBootstrapValidation(
				{
					preventSubmit: true,
					submitError: function($form, event, errors) {
						// Here I do nothing, but you could do something like display 
						// the error messages to the user, log, etc.
					},
					submitSuccess: function($form, event) {
						alert("OK");
						
						event.preventDefault();
					},
					filter: function() {
						return $(this).is(":visible");
					}
				}
			);*/
			
		});
		
	
    </script>
 
       
  </head>
  <body>
 
  <div id="header">
    <?php require_once('../config/header.php');?>
    </div>
    <div class="container">
    <?php
	
	if ($password != '')
	{
		$opps = $_REQUEST['id'];
		echo "<h1>Opps!</h1>";
		echo "<h2>It seems as though you have previously entered a password for this account, if this was not you or you have forgotton your password, please follow the procedure for resetting your <a href='reset_password.php?opps=$opps'>password</a>.</h2>";
	}
	else
	{
	
	?>
     

      <h2>Verify Account</h2>
      <p class="lead">Before we can officially welcome you to the BARTER project, we need you to create a password</p>

     
      <div class="row">
        <div class="col-12 col-sm-8 col-lg-8">

            <form class="form-horizontal"  id="add_user_form" name="verify_account" action="php/verified_user.php" method="post">
            <fieldset>
            
            <!-- Form Name -->
            <legend>Hello, <?php echo $name; ?></legend>
  			<h4>You are creating an account for the card, <?php echo $card_id; ?></h4>
           
            
         
            
            <!-- Password input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="password">Password:</label>
              <div class="col-lg-10 controls">
               <label for="password1"><h5 id="account_name"></h5>
                <input id="password1" name="password1" type="password" placeholder="password" class="form-control" required pattern="^(?=.*\d)(?=.*[a-zA-Z]).{6,50}$" 
        data-validation-pattern-message="Your password must contain at least one numeric character and one alpha character with a minimum length of 6 characters...."></label>
                <p class="help-block"></p>
              </div>
            </div>
            
             <!-- Password input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="password2">Confirm Password:</label>
              <div class="col-lg-10 controls">
              <label for="password2"><h5 id="account_name"></h5>
                <input id="password2" name="password2" type="password" placeholder="confirm password" class="form-control" data-validation-matches-match="password1"  required></label>
                <p class="help-block"></p>
              </div>
            </div>
            
          
                <div class="controls controls-row">
                <input type="hidden" name="user_id" value="<?php echo $id;?>">
                <input type="hidden" name="pass_key" value="<?php echo $_REQUEST["id"];?>">
            		<button type="submit" class="btn btn-default">Update</button>
            		</div>
            </fieldset>
            </form>
            <br />
		</div>
        
      	<div class="col-6 col-sm-4 col-lg-4 white_bg">
        
          <?php //require_once('../config/needacard_sidebar.php');?>
           
            
        </div>
      </div>
     <?php }?>
    </div> <!-- /container -->
  </body>
</html>