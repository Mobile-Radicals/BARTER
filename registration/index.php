<?php
require '../config/con.php';
require '../config/utils.php';
?>
<!DOCTYPE html>
<html>
  <head>
    <title>BARTER - Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../config/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="../config/style.css" rel="stylesheet">
    <link href="../config/css/datepicker.css" rel="stylesheet">
    <link href="../config/css/bootstrap-glyphicons.css" rel="stylesheet">  
    <link rel="icon" type="image/png" href="../images/fav.png">
	<script src="http://code.jquery.com/jquery.js"></script>
    <script src="../config/js/bootstrap.min.js"></script>
    <script src="../config/js/jqBootstrapValidation.js"></script>
    <script src="../config/js/bootstrap-datepicker.js"></script>

		<script>
		if (top.location != location) 
		{
			top.location.href = document.location.href;
		}
		
		$(function(){
			window.prettyPrint && prettyPrint();
			var yearOfBirthPicker = $('#dpYears').datepicker().on('changeDate', function(ev) {$('.datepicker').hide();});
		});
	</script>
    
	<script>
		$(function(){
			    
			$('#trader_details').hide();
			$('#customer_details').show();
			$('#account_name').text('Please enter your full name');
			$('#name').attr('placeholder',"full name");
			$('#account_business_name').prop("required", false);
			$('#b_postcode').prop("required", false);
						
			$("[name='account']").click(function(){
				if ($(this).attr("value") == "customer")
				{
					$('#trader_details').hide();
					$('#customer_details').show();
					$('#account_name').text('Please enter your full name');
					$('#name').attr('placeholder',"full name");
					$('#account_business_name').prop("required", false);
					$('#b_postcode').prop("required", false);
				} 
				else if (($(this).attr("value") == "business") || ($(this).attr("value") == "organisation"))
				{
					$('#trader_details').show();
					$('#customer_details').hide();
					$('#account_business_name').prop("required", true);
					$('#b_postcode').prop("required", true);
				}
			});
			
		 	$("input,select,textarea").not("[type=submit]").jqBootstrapValidation({
				
				preventSubmit: true,
					submitError: function($form, event, errors) {
						
						console.log("submitError");
						alert("There seems to be an error submitting your details, please check you have filled in all the required fields.");
					},
					
				submitSuccess: function ($form, event) {
					
					console.log("submitSuccess");
					var service;
					var manufacturer;
					var retailer;
					var type_fixed;
					var type_normadic;
					var wholesaler;
					/*var environmental;
					var social;
					var wellbeing;
					var economic;*/
					
					if ($('#service').is(':checked')) {
						service = "&service=1";
					} else {
						service = "&service=0";
					}
					
					if ($('#manufacturer').is(':checked')) {
						manufacturer = "&manufacturer=1";
					} else {
						manufacturer = "&manufacturer=0";
					}
					
					if ($('#wholesaler').is(':checked')) {
						wholesaler = "&wholesaler=1";
					} else {
						wholesaler = "&wholesaler=0";
					}
					
					if ($('#retailer').is(':checked')) {
						retailer = "&retailer=1";
					} else {
						retailer = "&retailer=0";
					}
					
					if ($('#fixed').is(':checked')) {
						type_fixed = "&fixed=1";
					} else {
						type_fixed = "&fixed=0";
					}
					if ($('#normadic').is(':checked')) {
						type_normadic = "&normadic=1";
					} else {
						type_normadic = "&normadic=0";
					}
					
					/*if ($('#environmental').is(':checked')) {
						environmental = "&environmental=environmental";
					} else {
						environmental = "&environmental=";
					}
					
					if ($('#social').is(':checked')) {
						social = "&social=social";
					} else {
						social = "&social=";
					}
					
					if ($('#economic').is(':checked')) {
						economic = "&economic=economic";
					} else {
						economic = "&economic=";
					}
					
					if ($('#wellbeing').is(':checked')) {
						wellbeing = "&wellbeing=wellbeing";
					} else {
						wellbeing = "&wellbeing=";
					}*/
					
					
					var dob = "&dob="+$('#dob').val();

					var form = $('#add_user_form').find('input, textarea, select')
                       .not(':checkbox')
                       .serialize()

					
					//form += environmental + social + wellbeing + economic + service + manufacturer + wholesaler+ retailer + type_fixed + type_normadic + dob;
					
					form += service + manufacturer + wholesaler+ retailer + type_fixed + type_normadic + dob;
					
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
							//alert(received_data.message);
							window.location = "http://barterproject.org";
						}
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
 
  </head>
  <body>
    <div id="header">
    <?php require_once('../config/header.php');?>
    </div>
    
     <div class="container">

	  <h2>Registration</h2>
      <p class="lead">Registration details for both traders and consumers</p>

<iframe width="560" height="315" src="//www.youtube.com/embed/6cECJY7bUzg" frameborder="0" allowfullscreen></iframe>
     
      <div class="row">
        <div class="col-12 col-sm-8 col-lg-8">

            <form class="form-horizontal"  id="add_user_form" name="add_user_form" action="php/add_user.php" method="post">
            <fieldset>
            
            <!-- Form Name -->
            <legend>Account Details</legend>
  
            <!-- Text input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="card_id">Card ID:</label>
              <div class="col-lg-10">
              		<label for="card_id">Enter the digits shown on your BARTER card <a class="hover_span">help
                    <div class="hover_content"><p>You need to type in here all the digits of your card number with no spaces.</p>
                    <img src='http://barterproject.org/images/card_example.png' alt='logo' class="card_exmaple"/></div></a>
                		<input id="card_id" name="card_id" style="text-transform:uppercase" type="text" placeholder="0462478AF52680" class="form-control" required data-validation-ajax-ajax="php/check_rfid.php" maxlength="20">
                	</label>
                <p class="help-block"></p>
              </div>
            </div>
            
              <!-- Multiple Radios -->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="account">Account Type:</label>
              <div class="col-lg-10">
               <label class="radio" for="account-0">
                  <input type="radio" name="account" id="account-0" value="customer" checked="checked"> 
                  Customer
                </label>
                <label class="radio" for="account-1">
                  <input type="radio" name="account" id="account-1" value="business">
                  Business
                </label>
                <label class="radio" for="account-2">
                  <input type="radio" name="account" id="account-2" value="organisation">
                Organisation (Not For Profit)
                </label>
              </div>
            </div>
            
            <!-- Text input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="name">Your Name:</label>
              <div class="col-lg-10 controls">
              		 <label for="account_name"><h5 id="account_name">Please enter your full name</h5>
                		<input id="account_name" name="account_name" type="text" placeholder="full name" class="form-control" required>
                   </label>
                <p class="help-block"></p>
              </div>
            </div>
 
           
            <!-- Text input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="email">Email Address:</label>
              <div class="col-lg-10 controls">
                   <label  for="email"><h5>We will send you an email in order for you to verify your account</h5>
                        <input id="email" name="email" type="email" placeholder="email address" class="form-control" required>
                    </label>
                    <p class="help-block"></p>
              </div>
            </div>
            

 <!-- Text input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="postcode">Postcode: <br /></label>
              <div class="col-lg-10 controls">
                    <table>
                    <tr>
                    <td> <label for="postcode"><input id="postcode" name="postcode"  type="text" placeholder="LA1 4XX" class="form-control" maxlength="8" required 
                    data-validation-regex-regex="[A-Za-z][A-Za-z][0-9R][0-9A-Za-z ][ ]?[0-9][A-Za-z][A-Za-z]"
                    data-validation-regex-message="Please enter a valid UK postcode"  size="8" style="text-transform:uppercase">
                    </label></td>
                    <!--<td><label for="postcode1"><input id="postcode1" name="postcode1"  type="text" placeholder="4XX" class="form-control" maxlength="3" required 
                    data-validation-regex-regex="[0-9][A-Z||a-z][A-Z||a-z]"
                    data-validation-regex-message="Please enter a valid UK postcode"  size="3"></td>
                      </label>--></tr>
                      
                    </table>
                  

                <!-- 
                
                onKeyDown="this.value=this.value.toUpperCase()"
                
                data-validation-regex-regex="^([A-PR-UWYZ](([0-9](([0-9]|[A-HJKSTUW])?)?)|([A-HK-Y][0-9]([0-9]|[ABEHMNPRVWXY])?)) [0-9][ABD-HJLNP-UW-Z]{2})|GIR 0AA$" -->
                <p class="help-block"></p>
              </div>
            </div>
            
            
             <div class="form-group control-group">
         <label class="control-label col-lg-2" for="datepicker">Year of Birth: <br /><a class="hover_span">why?
                    <div class="hover_content"><p>We need to capture your year of birth, as we need to ensure the system works for varying age demographics</p></div></a></label>
			  <!--<div class="input-group date col-lg-10 controls" name="datepicker" id="dpYears" data-date=" 1960"
              data-date-format=" yyyy" data-date-viewmode="2" data-date-minviewmode="2" style="width:200px;">
              
				<input id="dob" class="span2 form-control" type="text" value="1960" readonly>
               <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			  </div>-->
              <div class="col-lg-10 controls">
                <label  for="dob">
                    <select id="dob" name="dob" class="form-control">
                    <option value="2005">2005</option>
                    <option value="2004">2004</option>
                    <option value="2003">2003</option>
                    <option value="2002">2002</option>
                    <option value="2001">2001</option>
                    <option value="2000">2000</option>
                    <option value="1999">1999</option>
                    <option value="1998">1998</option>
                    <option value="1997">1997</option>
                    <option value="1996">1996</option>
                    <option value="1995">1995</option>
                    <option value="1994">1994</option>
                    <option value="1993">1993</option>
                    <option value="1992">1992</option>
                    <option value="1991">1991</option>
                    <option value="1990">1990</option>
                    <option value="1989">1989</option>
                    <option value="1988">1988</option>
                    <option value="1987">1987</option>
                    <option value="1986">1986</option>
                    <option value="1985">1985</option>
                    <option value="1984">1984</option>
                    <option value="1983">1983</option>
                    <option value="1982">1982</option>
                    <option value="1981">1981</option>
                    <option value="1980">1980</option>
                    <option value="1979">1979</option>
                    <option value="1978">1978</option>
                    <option value="1977">1977</option>
                    <option value="1976">1976</option>
                    <option value="1975">1975</option>
                    <option value="1974">1974</option>
                    <option value="1973">1973</option>
                    <option value="1972">1972</option>
                    <option value="1971">1971</option>
                    <option value="1970">1970</option>
                    <option value="1969">1969</option>
                    <option value="1968">1968</option>
                    <option value="1967">1967</option>
                    <option value="1966">1966</option>
                    <option value="1965">1965</option>
                    <option value="1964">1964</option>
                    <option value="1963">1963</option>
                    <option value="1962">1962</option>
                    <option value="1961">1961</option>
                    <option value="1960" selected>1960</option>
                    <option value="1959">1959</option>
                    <option value="1958">1958</option>
                    <option value="1957">1957</option>
                    <option value="1956">1956</option>
                    <option value="1955">1955</option>
                    <option value="1954">1954</option>
                    <option value="1953">1953</option>
                    <option value="1952">1952</option>
                    <option value="1951">1951</option>
                    <option value="1950">1950</option>
                    <option value="1949">1949</option>
                    <option value="1948">1948</option>
                    <option value="1947">1947</option>
                    <option value="1946">1946</option>
                    <option value="1945">1945</option>
                    <option value="1944">1944</option>
                    <option value="1943">1943</option>
                    <option value="1942">1942</option>
                    <option value="1941">1941</option>
                    <option value="1940">1940</option>
                    <option value="1939">1939</option>
                    <option value="1938">1938</option>
                    <option value="1937">1937</option>
                    <option value="1936">1936</option>
                    <option value="1935">1935</option>
                    <option value="1934">1934</option>
                    <option value="1933">1933</option>
                    <option value="1932">1932</option>
                    <option value="1931">1931</option>
                    <option value="1930">1930</option>
                    <option value="1929">1929</option>
                    <option value="1928">1928</option>
                    <option value="1927">1927</option>
                    <option value="1926">1926</option>
                    <option value="1925">1925</option>
                    <option value="1924">1924</option>
                    <option value="1923">1923</option>
                    <option value="1922">1922</option>
                    <option value="1921">1921</option>
                    <option value="1920">1920</option>
                    <option value="1919">1919</option>
                    <option value="1918">1918</option>
                    <option value="1917">1917</option>
                    <option value="1916">1916</option>
                    <option value="1915">1915</option>
                    <option value="1914">1914</option>
                    <option value="1913">1913</option>
                    <option value="1912">1912</option>
                    <option value="1911">1911</option>
                    <option value="1910">1910</option>
                    <option value="1909">1909</option>
                    <option value="1908">1908</option>
                    <option value="1907">1907</option>
                    <option value="1906">1906</option>
                    <option value="1905">1905</option>
                    <option value="1904">1904</option>
                    <option value="1903">1903</option>
                    <option value="1902">1902</option>
                    <option value="1901">1901</option>
                    <option value="1900">1900</option>
                   </select>
                </label>
             </div>
         </div>
	
            <!-- Multiple Radios (inline) -->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="gender">Gender: <br /><a class="hover_span">why?
                    <div class="hover_content"><p>We need to capture your gender, for research purposes ONLY</p></div></a></label>
         	  <div class="col-lg-10 controls">
               <label  for="gender">
                <select id="gender" name="gender" class="form-control">
                  <option value="M">Male</option>
                  <option value="F">Female</option>
                  <option value="0">Rather not say</option>
                </select>
                </label>
              </div>
            </div>
            
           
            
           
            
            
            <!-- Select Basic -->
           <!-- <div class="form-group control-group">
              <label class="control-label col-lg-2" for="ethical_pref">Ethical Preference:<Br /><a class="hover_span">what?
                    <div class="hover_content"><p>We will ultimately be using this preference to support local initiatives in these sectors.</p></div></a></label>
              <div class="col-lg-10 controls">
              
               <!-- <select id="ethical_pref" name="ethical_pref" class="form-control">
                  <option value="education">Education</option>
                  <option value="building_communities">Building Communities</option>
                  <option value="protecting_nature_wildlife">Protecting Nature &amp; Wildlife</option>
                  <option value="environmental_impact">Environmental Impact</option>
                  <option value="community_ownership">Community Ownership</option>
                  <option value="economy">Economy</option>
                </select>-->
                
                 <!--<label class="checkbox" for="environmental">
             			<input type="checkbox" name="ethical_pref[]" id="environmental" value="environmental" minchecked="1" data-validation-minchecked-message="You must select at least one ethical preference.">
                      Environmental Stewardship
                    </label><br />
                    
                     <label class="checkbox" for="social">
             			<input type="checkbox" name="ethical_pref[]" id="social" value="social" >
                      Social Justice
                    </label><br />
                    
                     <label class="checkbox" for="economic">
             			<input type="checkbox" name="ethical_pref[]" id="economic" value="economic" >
                      Economic Concerns
                    </label><br />
                    
                     <label class="checkbox" for="wellbeing">
             			<input type="checkbox" name="ethical_pref[]" id="wellbeing" value="wellbeing" >
                      Wellbeing
                    </label><br />
                    
                    <p class="help-block"></p>
                    
                    
              </div>
            </div>-->
            <div id="customer_details">
            	   <div class="form-group control-group">
          <label class="control-label col-lg-2" for="employment_status"> </label>
                  <div class="col-lg-10 controls">
                <label  for="employment_status">Employment Status
                     <select id="employment_status" name="employment_status" class="form-control">
                        <option value="unknown">Rather not say</option>
                        <option value="employed">Employed</option>
                        <option value="self_employed">Self Employed</option>
                        <option value="student">Student</option>
                        <option value="unemployed">Unemployed</option>
                    </select>
                  </label>
                 </div>
                 </div>
                 
                    <div class="form-group control-group">
         <label class="control-label col-lg-2" for="barter_card"> </label>
                  <div class="col-lg-10 controls">
                <label  for="barter_card">Where did you get your BARTER card?
                     <select id="barter_card" name="barter_card" class="form-control">
                     <option value="other">Other</option>
                     <?php $data = get_bartercard_businesses();

							print_r($data);
							foreach ($data as $item) 
							{
								echo "<option value='".$item[0]."'>".$item[0]."</option>";
							}
							
							?>
                    </select>
                  </label>
                 </div>
                 </div>
            </div>
          
            <!--<div id="trader_details" style="display:none">-->
            <div id="trader_details">
                   <!-- Text input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="name">Business Name:</label>
              <div class="col-lg-10 controls">
              		 <label for="account_business_name">
                		<input id="account_business_name" name="account_business_name" type="text" placeholder="" class="form-control"  >
                   </label>
                <p class="help-block"></p>
              </div>
            </div>
            
            
             <!-- Text input-->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="postcode">Business Postcode: <br /></label>
              <div class="col-lg-10 controls">
                    <table>
                    <tr>
                    <td> <label for="b_postcode">
                    <input id="b_postcode" name="b_postcode"  type="text" placeholder="LA1 4ZZ" class="form-control" maxlength="8" required 
                    data-validation-regex-regex="[A-Za-z][A-Za-z][0-9R][0-9A-Za-z ][ ]?[0-9][A-Za-z][A-Za-z]"
                    data-validation-regex-message="Please enter a valid UK postcode"  size="8" style="text-transform:uppercase" >
                    </label></td>
                    <!--<td><label for="b_postcode1"><input id="b_postcode1" name="b_postcode1"  type="text" placeholder="4XX" class="form-control" maxlength="3" required 
                    data-validation-regex-regex="[0-9][A-Z||a-z][A-Z||a-z]"
                    data-validation-regex-message="Please enter a valid UK postcode"  size="3"></td>
                      </label>--></tr>
                      
                    </table>
                  

                <!-- 
                
                onKeyDown="this.value=this.value.toUpperCase()"
                
                data-validation-regex-regex="^([A-PR-UWYZ](([0-9](([0-9]|[A-HJKSTUW])?)?)|([A-HK-Y][0-9]([0-9]|[ABEHMNPRVWXY])?)) [0-9][ABD-HJLNP-UW-Z]{2})|GIR 0AA$" -->
                <p class="help-block"></p>
              </div>
            </div>
            
                <!-- Multiple Checkboxes -->
                <div class="form-group control-group">
                  <label class="control-label col-lg-2" for="trader_type">Type of Business:</label>
                  <div class="col-lg-10 controls">
                   Select the options that best describe your business
                    <label class="checkbox" for="manufacturer">
                     <img src="../images/manufacturer_icon_on.png" width="50px" style="float:left; margin: 0 20px 0 0;"/>
                      <br /><input type="checkbox" name="trader_type[]" id="manufacturer" value="manufacturer" minchecked="1" data-validation-minchecked-message="You must select at least one type of business.">
                      Manufacturing<br />
                    </label><br />
                     <label class="checkbox" for="wholesaler">
                     <img src="../images/wholesaler_icon_on.png" width="50px" style="float:left; margin: 0 20px 0 0;"/>
                       <br /><input type="checkbox" name="trader_type[]" id="wholesaler" value="wholesaler">
                      Wholesaling<br />
                    </label><br />
                    <label class="checkbox" for="retailer">
                     <img src="../images/retail_icon_on.png" width="50px" style="float:left; margin: 0 20px 0 0;"/>
                       <br /><input type="checkbox" name="trader_type[]" id="retailer" value="retailer">
                      Retailing<br />
                    </label><br />
                    <label class="checkbox" for="service">
                     <img src="../images/service_icon_on.png" width="50px" style="float:left; margin: 0 20px 0 0;"/>
                       <br /><input type="checkbox" name="trader_type[]" id="service" value="service" >
                      Service<br />
                    </label>
                    <p class="help-block"></p>
                  </div>
                </div>
                
                <!-- Multiple Checkboxes -->
                <div class="form-group control-group">
                  <label class="control-label col-lg-2" for="transaction_types">Style of Business:</label>
                  <div class="col-lg-10 controls">
                   Select the options that best describes how you perform transactions
                    <label class="checkbox" for="fixed">
                    <img src="../images/fixed_icon_on copy.png" width="50px" style="float:left; margin: 0 20px 0 0;"/>
                       <br /><input type="checkbox" name="transaction_types[]" id="fixed"  value="fixed" minchecked="1" data-validation-minchecked-message="You must select at least one stlye of trading.">
                      In a fixed location<br />(business premises, market stall etc)
                    </label><br />
                    <label class="checkbox" for="normadic" style="margin:10px 0 0 0;">
                     <img src="../images/location_icon_on.png" width="50px" style="clear:both; float:left; margin: 0 20px 0 0;"/>
                       <br /><input type="checkbox" name="transaction_types[]"  id="normadic" value="normadic">
                      In varying locations<br />(at customers premises) 
                    </label>
                    <p class="help-block"></p>
                  </div>
                </div>
                

               
                 <!-- Textarea -->
                <div class="form-group control-group">
                  <label class="control-label col-lg-2" for="goods_services">What do you do? <br/> <a class="hover_span">why?
                    <div class="hover_content"><p>Tell your customers what you do. It will make it easier for them to search for you.</p></div></a></label>
                  <div class="col-lg-10 controls">           
                  <label  for="goods_services">
                          Itemise the goods and services you offer
                    <textarea id="goods_services" name="goods_services" placeholder="Marketing Design, Business Consultation" style="resize:none" class="form-control"></textarea>
                        <p>separate each item by a comma(,) remember make this as easy to search as possible  </p>
                    </label>
                  </div>
                </div>
                
                 <!-- Textarea -->
                <div class="form-group control-group">
                  <label class="control-label col-lg-2" for="statement">Statement: <br /><a class="hover_span">why?
                    <div class="hover_content"><p>You can write anything you would like here. <br />See it as an area to write a personal message. <br />Why should the customers support your business etc..</p></div></a></label>
                  <div class="col-lg-10">        
                  <label  for="statement" >
                       Let your consumers know a little more about you / your business 
                     
                    <textarea id="statement" name="statement" style="resize:none"  class="form-control" ></textarea>
                       
                    </label>
                  </div>
                </div>
                                
            </div>
            
            <!-- <div class="form-group control-group">
                  <label class="control-label col-lg-2" for="movie">Favourite Character:<br /><a class="hover_span">why?
                    <div class="hover_content"><p>Because its funny! why else</p></div></a>
   </label>
                  <div class="col-lg-10">        
                  <label  for="movie" >
                                          
                     <select id="movie" name="movie" class="form-control">
                          <option value="homer_simpson">Homer Simpson</option>
                          <option value="darth_vader">Darth Vader</option>
                          <option value="indiana_jones">Indiana Jones</option>
                          <option value="jack_sparrow">Jack Sparrow</option>
                          <option value="james_bond">James Bond</option>
                          <option value="john_mcclane">John McClane</option>
                          <option value="the_terminator">The Terminator</option>
                          <option value="forrest_gump">Forrest Gump</option>
                          <option value="yoda">Yoda</option>
                          <option value="baby">Baby</option>
                          <option value="vivian_ward">Vivian Ward</option>
                          <option value="sarah_connor">Sarah Connor</option>
                          <option value="princess_leia">Princess Leia</option>
                          <option value="dorothy_gale">Dorothy Gale</option>
                          <option value="jessica_rabbit">Jessica Rabbit</option>
                          <option value="mary_poppins">Mary Poppins</option>
                          <option value="catherine_tramell">Catherine Tramell</option>
                          <option value="martin_riggs">Martin Riggs</option>
                          <option value="buzz_lightyear">Buzz Lightyear</option>
                          <option value="axel_foley">Axel Foley</option>
                        	 <option value="et">E.T.</option>
                          <option value="sheldon_cooper">Sheldon Cooper</option>
                          <option value="leonard_hofstadter">Leonard Hofstadter</option>
                          <option value="penny">Penny</option>
                          <option value="amy_farrah_fowler">Amy Farrah Fowler</option>
                </select>
                       
                    </label>
                  </div>
                </div>-->
          
             <!-- Multiple Radios -->
            <div class="form-group control-group">
              <label class="control-label col-lg-2" for="ethical_pref">Ethical Preference:<Br /><a class="hover_span">what?
                    <div class="hover_content"><p>We will ultimately be using this preference to support local initiatives in these sectors.</p></div></a></label>
              <div class="col-lg-10">
                <label class="radio" for="ethical_pref-0">
                  <input type="radio" name="ethical_pref_type" id="ethical_pref-0" value="environmental" checked="checked">
                  Environmental Stewardship
                </label>
                <label class="radio" for="ethical_pref-1">
                  <input type="radio" name="ethical_pref_type" id="ethical_pref-1" value="social">
                   Social Justice
                </label>
                <label class="radio" for="ethical_pref-2">
                  <input type="radio" name="ethical_pref_type" id="ethical_pref-2" value="economic">
                Economic Concerns
                </label>
                <label class="radio" for="ethical_pref-3">
                  <input type="radio" name="ethical_pref_type" id="ethical_pref-3" value="wellbeing">
                Wellbeing
                </label>
                <p class="help-block"></p>
                <a href="http://sustainablebusinessforum.com/miketyrrell/57395/quintuple-bottom-line" target="_blank">more info</a>
              </div>
            </div>
            
           <div class="form-group control-group">
                  <label class="control-label col-lg-2" for="terms-and-conditions">Legal</label>
                  <div class="col-lg-10 controls">
                      <label for="terms-and-conditions">
                      	<input type="checkbox"  name="terms-and-conditions" required data-validation-required-message="You must agree to the terms and conditions">
                     	I agree to the <a href="http://barterproject.org/tc/" target="_blank">terms and conditions</a>
                     </label>
                    <p class="help-block"></p>
                  </div>
                </div>
                <div class="controls controls-row">
            		<button type="submit" class="btn btn-success  btn-lg btn-block">Register</button>
            		</div>
            </fieldset>
            </form>
            <br />
		</div>
        
      	<div class="col-6 col-sm-4 col-lg-4">
        	<?php require_once('../config/am_i_local.php');?>
          <?php require_once('../config/needacard_sidebar.php');?>
           
            
        </div>
      </div>
     

    </div> <!-- /container -->
  </body>
</html>