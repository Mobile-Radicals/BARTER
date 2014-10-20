<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require '../../config/session_check.php';
require_once("../../config/utils.php");

$uid=$_SESSION['cid'];
$sort = "t.trans_id";
$order="DESC";
$offset=0;
$limit=0;

$recent=$_REQUEST['recent'];
$web=$_REQUEST['web'];
$mobile =$_REQUEST['mobile'];
$goods=$_REQUEST['goods'];
$service=$_REQUEST['service'];
$goods_service=$_REQUEST['goods_service'];
$trader=$_REQUEST['trader'];
$customer=$_REQUEST['customer'];
$local=$_REQUEST['local'];
$non_local=$_REQUEST['non_local'];

$ajax_response = false;

$query ="t.trader_id='$uid' AND t.trans_id > 0 ";
$query0="";
$query1="";

//transaction date
if ($recent == '1'){
	//$query .= "AND t.trans_timestamp='web_manual'";
}


//transaction origin
if (($web == '1') && ($mobile == '1'))
{
	$query .= "AND (t.trans_origin='mobile_nfc' OR t.trans_origin='mobile_qr' OR t.trans_origin='mobile_manual' OR t.trans_origin='web_manual')";
}
else if ($web == '1'){
	$query .= "AND t.trans_origin='web_manual'";
}
else if ($mobile == '1'){
	$query .= "AND t.trans_origin='mobile_nfc' OR t.trans_origin='mobile_qr' OR t.trans_origin='mobile_manual'";
}
 

//transaction typw
$trans_sale_type_counter = 0;

if ($goods == '1'){
	$query0 .= "AND t.trans_type='goods'";
	$trans_sale_type_counter++;
}

if ($service == '1'){
	$query0 .= "AND t.trans_type='services'";
	$trans_sale_type_counter++;
}

if ($goods_service == '1'){
	$query0 .= "AND t.trans_type='both'";
	$trans_sale_type_counter++;
}

//there has been more than one customer type selected
if ($trans_sale_type_counter > 1)
{
	$split_query1 =  str_replace("AND"," OR ", $query0);
	$split_query1 =  preg_replace('/OR/', 'AND (', $split_query1, 1); 
	$query .= $split_query1.")";
}
else
{
	$query .= $query0;
}

//transactin consumer typer
$trans_type_counter = 0;

if ($trader == '1'){
	$query1 .= "AND t.consumer_type='barter'";
	$trans_type_counter++;
}

if ($customer == '1'){
	$query1 .= "AND t.consumer_type='barter'";
	$trans_type_counter++;
}

if ($local == '1'){
	$query1 .= "AND t.consumer_type='local_non_barter'";
	$trans_type_counter++;
}

if ($non_local == '1'){
	$query1 .= "AND t.consumer_type='external'";
	$trans_type_counter++;
}

//there has been more than one customer type selected
if ($trans_type_counter > 1)
{
	$split_query =  str_replace("AND"," OR ", $query1);
	$split_query =  preg_replace('/OR/', 'AND (', $split_query, 1); 
	$query .= $split_query.")";
}
else
{
	$query .= $query1;
}

//echo $query;


	//if limit is 0 do not limit the returned data
	if ($limit == 0)
	{
		$stmt = DB::get()->prepare("SELECT * FROM tbl_transactions AS t LEFT JOIN tbl_users AS u ON t.consumer_id=u.user_card_id WHERE $query ORDER BY $sort $order");
		//echo "SELECT * FROM tbl_transactions AS t LEFT JOIN tbl_users AS u ON t.consumer_id=u.user_card_id WHERE $query ORDER BY $sort $order";
		//$stmt->bindParam(':tid', $uid, PDO::PARAM_STR);
		//$stmt->bindValue(':order', $sort, PDO::PARAM_STR);
	}
	else
	{
		$stmt = DB::get()->prepare("SELECT * FROM tbl_transactions AS t LEFT JOIN tbl_users AS u ON t.consumer_id=u.user_card_id WHERE $query LIMIT :offset, :limit");
		$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
		$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
	}
	$stmt->execute();		
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$row = $stmt->rowCount();
	
	$ajax_response = true;
	
	$data = array();
			
	foreach ($rows as $item) 
	{
		$activity = array();
		array_push($activity, $item['trans_id'],$item['consumer_id'],$item['consumer_type'],$item['consumer_name'],$item['trans_lat'],$item['trans_lon'],$item['trans_type'],$item['trans_origin'],$item['trans_price'],$item['trans_points'],$item['trans_timestamp'],$item['upload_timestamp'],$item['user_name'],$item['user_postcode'],$item['user_email'],$item['business_name'],$item['is_trader']);
		
		array_push($data, $activity);
	}
	



