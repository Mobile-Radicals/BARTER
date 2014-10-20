<?php

if ($user_type == "Trader")
{
echo '<div id="header_img"><a href="http://barterproject.org/"><img src="http://barterproject.org/wp-content/uploads/2013/06/final_logo_full.png" alt="logo" id="logo" /></a></div>
<div class="header_container">

      <div class="navbar">
	 

        <div class="container">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-responsive-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        
		
          <div class="nav-collapse navbar-responsive-collapse collapse" style="height: 0px;">
		   <a class="navbar-brand" href="#"></a>
            <ul class="nav navbar-nav dropdown">
				<li><a href="portal.php">Home</a></li>
				<li><a href="overview.php">Overview</a></li>
				<li><a href="connections.php">Connections</a></li>
				<li><a href="transaction.php">Transaction</a></li>
				<li><a href="activity.php">Activity</a></li>
				';
				/*<li><a href="redeem.php">Redeem</a></li>
				<li><a href="profile.php">Profile</a></li>
				<li><a href="stats.php">Stats</a></li>*/
				echo '<li><a href="settings.php">Settings</a></li>
				<li><a href="updates.php">Updates</a></li>
				<li><a href="help.php">Help</a></li>
				<li><a href="logout.php">Log out</a></li>
            </ul>
            
		
    
          </div><!-- /.nav-collapse -->
        </div><!-- /.container -->
      </div><!-- /.navbar -->
    </div>';
}
else
{
	echo '<div id="header_img"><a href="http://barterproject.org/"><img src="http://barterproject.org/wp-content/uploads/2013/06/final_logo_full.png" alt="logo" id="logo" /></a></div>
<div class="header_container">

      <div class="navbar">
	 

        <div class="container">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-responsive-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        
		
          <div class="nav-collapse navbar-responsive-collapse collapse" style="height: 0px;">
		   <a class="navbar-brand" href="#"></a>
            <ul class="nav navbar-nav dropdown">
              <li><a href="portal.php">Home</a></li>
              <li><a href="#">Activity</a></li>
              
			   <li><a href="profile.php">Profile</a></li>
			    <li><a href="#">Stats</a></li>
				<li><a href="settings.php">Settings</a></li>
             <li><a href="logout.php">Log out</a></li>
            </ul>
            
		
    
          </div><!-- /.nav-collapse -->
        </div><!-- /.container -->
      </div><!-- /.navbar -->
    </div>';
}
?>