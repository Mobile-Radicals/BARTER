<?php

echo   '<h2>Need a<br />BARTER card?</h2>
             <h3>join the community</h3>
            <h4>Visit any of these businesses:</h4>
			 <div style="height:300px; overflow:scroll; left:0; float:left; list-style:none; margin:10px 0 20px 0; padding 0; width:300px;">';
			
			$data = get_bartercard_businesses();
			
			//print_r($data);
			foreach ($data as $item) 
			{
				//array_push($business, $item['b_name'],$item['b_url'],$item['b_type'],$item['b_contact_number'],$item['b_contact_person']);
				
				if ($item[4] != "")
				{
					$contact = $item[4]." (".$item[3].")";
				}
				else
				{
					$contact = "";
				}
			
echo "           
                <div style='background:#fff; padding: 10px; margin:0px'>
				<h4>".$item[0]."</h4>
				<a href='".$item[1]."' target='_blank'>".$item[1]."</a>
				<p>".$contact."</p>
				</div>
				
			";
			}
echo '     </div>
           <h4>Contact us</h4>
             <ul>
          	     <li><a href="https://twitter.com/trade_barter" target="_blank">@trade_BARTER</a></li><br />
                <li><a href="mailto:hello@barterproject.org">hello@barterproject.org</a></li>
				  <li><a href="mailto:mark@barterproject.org">mark@barterproject.org</a></li>
				  <li><a href="mailto:bran@barterproject.org">bran@barterproject.org</a></li>
            </ul>
            
           <h2 style="clear:both; text-align:right;"><a href="https://twitter.com/search?q=tradeBARTER&src=typd" target="_blank">#tradeBARTER</a> <span style="font-size:16px; color:#4e4d4d; ">join the community</span></h2>';
?>