ob_start();
echo '<h4><u><strong>'.sizeof($data).'</strong> Transactions</u></h4><table id="activity" class="table table-bordered table-hover">';
        
  
        foreach ($data as $item){
            
            if ($item[2] == "barter")
            {
                $style_class = "success";
                $cat = 1;
            }
            else if ($item[2] == "local_non_barter")
            {
                $style_class = "active";
                $cat = 2;
            }
            else
            {
                $style_class = "warning";
                $cat = 3;
            }
            ?>
            <tr class="<?=$style_class?>">
            <td>
           <?php
                if ($cat != 1)
                {
                    //the transactions is not part of BARTER
                    echo $item[3];
                }
                else
                {
                    if ($item[16] == 1)
                    {
                        echo $item[15]." - ".$item[12];
                    }
                    else
                    {
                        //try and get the users details (non traders)
                        if (isset($item[12]))
                        {
                            echo "<tr><td>".$item[12]."</td></tr>";
                        }
                        else
                        {
                            echo '<strong>Information cannot be found</strong>';
                        }
                    }
                }
            ?>
            </td>
            <td><?=CURRENCY.number_format ( $item[8],2 )?></td>
            <td><?=$item[9]?> pts</td>
            <td colspan="2">recorded: <b><?=convert_to_date($item[10])?></b></td>
            </tr>
          <tr class="extra_content" hidden="true" >
            <td>
           <?php
                //trader
                echo '<table class="table">';
                
                //check to see if the user is on the system
                if ($cat != 1)
                {
                    //the transactions is not part of BARTER
                    echo "<tr><td>".$item[3]."</td></tr>";
                }
                else
                {
                    if ($item[16] == 1)
                    {
                        echo "<tr><td>".$item[15]." - ".$item[12]."</td></tr>";
                        echo "<tr><td>".$item[13]."</td></tr>";
                        echo "<tr><td>".$item[14]."</td></tr>";
                    }
                    else
                    {
                        //try and get the users details (non traders)
                        if (isset($item[12]))
                        {
                            echo "<tr><td>".$item[12]."</td></tr>";
                            echo "<tr><td>".$item[13]."</td></tr>";
                            echo "<tr><td>".$item[14]."</td></tr>";
                        }
                        else
                        {
                            echo '<tr><td class="danger"><strong>Information cannot be found</strong></td></tr>';
                        }
                    }
                    echo "<tr><td>".$item[1]."</td></tr>";
                }
                
                echo "</table>";
                
              ?>
            </td>
           
           
            <td  align="center">
                <?php
                    if ($item[6] == "goods")
                    {
                        $icon = "http://barterproject.org/images/goods_icon.png";
                    }
                    else if ($item[6] == "services")
                    {
                        $icon = "http://barterproject.org/images/service_trans_icon.png";
                    }
                    else
                    {
                        $icon = "http://barterproject.org/images/goods_service.png";
                    }
                ?>
                <img src="<?=$icon?>" alt="icon" width="80px" />
            </td>
             <td align="center"><?php //echo '<img src="http://maps.googleapis.com/maps/api/staticmap?center='.$item[4].','.$item[5].'&zoom=15&size=200x200&maptype=roadmap4&markers=color:green%7Clabel:B%7C'.$item[4].','.$item[5].'&sensor=false" />';
			  if ($item[7] == "mobile_nfc")
                    {
                        $icon1 = "http://barterproject.org/images/nfc_icon.png";
                        //$icon2 = "http://barterproject.org/images/mobile.png";
                    }
                    else if ($item[7] == "mobile_qr")
                    {
                        $icon1 = "http://barterproject.org/images/qr_icon.png";
                       // $icon2 = "http://barterproject.org/images/mobile.png";
                    }
                    else if ($item[7] == "mobile_manual")
                    {
                        $icon1 = "http://barterproject.org/images/input_icon.png";
                       // $icon2 = "http://barterproject.org/images/mobile.png";
                    }
                    else
                    {
                        $icon1 = "http://barterproject.org/images/input_icon.png";
                       // $icon2 = "http://barterproject.org/images/web_manual.png";
                    }
			 
			 
			 ?>
              <div class="alert-barter-info">
                   <img src="<?=$icon1?>" alt="icon" width="80px" />
               
                </div>
             </td>
            <td  align="center"><?php
                    if ($item[7] == "mobile_nfc")
                    {
                       // $icon1 = "http://barterproject.org/images/nfc_icon.png";
                        $icon2 = "http://barterproject.org/images/mobile.png";
                    }
                    else if ($item[7] == "mobile_qr")
                    {
                       // $icon1 = "http://barterproject.org/images/qr_icon.png";
                        $icon2 = "http://barterproject.org/images/mobile.png";
                    }
                    else if ($item[7] == "mobile_manual")
                    {
                       // $icon1 = "http://barterproject.org/images/input_icon.png";
                        $icon2 = "http://barterproject.org/images/mobile.png";
                    }
                    else
                    {
                       // $icon1 = "http://barterproject.org/images/input_icon.png";
                        $icon2 = "http://barterproject.org/images/web_manual.png";
                    }
                ?>
                <div class="alert-barter-info">
                    
                		<img src="<?=$icon2?>" alt="icon" width="80px" />
                </div>
                </td>
            <td>uploaded: <?=convert_to_date($item[11])?></td>
           </tr>
        <?php }?>
        </table>
<?php
ob_end_flush();
?>

