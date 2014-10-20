<?php
session_name('barter');
session_start(); 
session_unset();
header("Location: http://barterproject.org");
?>