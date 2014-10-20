<?php 

echo '

 <div class="navbar-header">
           <button class="navbar-toggle" type="button" data-toggle="collapse" id="menu-toggler">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
           <a href="dashboard.php"><img src="http://barterproject.org/wp-content/uploads/2013/06/final_logo_full.png" alt="logo" height="80px" id="logo" /></a>
        </div>
        <ul class="nav navbar-nav pull-right hidden-xs">
           
            <li class="dropdown">
                <a href="#" class="dropdown-toggle hidden-xs hidden-sm" data-toggle="dropdown">
                    Your account
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="profile.php">My Profile</a></li>
					<li><a href="#">Trader: '.$_SESSION['bname'].'</a></li>
					<li><a href="#">ID: '.$_SESSION['cid'].'</a></li>
                    <li><a href="export_data.php">Export your data</a></li>
                    <li><a href="feedback.php">Send feedback</a></li>
                </ul>
            </li>
            <li class="settings hidden-xs hidden-sm">
                <a href="profile.php" role="button">
                    <i class="icon-cog"></i>
                </a>
            </li>
           <li class="settings hidden-xs hidden-sm">
                <a href="logout.php" role="button">
                    <i class="icon-share-alt"></i>
                </a>
            </li>
        </ul>';

?>