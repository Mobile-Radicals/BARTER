<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title> </title>
<meta name="viewport" content="width=device-width, user-scalable=no">
<style>
html, body{ padding:0; margin:0;}
#canvas-holder { width:90%; height:100%; padding:0 0 0 20px; margin:0; overflow:hidden; }
::-webkit-scrollbar { 
	display: none; 
}

</style>
</head>

<body>
<div id="canvas-holder">
	<table width="100%">
	<?php
    //include the database class
    require_once '../config/con.php';
    require_once '../config/utils.php';
        
    try{
        $card_id = $_REQUEST['id'];
        
        //check if the trader is already a registered trader or
        $stmt = DB::get()->prepare("SELECT * 
                                    FROM tbl_transactions
                                    LEFT JOIN tbl_users ON tbl_transactions.trader_id = tbl_users.user_card_id
                                    WHERE tbl_transactions.consumer_id=:id ORDER BY trans_id DESC");
        $stmt->bindParam(':id', $card_id, PDO::PARAM_STR);
        $stmt->execute();
        
        //set the fetch mode  
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        
        $row = $stmt->rowCount();
            
        if($row > 0){
            
            while($row = $stmt->fetch()){
                //get all the data
                echo "<tr height='60px' style='table-layout:fixed'>
                        <td colspan='5'>".convert_to_date_no_time($row->trans_timestamp)."</td>
                    </tr>
					<tr>
						<td colspan='2'>".$row->business_name."</td>
						<td>£".$row->trans_price."</td>
						<td>".$row->trans_points."pts</td>
						<td>".$row->trans_type."</td>
					</tr>";
            }
        }
        else{
            //no transactions
            
        }
            
    }
    catch(PDOException $e){
        
    }
    ?>
	</table>
</div>
</body>
</html>

<!--

 if ($row->trans_lat == 0|| $row->trans_lon == 0){
                           // echo "<td><strong>no location</strong></td>";
                        }else{
                       // echo "<td><img src='http://maps.googleapis.com/maps/api/staticmap?center=".$row->trans_lat.",".$row->trans_lon."&zoom=15&size=200x100&maptype=roadmap4&markers=color:green%7Clabel:B%7C".$row->trans_lat.",".$row->trans_lon."&sensor=false' /></td>";
                        }
                        
                        -->
