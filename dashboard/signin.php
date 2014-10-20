<!DOCTYPE html>
<html class="login-bg">
<head>
	<title>Detail Admin - Sign in</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
    <!-- bootstrap -->
    <link href="css/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="css/bootstrap/bootstrap-overrides.css" type="text/css" rel="stylesheet" />

    <!-- global styles -->
    <link rel="stylesheet" type="text/css" href="css/compiled/layout.css" />
    <link rel="stylesheet" type="text/css" href="css/compiled/elements.css" />
    <link rel="stylesheet" type="text/css" href="css/compiled/icons.css" />
	
    <link href="../config/style.css" rel="stylesheet">
    
    <!-- libraries -->
    <link rel="stylesheet" type="text/css" href="css/lib/font-awesome.css" />
    
    <!-- this page specific styles -->
    <link rel="stylesheet" href="css/compiled/signin.css" type="text/css" media="screen" />

    <!-- open sans font -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css' />

    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>


    


    <div class="login-wrapper">
      <a href="http://barterproject.org/"><img src="http://barterproject.org/wp-content/uploads/2013/06/final_logo_full.png" alt="logo" id="logo" /></a><br /><br />

        <div class="box">
            <div class="content-wrap">
                <h6>Log in</h6>
                <form class="form-signin"  id="signin_form" name="add_user_form" action="php/login.php" method="post">
        			
                    <p>please enter your email address and password</p>
                    <input id="email" name="email" type="email" class="form-control" placeholder="Email address" autofocus required>
                    <input id="password" name="password" type="password" class="form-control" placeholder="Password" required>
                    <!--<label class="checkbox">
                      <input type="checkbox" value="remember-me"> Remember me
                    </label>-->
                    <button class="btn-glow primary login" type="submit">Sign in</button>
                  </form>
            </div>
        </div>

        <div class="no-account">
            <p>Don't have an account?</p>
            <a href="../registration/index.php">Sign up</a>
        </div>
    </div>

	<!-- scripts -->
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/theme.js"></script>

	<script src="../config/js/jqBootstrapValidation.js"></script>
    
 
  	<script>

		
		$(function(){
			
				$("input,select,textarea").not("[type=submit]").jqBootstrapValidation({
				preventSubmit: true,
					submitError: function($form, event, errors) {
						// Here I do nothing, but you could do something like display 
						// the error messages to the user, log, etc.
						alert("error");
					},
					
				submitSuccess: function ($form, event) {
					
			
					
					
					var form = $('#signin_form').find('input, textarea, select')
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
						//alert(received_data.message);

						if(received_data.response == true){
							
							if (received_data.message == "multiple-accounts")
							{
								$("#myModal").modal("show"); 
								//alert(received_data.message);
							}
							else
							{
								//alert(received_data.message);
								window.location = "http://barterproject.org/dashboard/dashboard.php";
							}
							
							//alert('do some redirect here!');
							//window.location = "http://barterproject.org/portal/portal.php";
						}
						else
						{
							alert("It seems as though you dont have permission to access the system at this point or your login credentials are incorrect");
						}
					}
				  });
				  // will not trigger the default submission in favor of the ajax function
				  event.preventDefault();
				},
				
				filter: function() {
						return $(this).is(":visible");
					}
					
			
			  });
  
			
		
			
			$('.ok-log-in').click(function() {
				//alert("jhere");
				window.location = "http://barterproject.org/dashboard/dashboard.php";
			});
		});
	
    </script>
 
  
</html>