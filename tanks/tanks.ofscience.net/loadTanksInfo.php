<?php
/**
 * Website: https://github.com/pcarrigg
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Paul Carrigg <pcarrigg@gmail.com>
 * @version 1.00 
 */


if ($_GET["server"] == "eu")
	$server = "eu";
else if ($_GET["server"] == "sea")
		$server = "sea";
else
	$server ="na";
include_once('libs/WOTLib.php');






	$ch = curl_init("http://$site/encyclopedia/vehicles/");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
 	$html = str_get_html($output);
echo "Loading Tanks<BR>";
	$g=0;
	foreach( $html->find('h2.b-header-h2__encyclopedia-indent') as $b ) {
		$tankCountry = $b->plaintext;
		$c = $b->parent();
		echo "Country:" .$tankCountry."<BR>";
		$i = 0;
		
		foreach ($c->find('div.i-three-coll') as $col)
		{
			if ($g <= $i && $i < ($g + 5)) {
							$class = $col->find('h5',0)->plaintext;
							echo "&nbsp;&nbsp;&nbsp;&nbsp;".$class."<BR>";
							foreach ($col->find('li') as $li)
							{
								$premium = false;
								if(strpos($li->class,"premium"))
									$premium=true;
								$tankLevel = $li->find('span.b-encyclopedia-list_level',0)->plaintext;
								$tankName = $li->find('span.b-encyclopedia-list_name_text',0)->plaintext;
								if ($premium)
								echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*".$tankName ." " .$tankLevel."";
								else
								echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$tankName ." " .$tankLevel."";
				
				
								$q = $db->prepare("select * from tank_list where name=:name");
								$q->execute(array(":name"=>$tankName));
							
								if ($q->rowCount()){
									$r = $q->fetch();
									if ($r["level"] != romanToInt($tankLevel)) {
										$t = $db->prepare("update tank_list set level=:level where id=:id");
										$t->execute(array("level"=>romanToInt($tankLevel),"id"=>$r["id"]));
										echo " [ updated ]<BR>";
									} else
										echo " [ found ]<BR>";
								} else {
					
					
									$t = $db->prepare("insert into tank_list (name,level,class,premium,country) values (:name,:level,:class,:premium,:country)");
									$t->execute(array(
										":name"=>trim($tankName),
										":country"=>trim(html_entity_decode($tankCountry)," \t\n\r\0\x0B\xA0"),
										":class"=>trim(html_entity_decode($class)," \t\n\r\0\x0B\xA0"),
										":level"=>romanToInt($tankLevel),
										":premium"=>$premium
									));
								 echo " [ + ]<BR>";
								}
				
				
				
							}
			
			} else {
				
			}
			$i++;
		}
		$g = $g+5;
		//$wotStats["registered"] = $stamp->getAttribute('data-timestamp');
	}





?>
