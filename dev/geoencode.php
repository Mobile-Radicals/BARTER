
<?php
 include ('../config/utils.php');

 
$city = 'LA1 4Dx';
 
$latlon = geoencodeaddress($city);
print_r($latlon);
 
?>
