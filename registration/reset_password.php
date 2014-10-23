<?php
//include the database class
require '../config/con.php';

//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT user_id, user_name, user_card_id, user_pass FROM tbl_users WHERE pass_key=:id LIMIT 1");
$stmt->bindParam(':id', $_REQUEST["opps"], PDO::PARAM_STR);
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
   
 
    


		
    
	<script>
		
		$(function(){
			   	
					
						    
		$("#submitBtn").on("click", function(e) {
			
			var form = $('#reset_password_form').serialize();
			console.log(form);
			 $.ajax({
				type: 'POST',
				url: $('#reset_password_form').attr('action'),
				data: form,
				success:function(data)
				{
				
					// just to confirm ajax submission
						console.log('submitted successfully!');
						var received_data = JSON.parse(data);
						//alert(received_data.message);
						
						if(received_data.response == true){
							alert(received_data.message);
							window.location = "http://barterproject.org/portal"
						}
						
				}
			});
			// will not trigger the default submission in favor of the ajax function
			event.preventDefault();
		});
			
		});
		
		
	
    </script>
 
       
  </head>
  <body>
 
  <div id="header">
    <?php require_once('../config/header.php');?>
    </div>
    <div class="container">
    <?php
	
	/*if ($password != '')
	{
		echo "<h1>Opps!</h1>";
		echo "<h2>It seems as though you have previously entered a password for this account, if this was not you or you have forgotton your password, please follow the procedure for resetting your <a href='reset_password.php'>password</a>.</h2>";
	}
	else
	{*/
	
	?>
     
     
      <div class="row">
        <div class="col-12 col-sm-8 col-lg-8">

            <form class="form-horizontal"  id="reset_password_form" name="verify_account" action="php/reset_password.php" method="post">
            <fieldset>
            
            <!-- Form Name -->
            <legend><h1>Forgot your password?</h1></legend>
  		 
<h3>We will send you out an email in order to reset your password, in the time being we have deactivated your account.</h3>
  <br />
            <!-- Text input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="card_id">Card ID:</label>
              <div class="col-lg-10">
              		<label for="card_id">Enter the digits shown on your BARTER card <a class="hover_span">help
                    <div class="hover_content"><p>You need to type in here all the digits of your card number with no spaces.</p>
                    <img src='http://barterproject.org/images/card_example.png' alt='logo' class="card_exmaple"/></div></a>
                		<input id="card_id" name="card_id" type="text" placeholder="for example: 0462478AF52680" class="form-control" required maxlength="20">
                	</label>
                <p class="help-block"></p>
              </div>
            </div>
            
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="email">Email Address:</label>
              <div class="col-lg-10 controls">
                   <label  for="email">
                        <input id="email" name="email" type="email" placeholder="email address" class="form-control" required>
                    </label>
                    <p class="help-block"></p>
              </div>
            </div>
            
          
                <div class="controls controls-row">
                <input type="hidden" name="user_id" value="<?php echo $id;?>">
                <input type="hidden" name="pass_key" value="<?php echo $_REQUEST["opps"];?>">
            		<button id="submitBtn" type="button" class="btn btn-default">Reset Password</button>
            		</div>
            </fieldset>
            </form>
            <br />
		</div>
        
 
      </div>
     <?php //}?>
    </div> <!-- /container -->
  </body>
</html>