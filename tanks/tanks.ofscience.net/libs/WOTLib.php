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


include_once('simple_html_dom.php');
if ($server == "eu")
	include_once('/home/tanks/tanks_db.eu.inc.php');
else if ($server == "sea")
	include_once('/home/tanks/tanks_db.sea.inc.php');
else
	include_once('/home/tanks/tanks_db.inc.php');

$WOTServer = "http://dava.worldoftanks.com";
$userStats = "/userstats/2/stats/slice/?server=us&platform=ios&hours_ago=1&hours_ago=168&hours_ago=336&account_id=";
$region["na"] = true;





if ($server =="eu")
	$site = "worldoftanks.eu";
else if ($server =="sea")
		$site = "worldoftanks-sea.com";
else
	$site = "worldoftanks.com";

$cch = curl_init("http://$site/community/accounts/");
curl_setopt($cch, CURLOPT_HEADER, 0);
curl_setopt($cch, CURLOPT_RETURNTRANSFER, 1);


$db = new PDO("mysql:host=$DBHost;dbname=$DBName",$DBUser,$DBPass, array(
    PDO::ATTR_PERSISTENT => true
));

$statCache = array();
$latestStats=false;
$idCache = array();
$tankList= false;

function updateClanStats($clanName)
{
	global $db,$site; // http://worldoftanks.com/community/clans/#wot&ct_search=rddt5&ct_order_by=-name
		$ch = curl_init("http://$site/community/clans/?type=table&offset=0&limit=25&order_by=-name&search=$clanName&echo=1&id=clans_index");//"http://$site/community/clans/#wot&ct_search=$clanName&ct_order_by=-name");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json, text/javascript, */*; q=0.01","Referer: http://$site/community/clans/","X-Requested-With:XMLHttpRequest"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch); 
		curl_close($ch); 
		$results = json_decode($output);
		//echo "<pre>";
	//	print_r($results);
	//	echo "</pre>";
		if($results->request_data->items[0]->id) {
			//Store the results in the DB

			$q2 = $db->prepare("insert into clan_stats (clan_id,abbreviation,created_at,name,member_count,owner,motto,clan_emblem_url,clan_color,owner_id) values (:cid,:abb,:cat,:name,:memb,:own,:mot,:emb,:color,:oid)");
			$q2->execute(array(
				":cid"=>$results->request_data->items[0]->id,
				":abb"=>$results->request_data->items[0]->abbreviation,
				":cat"=>$results->request_data->items[0]->created_at,
				":name"=>$results->request_data->items[0]->name,
				":memb"=>$results->request_data->items[0]->member_count,
				":own"=>$results->request_data->items[0]->owner,
				":mot"=>$results->request_data->items[0]->motto,
				":emb"=>$results->request_data->items[0]->clan_emblem_url,
				":color"=>$results->request_data->items[0]->clan_color,
				":oid"=>$results->request_data->items[0]->owner_id
				));
//				return $results->request_data->items[0]->id;

		}
}


function getClan($clanName)
{
	global $db;
	$clanName =ereg_replace("[^-A-Za-z0-9_]", "", $clanName );
	
	
	$q = $db->prepare("select * from clans where clan_name=?");
	$q->execute(array($clanName));
	if ($q->rowCount()) { 
		// Found a user!
	//	echo "Found Clan";
		$result = $q->fetch();
		$q2 = $db->prepare("select * from clan_stats where clan_id=? order by updateTime DESC LIMIT 1");
		$q2->execute(array($result["clan_id"]));
		$stat = $q2->fetch();		
		$result["stats"] = $stat;
		return $result;		
	}
	
	return false;
}

function getClanIdFromName($clanName)
{
	global $db,$site,$debug;
	$clanName =ereg_replace("[^-A-Za-z0-9_]", "", $clanName );
	if ($debug) {
		echo "Searching for ".strtoupper($clanName)."<BR>";
	}
	
	$q = $db->prepare("select * from clans where clan_name=?");
	$q->execute(array($clanName));
	if ($q->rowCount()) { 
		// Found a user!
		$result = $q->fetch();
		return $result["clan_id"];		
	}

	$ch = curl_init("http://$site/community/clans/?type=table&offset=0&limit=25&order_by=-name&search=$clanName&echo=1&id=clans_index");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json, text/javascript, */*; q=0.01","Referer: http://$site/community/clans/","X-Requested-With:XMLHttpRequest"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch); 
	curl_close($ch); 
	$results = json_decode($output);

	$i = 0;
	foreach ($results->request_data->items as $item) {
		if ($debug) {
			echo "Found: -" .$results->request_data->items[$i]->abbreviation."-<BR>";
		}
	if($results->request_data->items[$i]->abbreviation == strtoupper($clanName)) {
		//Store the results in the DB
		if ($debug) {
			echo "Adding: -" .$results->request_data->items[$i]->abbreviation."-<BR>";
		}
		$q = $db->prepare("insert into clans (clan_name,clan_id) values (:name,:id)");
		$q->execute(array(":name"=>$results->request_data->items[$i]->abbreviation,":id"=>$results->request_data->items[$i]->id));
		
		
		$q2 = $db->prepare("insert into clan_stats (clan_id,abbreviation,created_at,name,member_count,owner,motto,clan_emblem_url,clan_color,owner_id) values (:cid,:abb,:cat,:name,:memb,:own,:mot,:emb,:color,:oid)");
		$q2->execute(array(
			":cid"=>$results->request_data->items[$i]->id,
			":abb"=>$results->request_data->items[$i]->abbreviation,
			":cat"=>$results->request_data->items[$i]->created_at,
			":name"=>$results->request_data->items[$i]->name,
			":memb"=>$results->request_data->items[$i]->member_count,
			":own"=>$results->request_data->items[$i]->owner,
			":mot"=>$results->request_data->items[$i]->motto,
			":emb"=>$results->request_data->items[$i]->clan_emblem_url,
			":color"=>$results->request_data->items[$i]->clan_color,
			":oid"=>$results->request_data->items[$i]->owner_id
			));
			
			
			return $results->request_data->items[$i]->id;
			
			
			
			
			
	}
		$i++;
	}
	
	return false;
}


function loadClanMembers($clanName)
{
	global $db,$site,$forceUpdate,$debug;
	$clan_id = getClanIdFromName($clanName);
	//echo "<BR>".$clan_id."<BR>";
	//$clan_id = $clan_id."-".strtoupper($clanName);
	$clanUsers = array();
	//Only Refresh Clan User List every 24hours
	$q = $db->prepare("select * from clans where clan_name=? and refreshDate >= DATE_SUB(NOW() ,INTERVAL 24 HOUR)");
	$q->execute(array($clanName));
	$cusers=array();
	if ($q->rowCount()) { 
		// Found a user!
			
			$row = $q->fetch();
			$q2 = $db->prepare("select * from accounts where clan_id=?");
			$q2->execute(array($row["clan_id"]));
			while($r = $q2->fetch()) {
				array_push($clanUsers,array("name"=>$r["account_name"],"battles"=>$r["battles"],"wr"=>$r["wr"],"eff"=>$r["eff"]));
			}
			if (!$forceUpdate)
				return $clanUsers;
	}
	

	//http://worldoftanks.com/community/clans/1000003624/members/?type=table&_=1352839328274&offset=0&limit=100&order_by=role&search=&echo=1&id=clan_members_index
	$ch = curl_init("http://$site/community/clans/$clan_id/members/?type=table&offset=0&limit=100&order_by=-date&search=&echo=1&id=clan_members_index");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json, text/javascript, */*; q=0.01","Referer: http://$site/community/clans/$clan_id-". strtoupper($clanName) ."/","X-Requested-With:XMLHttpRequest"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch); 
	curl_close($ch); 
	if ($debug){
		echo "<pre>";
		echo $output;
		echo "</pre>";
	}
	$results = json_decode($output);
	$clanUsers = array();
	if ($results->request_data->items[0]->account_id) {
		foreach ($results->request_data->items as $member)
		{
			$q = $db->prepare("select * from accounts where account_id=?");
			$q->execute(array($member->account_id));
			if ($q->rowCount()) { 
				// Found a user! we do nothing!
				//$result = $q->fetch();
				
				$q = $db->prepare("update accounts set clan_id=:cid where account_id=:acid");
				$q->execute(array(":cid"=>$clan_id,":acid"=>$member->account_id));
				//return $result["clan_id"];	
			//	echo "Already Tracking: ". $result["account_name"]."<BR>";				
			} else {
				
				
				$q2 = $db->prepare("insert into accounts (account_name,account_id,clan_id) values (:name,:id,:cid)");
				$q2->execute(array(":name"=>$member->name,":id"=>$member->account_id,":cid"=>$clan_id));
				//return $results->request_data->items[0]->id;
				//print_r($member);
				echo "Now Tracking: " . $member->name."<BR>";
			}
			$stats = loadStatistics($member->account_id,24,false,false);
			if ($stats)
				if ($stats["battles"]) {
					$wr = round((($stats["victories"] / $stats["battles"]) * 100),2);
					$battles = $stats["battles"];
					$efficiency = $stats["efficiency"];
					$days = $stats["clan_days"];
				} else{ 
					$wr = 0;
					$battles =0;
					$efficiency = 0;
					$days = 0;
				}
			else {
				$wr = 0;
				$battles = 0;
				$efficiency = 0;
				$days =0;
			}
			array_push($clanUsers,array("name"=>$member->name,"battles"=>$battles,"wr"=>$wr,"eff"=>$efficiency,"days"=>$days));
		}
		
			//Remove users if they arent in the clan anymore
				$q = $db->prepare("select * from accounts where clan_id=:cid");
				$q->execute(array(":cid"=>$clan_id));
				//print_r($clanUsers);
				//echo count($clanUsers);
				while($row = $q->fetch()) {
					$found = false;
					foreach($clanUsers as $cmem)
					{
						if ($row["account_name"] == $cmem["name"]) 
							$found = true;
					}
					if (!$found)
					{	
						$q2 = $db->prepare("update accounts set clan_id=:cid where account_id=:acid");
						$q2->execute(array(":cid"=>"0",":acid"=>$row["account_id"]));
						echo "Removing ".$row["account_name"]."<BR>";
					}
				}
				
		
			updateClanStats($clanName);
			$q = $db->prepare("update clans set refreshDate=NOW()  WHERE clan_id=:cid");
			$q->execute(array(":cid"=>$clan_id));
	
		return $clanUsers;
	}
	return array();
}




function getPlayerIdFromNick($nickname)
{
	global $db,$site;
	//curl -H "Accept:application/json, text/javascript, */*; q=0.01" -H "Referer:http://worldoftanks.com/community/accounts/" -H "X-
	//Requested-With:XMLHttpRequest"  -G    //"http://worldoftanks.com/community/accounts/?type=table&_=1339526733658&offset=0&limit=25&order_by=name&search=test&echo=2&id=
 //	accounts_index"
	$nickname =ereg_replace("[^A-Za-z0-9_]", "", $nickname );
	//Check if the user is already in the database
	$q = $db->prepare("select * from accounts where account_name=?");
	$q->execute(array($nickname));
	if ($q->rowCount()) { 
		// Found a user!
		$result = $q->fetch();
		return $result["account_id"];		
	}
	
	
	//We didnt find our user in the database so lets query the WOT server for it
	
 	$ch = curl_init("http://$site/community/accounts/?type=table&&offset=0&limit=25&order_by=name&search=$nickname&echo=1&id=accounts_index");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json, text/javascript, */*; q=0.01","Referer: http://$site/community/accounts/","X-Requested-With:XMLHttpRequest"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch); 
	curl_close($ch); 
	$results = json_decode($output);
	
	if($results->request_data->items[0]->id) {
		// check again to see if the users' in the DB
		
		$q2 = $db->prepare("select * from accounts where account_name=?");
		$q2->execute(array($nickname));
		if ($q2->rowCount()) { 
			// Found a user!
			$result = $q2->fetch();
			return $result["account_id"];		
		}
		//Store the results in the DB
		$q = $db->prepare("insert into accounts (account_name,account_id) values (:name,:id)");
		$q->execute(array(":name"=>$results->request_data->items[0]->name,":id"=>$results->request_data->items[0]->id));
		return $results->request_data->items[0]->id;
	}
	
	return false;
}
function getTopTankers($order=1,$dir=1)
{
	global $db;
	if ($dir == 1)
		$direct = "DESC";
	else
		$direct = "ASC";
	if ($order== 1)
		$sort = "eff";
	else if ($order == 2)
		$sort = "wr";
	else if ($order == 3)
		$sort = "battles";
	//else if ($order== 4)
	//	"eff where update"
	$q = $db->prepare("select * from accounts where battles > 500 order by $sort $direct limit 10");
	$q->execute();
	$topTanks = array();
	if ($q->rowCount()) { 
		// Found a user!
		while ($result = $q->fetch())
		{
			array_push($topTanks,$result);
		}
	
		//return $result["account_name"];		
	}
		return $topTanks;
}

function colorEff($eff)
{
	if ($eff <=600)
		return ("<span class='red'>$eff</span>");
	else 	if ($eff <=900)
		return ("<span class='red'>$eff</span>");
	else 	if ($eff <=1200)
		return ("<span class='yellow'>$eff</span>");
	else 	if ($eff <=1500)
		return ("<span class='green'>$eff</span>");
	else 	
		return ("<span class='purple'>$eff</span>");	
		
}
function colorWR($eff)
{
	if ($eff < 47)
		return ("<span class='red'>$eff</span>");
	else 	if ($eff <= 50)
		return ("<span class='yellow'>$eff</span>");
	else 	if ($eff <= 55)
		return ("<span class='green'>$eff</span>");
	else 	
		return ("<span class='purple'>$eff</span>");	
		
}



function getRecentlyAdded()
{
	global $db;
	$q = $db->prepare("select * from accounts order by createDate DESC limit 10");
	$q->execute();
	$topTanks = array();
	if ($q->rowCount()) { 
		// Found a user!
		while ($result = $q->fetch())
		{
			array_push($topTanks,$result);
		}
	
		//return $result["account_name"];		
	}
		return $topTanks;
}


function getAccountName($account_id)
{
	global $db;
	$q = $db->prepare("select * from accounts where account_id=?");
	$q->execute(array($account_id));
	if ($q->rowCount()) { 
		// Found a user!
		$result = $q->fetch();
		return $result["account_name"];		
	}
	return false;
}

function getTrackingDate($account_id)
{
	global $db;
	$q = $db->prepare("select * from accounts where account_id=?");
	$q->execute(array($account_id));
	if ($q->rowCount()) { 
		// Found a user!
		$result = $q->fetch();
		return $result["createDate"];		
	}
	return false;
}

function getLastUpdate($account_id)
{
	global $db;
	$q = $db->prepare("select * from accounts where account_id=?");
	$q->execute(array($account_id));
	if ($q->rowCount()) { 
		// Found a user!
		$result = $q->fetch();
		return $result["lastUpdate"];		
	}
	return "Unknown";
}

function checkForceUpdate($account_id)
{
	global $db;
	$q = $db->prepare("select * from accounts where account_id=? and lastUpdate >= (NOW() - INTERVAL 30 MINUTE)");
	$q->execute(array($account_id));
	if ($q->rowCount()) { 
		// Found a user!
		$result = $q->fetch();
		return false;
	}
	return true;
}


function refreshStats()
{
	global $db;
	$q = $db->prepare("select * from accounts where lastUpdate <= DATE_SUB(NOW() ,INTERVAL 45 MINUTE)");
	$q->execute();
	while($result = $q->fetch()) {
	//	return $result["account_name"];	
		//echo "Processing - " . $result["account_name"]."\n";
		loadStatistics($result["account_id"],0,true);
		//sleep(rand(0,2));
	}
	
	$q = $db->prepare("select * from clans");
	$q->execute();
	while($result = $q->fetch()) {
	//	return $result["account_name"];	
		updateClanStats($result["clan_name"]);
	//	loadStatistics($result["account_id"]);
		//sleep(rand(0,2));
	}
	
	
	return true;
}

function tracking()
{
	global $db;
	$q = $db->prepare("select * from accounts");
	$q->execute();
	$accounts = $q->rowCount();
	
	$q = $db->prepare("select * from clans");
	$q->execute();
	$clans = $q->rowCount();
	
	return "Currently tracking $accounts tankers and $clans clans";
	
}
//Loads the statistics from the database or WOT Servers
// Age specifies the age in hours of the statistics to be loaded
// age = 0, checks WOT servers for an update (if no update has been run in the past 15 minutes)
// age = 1, past hour
// age = 24, past day
// age = 168, past week
// age = 720, pasth 30 days
//
// returns WOTStats array on success, false on error
function loadStatistics($account_id,$age=0,$loadData=false,$loadDataReally=true)
{
	global $db,$WOTServer,$userStats,$latestStats,$site,$forceUpdate,$cch;
	if ($loadDataReally) {
	if ($forceUpdate)
		$loadData = true;
	if (checkForceUpdate($account_id))
		$loadData = true;
}
	if ($age) {
		$q = $db->prepare("select * from account_stats where account_id=:id and updateTime >= NOW() - INTERVAL :age HOUR order by updateTime ASC LIMIT 1");
	//	$q = $db->prepare("select * from stats where account_id=:id and dstamp >= NOW() - INTERVAL :age HOUR order by dstamp ASC LIMIT 1");
		$q->execute(array(":id"=>$account_id,":age"=>$age));
		if ($q->rowCount()) {
			$res = $q->fetch();
			//Load the Tanks stats
			$q2= $db->prepare("select * from tank_stats where account_stats_update=:update and account_id=:acid");
			$q2->execute(array(":update"=>$res["updated"],":acid"=>$account_id));
			$res["tanks"] = array();
			while($t=$q2->fetch()){
					array_push($res["tanks"],$t);
			}
			
			if ($res["battles"]) {
				$res["victories_p"] = round(($res["victories"] / $res["battles"]) * 100,2);
				$res["defeats_p"] = round(($res["defeats"] / $res["battles"]) * 100,2);
				$res["draws"] = $res["battles"] - $res["victories"] - $res["defeats"];
				$res["draws_p"] =round(($res["draws"] / $res["battles"]) * 100,2);
				$res["survived_p"] = round(($res["survived"] / $res["battles"]) * 100,2);
				$res["destroyed_r"] = round(($res["destroyed"] / $res["battles"]),2);
				$res["damage_r"] = round(($res["damage"] / $res["battles"]),2);
				if (($res["battles"] - $res["survived"]))
					$res["kd_r"] = round(($res["destroyed"] / ($res["battles"] - $res["survived"])),2);
				$res["detected_r"] = round(($res["detected"] / $res["battles"]) ,2);
				$res["capture_r"] = round(($res["capture"] / $res["battles"]) ,2);			
				$res["defense_r"] = round(($res["defense"] / $res["battles"]) ,2);
				$res["experience_r"] = round(($res["experience"] / $res["battles"]),2);
			}
		//	return json_decode($res["data"]);
		//$statCache[""]
		$latestStats = $res;
		return $res;
		}
		return false;
		
		
	} else {
		
	

		$q = $db->prepare("select * from account_stats where account_id=:id and updateTime >= (NOW() - INTERVAL 30 MINUTE) order by dstamp DESC");
		$q->execute(array(":id"=>$account_id));
		if ($q->rowCount()){
			// We've already got fairly current data - dont overload WOT servers
			$res = $q->fetch();
			$q2= $db->prepare("select * from tank_stats where account_stats_update=:update and account_id=:acid");
			$q2->execute(array(":update"=>$res["update"],":acid"=>$account_id));
			$res["tanks"] = array();
			while($t=$q2->fetch()){
					array_push($res["tanks"],$t);
			}
			//return json_decode($res["data"]);
				if ($res["battles"]) {
					$res["victories_p"] = round(($res["victories"] / $res["battles"]) * 100,2);
					$res["defeats_p"] = round(($res["defeats"] / $res["battles"]) * 100,2);
					$res["draws"] = $res["battles"] - $res["victories"] - $res["defeats"];
					$res["draws_p"] =round(($res["draws"] / $res["battles"]) * 100,2);
					$res["survived_p"] = round(($res["survived"] / $res["battles"]) * 100,2);
					$res["destroyed_r"] = round(($res["destroyed"] / $res["battles"]),2);
					$res["damage_r"] = round(($res["damage"] / $res["battles"]),2);
					if (($res["battles"] - $res["survived"]))
						$res["kd_r"] = round(($res["destroyed"] / ($res["battles"] - $res["survived"])),2);
					$res["detected_r"] = round(($res["detected"] / $res["battles"]) ,2);
					$res["capture_r"] = round(($res["capture"] / $res["battles"]) ,2);			
					$res["defense_r"] = round(($res["defense"] / $res["battles"]) ,2);
					$res["experience_r"] = round(($res["experience"] / $res["battles"]) ,2);
				}
			$latestStats = $res;
			return $res;
			//return processStats(json_decode($res["data"]));
		} 
		
		
		$q = $db->prepare("select * from account_stats where account_id=:id order by updateTime DESC limit 1");
		$q->execute(array(":id"=>$account_id));
		$lastUpdate =false;
		if ($q->rowCount()){
			// We've already got fairly current data - dont overload WOT servers
			$res = $q->fetch();
			
			$q2= $db->prepare("select * from tank_stats where account_stats_update=:update and account_id=:acid");
			$q2->execute(array(":update"=>$res["updated"],":acid"=>$account_id));
			$res["tanks"] = array();
			while($t=$q2->fetch()){
					array_push($res["tanks"],$t);
			}
			$latestStats = $res;
			$lastUpdate= $res;
			//$lastUpdate = json_decode($res["data"]);
		} else {
			$loadData = true;
		}
			if ($lastUpdate["battles"]) {
				$lastUpdate["victories_p"] = round(($lastUpdate["victories"] / $lastUpdate["battles"]) * 100,2);
				$lastUpdate["defeats_p"] = round(($lastUpdate["defeats"] / $lastUpdate["battles"]) * 100,2);
				$lastUpdate["draws"] = $lastUpdate["battles"] - $lastUpdate["victories"] - $lastUpdate["defeats"];
				$lastUpdate["draws_p"] =round(($lastUpdate["draws"] / $lastUpdate["battles"]) * 100,2);
				$lastUpdate["survived_p"] = round(($lastUpdate["survived"] / $lastUpdate["battles"]) * 100,2);
				$lastUpdate["destroyed_r"] = round(($lastUpdate["destroyed"] / $lastUpdate["battles"]),2);
				$lastUpdate["damage_r"] = round(($lastUpdate["damage"] / $lastUpdate["battles"]),2);
				if (($lastUpdate["battles"] - $lastUpdate["survived"]))
					$lastUpdate["kd_r"] = round(($lastUpdate["destroyed"] / ($lastUpdate["battles"] - $lastUpdate["survived"])),2);
				$lastUpdate["detected_r"] = round(($lastUpdate["detected"] / $lastUpdate["battles"]) ,2);
				$lastUpdate["capture_r"] = round(($lastUpdate["capture"] / $lastUpdate["battles"]),2);			
				$lastUpdate["defense_r"] = round(($lastUpdate["defense"] / $lastUpdate["battles"]),2);
				$lastUpdate["experience_r"] = round(($lastUpdate["experience"] / $lastUpdate["battles"]) ,2);
			}
		
		
		if (!$loadData)
			return $lastUpdate;
		
		$account_name = getAccountName($account_id);
	
		$wotStats = array();
		$wotStats["web_check"] = true;
	//	echo "Going to the web..";
		//$ch = curl_init("http://$site/community/accounts/$account_id-$account_name/");
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($cch,CURLOPT_URL,"http://$site/community/accounts/$account_id-$account_name/");
		$output = curl_exec($cch);
		
	 	$html = str_get_html($output);
		if ($html) {
			
			foreach( $html->find('div.b-data-create') as $b ) {
				$stamp = $b->find('span',0);
				$wotStats["registered"] = $stamp->getAttribute('data-timestamp');
			}
	
			foreach( $html->find('div.b-data-date') as $b) {
				$stamp = $b->find('span',0);
				$wotStats["updated"] = $stamp->getAttribute('data-timestamp');
			}
			date_default_timezone_set('America/Los_Angeles');
			//echo $lastUpdate->updated. " ~ " . $wotStats["updated"]."<BR>";
		//if($lastUpdate)
		$q = $db->prepare("update accounts set lastUpdate=NOW() where account_id=:acid");
		$q->execute(array(":acid"=>$account_id));
		
			if ($lastUpdate["updated"] != $wotStats["updated"])	
		//	if(1)
			{
			
				foreach ($html->find('div.b-clan-list') as $b)
				{
					$wotStats['clan_url'] = $b->find('a',0)->getAttribute('href');
					$wotStats['clan_img'] = $b->find('img',0)->getAttribute('src');
					$wotStats['clan_tag'] = $b->find('span.tag',0)->plaintext;
					$wotStats['clan_name'] = $b->find('span.name',0)->plaintext;
					$wotStats['clan_motto'] = $b->find('p.motto',0)->plaintext;
					$wotStats['clan_days'] = $b->find('td.first',0)->find('span',0)->plaintext;
					$wotStats['clan_enrolled'] = $b->find('td.first',0)->next_sibling()->find('span',0)->getAttribute('data-timestamp');
		
		
				}
				foreach( $html->find('table.t-table-dotted') as $t )
				{
					$table = $t->find('th',0)->plaintext;
		
					if ($table == "Overall Results")
					{
						$wotStats['battles'] = fixStat($t->find('td.td-number-nowidth',0)->plaintext);
						$wotStats['victories'] = fixStat($t->find('td.td-number-nowidth',1)->plaintext);	
						$wotStats['defeats'] = fixStat($t->find('td.td-number-nowidth',2)->plaintext);
						$wotStats['survived'] = fixStat($t->find('td.td-number-nowidth',3)->plaintext);
					}
					if ($table == "Battle Performance")
					{
						$wotStats['destroyed'] = fixStat($t->find('td.td-number-nowidth',0)->plaintext);
						$wotStats['detected'] = fixStat($t->find('td.td-number-nowidth',1)->plaintext);	
						$wotStats['hitratio'] = fixStat($t->find('td.td-number-nowidth',2)->plaintext);
						$wotStats['damage'] = fixStat($t->find('td.td-number-nowidth',3)->plaintext);
						$wotStats['capture'] = fixStat($t->find('td.td-number-nowidth',4)->plaintext);
						$wotStats['defense'] = fixStat($t->find('td.td-number-nowidth',5)->plaintext);
					}
					if ($table == "Experience")
					{
						$wotStats['experience'] = fixStat($t->find('td.td-number-nowidth',0)->plaintext);
						$wotStats['avg_exp'] = fixStat($t->find('td.td-number-nowidth',1)->plaintext);	
						$wotStats['max_exp'] = fixStat($t->find('td.td-number-nowidth',2)->plaintext);
			
					}
		
					if ($table == "Rating")
					{
						$wotStats['global_rating_val'] = fixStat($t->find('td.value',0)->plaintext);
						$wotStats['global_rating_place'] =fixStat($t->find('td.value',1)->plaintext);
			
						$wotStats['vb_val'] = fixStat($t->find('td.value',2)->plaintext);
						$wotStats['vb_place'] =fixStat($t->find('td.value',3)->plaintext);
			
						$wotStats['avg_exp_val'] = fixStat($t->find('td.value',4)->plaintext);
						$wotStats['avg_exp_place'] =fixStat($t->find('td.value',5)->plaintext);

						$wotStats['victories_val'] = fixStat($t->find('td.value',6)->plaintext);
						$wotStats['victories_place'] =fixStat($t->find('td.value',7)->plaintext);
		
						$wotStats['battles_val'] = fixStat($t->find('td.value',8)->plaintext);
						$wotStats['battles_place'] =fixStat($t->find('td.value',9)->plaintext);

						$wotStats['capture_val'] = fixStat($t->find('td.value',10)->plaintext);
						$wotStats['capture_place'] =fixStat($t->find('td.value',11)->plaintext);
						$wotStats['defense_val'] = fixStat($t->find('td.value',12)->plaintext);
						$wotStats['defense_place'] =fixStat($t->find('td.value',13)->plaintext);
						$wotStats['frag_val'] = fixStat($t->find('td.value',14)->plaintext);
						$wotStats['frag_place'] =fixStat($t->find('td.value',15)->plaintext);
						$wotStats['detect_val'] = fixStat($t->find('td.value',16)->plaintext);
						$wotStats['detect_place'] =fixStat($t->find('td.value',17)->plaintext);
						$wotStats['experience_val'] = fixStat($t->find('td.value',18)->plaintext);
						$wotStats['experience_place'] =fixStat($t->find('td.value',19)->plaintext);

			
					}
	
				}
				foreach( $html->find('table.t-statistic') as $t )
				{
					$table = $t->find('th',0)->plaintext;
		
	
		
					if ($table == "Rating")
					{
						$wotStats['global_rating_val'] = fixStat($t->find('td.value',1)->plaintext);
						$wotStats['global_rating_place'] =fixStat($t->find('td.value',2)->plaintext);
			
						$wotStats['vb_val'] = fixStat($t->find('td.value',4)->plaintext);
						$wotStats['vb_place'] =fixStat($t->find('td.value',5)->plaintext);
			
						$wotStats['avg_exp_val'] = fixStat($t->find('td.value',7)->plaintext);
						$wotStats['avg_exp_place'] =fixStat($t->find('td.value',8)->plaintext);

						$wotStats['victories_val'] = fixStat($t->find('td.value',10)->plaintext);
						$wotStats['victories_place'] =fixStat($t->find('td.value',11)->plaintext);
		
						$wotStats['battles_val'] = fixStat($t->find('td.value',13)->plaintext);
						$wotStats['battles_place'] =fixStat($t->find('td.value',14)->plaintext);

						$wotStats['capture_val'] = fixStat($t->find('td.value',16)->plaintext);
						$wotStats['capture_place'] =fixStat($t->find('td.value',17)->plaintext);
						$wotStats['damage_val'] = fixStat($t->find('td.value',19)->plaintext);
						$wotStats['damage_place'] =fixStat($t->find('td.value',20)->plaintext);
			
			
						$wotStats['defense_val'] = fixStat($t->find('td.value',22)->plaintext);
						$wotStats['defense_place'] =fixStat($t->find('td.value',23)->plaintext);
						$wotStats['frag_val'] = fixStat($t->find('td.value',25)->plaintext);
						$wotStats['frag_place'] =fixStat($t->find('td.value',26)->plaintext);
						$wotStats['detect_val'] = fixStat($t->find('td.value',28)->plaintext);
						$wotStats['detect_place'] =fixStat($t->find('td.value',29)->plaintext);
						$wotStats['experience_val'] = fixStat($t->find('td.value',31)->plaintext);
						$wotStats['experience_place'] =fixStat($t->find('td.value',32)->plaintext);

			
					}
		
	
					if (trim($table) == "Vehicles")
					{
						$wotStats['tanks'] = array();
						foreach($t->find('tr') as $r)
						{
							$check = $r->find('td.td-armory-icon');
							if ($check) {
								$tank = array();
								$tank["name"] = $r->find('td.value a.b-gray-link',0)->plaintext;
								$tank["url"] = $r->find('a.b-gray-link',1)->getAttribute('href');
								$tank["image"] = $r->find('img.png',0)->getAttribute('src');
								$tank["level"] = $r->find('span.level',0)->find('a',0)->plaintext;
								$tank["battles"] = fixStat($r->find('td.right',0)->plaintext);
								$tank["victories"] = fixStat($r->find('td.right',1)->plaintext);
								$tank["updated"]=$wotStats["updated"];
								array_push($wotStats["tanks"],$tank);
							
								$t = $db->prepare("insert into tank_stats (account_id,name,url,image,level,battles,victories,account_stats_update) values (:id,:name,:url,:image,:level,:battles,:victories,:updated)");
								$t->execute(array(
									":id"=>$account_id,
									":name"=>$tank["name"],
									":url"=>$tank["url"],
									":image"=>$tank["image"],
									":level"=>romanToInt($tank["level"]),
									":battles"=>$tank["battles"],
									":victories"=>$tank["victories"],
									":updated"=>$wotStats["updated"]
								));
							
							
							}
						}
			
					}
		
	
				}
	
				$html->clear();
				unset($html);
				$wotStats["efficiency"] = calcEfficiencyArray($wotStats);
				if ($wotStats["battles"]) {
				
					//Store Json encoded data
					//$q = $db->prepare("insert into stats (account_id,data,dstamp) values (:id,:data,NOW())");
					//$q->execute(array(":id"=>$account_id,":data"=>json_encode($wotStats)));
				
					//Store split data into new DB TAble
					$q2 = $db->prepare("
					insert into account_stats (
						defeats,
						clan_tag,
						registered,
						updated,
						account_id,
						clan_url,
						clan_img,
						clan_name,
						clan_motto,
						clan_days,
						clan_enrolled,
						battles,
						victories,
						survived,
						destroyed,
						detected,
						hitratio,
						damage,
						capture,
						defense,
						experience,
						avg_exp,
						max_exp,
						global_rating_val,
						global_rating_place,
						vb_val,
						vb_place,
						avg_exp_val,
						avg_exp_place,
						victories_val,
						victories_place,
						battles_val,
						battles_place,
						capture_val,
						capture_place,
						defense_val,
						defense_place,
						frag_val,
						frag_place,
						detect_val,
						detect_place,
						experience_val,
						experience_place,
						efficiency) VALUES (
							:defeats,
							:clan_tag,
							:registered,
							:updated,
							:account_id,
							:clan_url,
							:clan_img,
							:clan_name,
							:clan_motto,
							:clan_days,
							:clan_enrolled,
							:battles,
							:victories,
							:survived,
							:destroyed,
							:detected,
							:hitratio,
							:damage,
							:capture,
							:defense,
							:experience,
							:avg_exp,
							:max_exp,
							:global_rating_val,
							:global_rating_place,
							:vb_val,
							:vb_place,
							:avg_exp_val,
							:avg_exp_place,
							:victories_val,
							:victories_place,
							:battles_val,
							:battles_place,
							:capture_val,
							:capture_place,
							:defense_val,
							:defense_place,
							:frag_val,
							:frag_place,
							:detect_val,
							:detect_place,
							:experience_val,
							:experience_place,
							:efficiency
						) 
					");
					$wotQuery = array();
					$f = explode(",","defeats,clan_tag,registered,
					updated,
					account_id,
					clan_url,
					clan_img,
					clan_name,
					clan_motto,
					clan_days,
					clan_enrolled,
					battles,
					victories,
					survived,
					destroyed,
					detected,
					hitratio,
					damage,
					capture,
					defense,
					experience,
					avg_exp,
					max_exp,
					global_rating_val,
					global_rating_place,
					vb_val,
					vb_place,
					avg_exp_val,
					avg_exp_place,
					victories_val,
					victories_place,
					battles_val,
					battles_place,
					capture_val,
					capture_place,
					defense_val,
					defense_place,
					frag_val,
					frag_place,
					detect_val,
					detect_place,
					experience_val,
					experience_place,
					efficiency");
					foreach ($wotStats as $key=>$value)
					{
						foreach ($f as $b) {
							$wotQuery[trim($b)] = "";
							if ($key == trim($b))
								$wotQuery[":".$key] = $value;
						}
					
					}
					$wotQuery["account_id"] = $account_id;
					$q2->execute($wotQuery);
				
				
				
					$q3 =$db->prepare("update accounts set battles=:battles,wr=:wr,eff=:eff,lastUpdate=NOW() where account_id=:acid");
									if ($wotStats["battles"])
										$wr = round((($wotStats["victories"] / $wotStats["battles"]) * 100),2);
									else
										$wr = 0;	$q3->execute(array(":acid"=>$account_id,":battles"=>$wotStats["battles"],":wr"=>$wr,":eff"=>$wotStats["efficiency"]));
				}		
			
				if ($wotStats["battles"]) {
					$wotStats["victories_p"] = round(($wotStats["victories"] / $wotStats["battles"]) * 100,2);
					$wotStats["defeats_p"] = round(($wotStats["defeats"] / $wotStats["battles"]) * 100,2);
					$wotStats["draws"] = $wotStats["battles"] - $wotStats["victories"] - $wotStats["defeats"];
					$wotStats["draws_p"] =round(($wotStats["draws"] / $wotStats["battles"]) * 100,2);
					$wotStats["survived_p"] = round(($wotStats["survived"] / $wotStats["battles"]) * 100,2);
					$wotStats["destroyed_r"] = round(($wotStats["destroyed"] / $wotStats["battles"]),2);
					$wotStats["damage_r"] = round(($wotStats["damage"] / $wotStats["battles"]),2);
					if ($wotStats["battles"] - $wotStats["survived"])
					$wotStats["kd_r"] = round(($wotStats["destroyed"] / ($wotStats["battles"] - $wotStats["survived"])),2);
					$wotStats["detected_r"] = round(($wotStats["detected"] / $wotStats["battles"]) ,2);
					$wotStats["capture_r"] = round(($wotStats["capture"] / $wotStats["battles"]) ,2);			
					$wotStats["defense_r"] = round(($wotStats["defense"] / $wotStats["battles"]) ,2);
					$wotStats["experience_"] = round(($wotStats["experience"] / $wotStats["battles"]),2);
				}
				$latestStats = $wotStats;
				return $wotStats;//json_decode(json_encode($wotStats));//processStats($wotStats);
	
			} else {
				$html->clear();
				unset($html);
				$latestStats = $lastUpdate;
				return $lastUpdate;
			}
		} else { //!html - some kinda read failure
			echo "Error Loading data for: " .getAccountName($account_id) ."\n";
			echo "CURL Output: $output\n"; 
			
		}
	}
}



function fixStat($string)
{
	$p = explode(" ",trim($string));
	return ereg_replace("[^0-9]", "", $p[0]);
}


function calcPeriodEfficiencyArray($stats,$periodStats)
{
	return calcPeriodEfficiency($stats,$periodStats);
}


// Calculates Effciencey over a period
// TotalStats being the end, and PeriodStats being the beginning of that period
function calcPeriodEfficiency($stats,$periodStats)
{
	if ($stats["battles"]) {
		if ($periodStats["battles"]) {
			$i = 0;
			$tid = 0;
			$level = 0;
	
				foreach ($periodStats["tanks"] as $tank)
				{
					
					//echo $tank["level"] ."<BR>";
					if (!is_numeric($tank["level"]))
						$l = romanToInt($tank["level"]);
					else 
						$l = $tank["level"];
					
					if ($l > 0) {
						if($stats["tanks"][$i]["battles"] - $tank["battles"]) {
							$level +=$l*($stats["tanks"][$tid]["battles"] - $tank["battles"]);
							$i++;
							
						}
					}
					$tid++;
				}
			
		//	echo $level ." - $i<BR>";
			
				if ($i) {
					$pbattles = ($stats["battles"] - $periodStats["battles"]);
					//echo "battles - ". $pbattles."<BR>";
					if ($pbattles) {
						$avg_level = $level / $pbattles;
						$avg_dmg = ($stats["damage"] - $periodStats["damage"]) / $pbattles;
						$avg_frags = ($stats["destroyed"] - $periodStats["destroyed"]) / $pbattles;
						$avg_spots = ($stats["detected"] - $periodStats["detected"]) / $pbattles;
						$avg_cap = ($stats["capture"] - $periodStats["capture"])  / $pbattles;
						$avg_def = ($stats["defense"] - $periodStats["defense"]) / $pbattles;
						return (round(
							($avg_dmg * (10 / $avg_level)*(.15 + $avg_level/50)) +
							($avg_frags *(.35-$avg_level/50)*1000) +
							($avg_spots * 200) +
							($avg_cap *150) +
							($avg_def *150)
							));
					}
				}
		
		}		
	}
	return 0;
}


function calcEfficiencyArray($stats)
{
	//echo $stats["battles"];
	return calcEfficiency($stats);
}

function calcEfficiency($stats)
{
//	echo $stats->battles ."\n";
	if ($stats)
	if ($stats["battles"]) {
		$i = 0;
		$level = 0;
		foreach ($stats["tanks"] as $tank)
		{
			$l = romanToInt($tank["level"]);
			if ($l > 0) {
			$level +=$l * $tank["battles"];// romanToInt($tank->level);// * ($tank->battles);
			$i++;
			}
		}
		if ($i) {
			$avg_level = $level / $stats["battles"];
			$avg_dmg = $stats["damage"] / $stats["battles"];
			$avg_frags = $stats["destroyed"] / $stats["battles"];
			$avg_spots = $stats["detected"] / $stats["battles"];
			$avg_cap = $stats["capture"]  / $stats["battles"];
			$avg_def = $stats["defense"] / $stats["battles"];
			//echo "$avg_frags<BR>$avg_dmg<BR>$avg_spots<BR>$avg_def<BR>$avg_cap<BR>$avg_level<BR>";
			return (round(
				($avg_dmg * (10 / $avg_level)*(.15 + $avg_level/50)) +
				($avg_frags *(.35-$avg_level/50)*1000) +
				($avg_spots * 200) +
				($avg_cap *150) +
				($avg_def *150)
				));
		}
	}
	return 0;
	
}


function romanToInt($roman)
{
	$romans = array(
	    'M' => 1000,
	    'CM' => 900,
	    'D' => 500,
	    'CD' => 400,
	    'C' => 100,
	    'XC' => 90,
	    'L' => 50,
	    'XL' => 40,
	    'X' => 10,
	    'IX' => 9,
	    'V' => 5,
	    'IV' => 4,
	    'I' => 1,
	);

	//$roman = 'MMMCMXCIX';
	$result = 0;

	foreach ($romans as $key => $value) {
	    while (strpos($roman, $key) === 0) {
	        $result += $value;
	        $roman = substr($roman, strlen($key));
	    }
	}
	return $result;
}


function loadGeneralStats($account_id)
{
	global $site;
	$stats = loadStatistics($account_id);
	if ($stats["battles"]) {
		$stats60 = loadStatistics($account_id,1);
		$stats24 = loadStatistics($account_id,24);
		$stats7 = loadStatistics($account_id,168);
		$stats30 = loadStatistics($account_id,768);
		date_default_timezone_set('America/Los_Angeles');
		$rs["currentStats"] = $stats;
		$rs["registered"] = date("Y-m-d g:i A",$stats["registered"]) . " PST";
		$rs["lastUpdate"] = date("Y-m-d g:i A",$stats["updated"]) . " PST";
		$rs["name"] = getAccountName($account_id);
	
		$rs["eff"] = calcEfficiency($stats);
		$rs["eff_60"] = calcPeriodEfficiency($stats,$stats60);
		$rs["eff_24"] = calcPeriodEfficiency($stats,$stats24);
		$rs["eff_7"] = calcPeriodEfficiency($stats,$stats7);
		$rs["eff_30"] = calcPeriodEfficiency($stats,$stats30);
	
	
	
		if ($stats["clan_name"]) {
			$rs["clan_tag"] = "Member of <a href=/?clanName=" .trim($stats["clan_tag"],"[]") .">".$stats["clan_tag"]."</a> for ". $stats["clan_days"] ." days.";
			$rs["clan_img"] = "http://$site/" . $stats["clan_img"];
		}
		//Totals
	
		$rs["battles"] = $stats["battles"];//->battles_count;
		if ($rs["battles"]) {
		$rs["victory"] = round( (( $stats["victories"]) / $rs["battles"]) *100,2) ;
		$rs["kd"] =  round($stats["destroyed"] / ($rs["battles"] - $stats["survived"]) ,2);
		$rs["dmg"] = round($stats["damage"] / $rs["battles"],2);
		$rs["exp"] = $stats["avg_exp"];
		} else {
			$rs["victory"] = 0;
			$rs["kd"] = 0;
			$rs["dmg"] = 0;
			$rs["exp"] = 0;
		}
	
	
		// Past 30days
	
		if ($stats30["battles"] && $stats30["battles"] < $rs["battles"]) {
			$rs["battles_30"] = $rs["battles"] - $stats30["battles"];
	
			$rs["victory_30"] = round( (($stats["victories"]- $stats30["victories"]) /$rs["battles_30"]) * 100,2) ;
		
			if (($rs["battles_30"] - ($stats["survived"] - $stats30["survived"])))
				$rs["kd_30"] =  round( ($stats["destroyed"] - $stats30["destroyed"]) / ($rs["battles_30"] - ($stats["survived"] - $stats30["survived"])),2);
			else 
				$rs["kd_30"] = round( ($stats["destroyed"] - $stats30["destroyed"]),2);
	//	v
		
		//	$rs["kd_30"] =  round( ($stats->destroyed - $stats30->destroyed) / (($stats->survivied - $stats30->survived)),2);
			$rs["dmg_30"] = round(($stats["damage"] - $stats30["damage"]) / $rs["battles_30"],2);
			$rs["exp_30"] = round(( $stats["experience"] - $stats30["experience"] ) / $rs["battles_30"],2);
		} else {
				$rs["battles_30"] = 0;
				$rs["victory_30"] = 0;
				$rs["kd_30"] = 0;
				$rs["dmg_30"] = 0;
				$rs["exp_30"] = 0;		
		}
		// Past 7days
	
		if ($stats7["battles"] && $stats7["battles"] < $rs["battles"]) {
			$rs["battles_7"] = $rs["battles"] - $stats7["battles"];
	
			$rs["victory_7"] = round( (($stats["victories"]- $stats7["victories"]) /$rs["battles_7"]) * 100,2) ;
		
			if (($rs["battles_7"] - ($stats["survived"] - $stats7["survived"])))
				$rs["kd_7"] =  round( ($stats["destroyed"] - $stats7["destroyed"]) / ($rs["battles_7"] - ($stats["survived"] - $stats7["survived"])),2);
			else 
				$rs["kd_7"] = round( ($stats["destroyed"] - $stats7["destroyed"]),2);
		
			//$rs["kd_7"] =  round( ($stats->destroyed - $stats7->destroyed) / (($stats->survivied - $stats7->survived)),2);
			$rs["dmg_7"] = round(($stats["damage"] - $stats7["damage"]) / $rs["battles_7"],2);
			$rs["exp_7"] = round(( $stats["experience"] - $stats7["experience"] ) / $rs["battles_7"],2);
		} else {
				$rs["battles_7"] = 0;
				$rs["victory_7"] = 0;
				$rs["kd_7"] = 0;
				$rs["dmg_7"] = 0;
				$rs["exp_7"] = 0;		
		}

		if ($stats24["battles"] && $stats24["battles"] < $rs["battles"]) {
			$rs["battles_24"] = $rs["battles"] - $stats24["battles"];
	
			$rs["victory_24"] = round( (($stats["victories"] - $stats24["victories"]) /$rs["battles_24"]) * 100,2) ;
			
			if (($rs["battles_24"] - ($stats["survived"] - $stats24["survived"])))
				$rs["kd_24"] =  round( ($stats["destroyed"] - $stats24["destroyed"]) / ($rs["battles_24"] - ($stats["survived"] - $stats24["survived"])),2);
			else 
				$rs["kd_24"] = round( ($stats["destroyed"] - $stats24["destroyed"]),2);
			
			//$rs["kd_24"] =  round( ($stats["destroyed - $stats24["destroyed) / (($stats["survivied - $stats24["survived)),2);
			$rs["dmg_24"] = round(($stats["damage"] - $stats24["damage"]) / $rs["battles_24"],2);
			$rs["exp_24"] = round( ($stats["experience"] - $stats24["experience"] ) / $rs["battles_24"],2);
		} else {
				$rs["battles_24"] = 0;
				$rs["victory_24"] = 0;
				$rs["kd_24"] = 0;
				$rs["dmg_24"] = 0;
				$rs["exp_24"] = 0;		
		}

		if ($stats60["battles"] && $stats60["battles"] < $rs["battles"]) {
			$rs["battles_60"] = $rs["battles"] - $stats60["battles"];
		
			$rs["victory_60"] = round( (($stats["victories"] - $stats60["victories"]) / $rs["battles_60"]) * 100,2) ;
			if (($rs["battles_60"] - ($stats["survived"] - $stats60["survived"])))
				$rs["kd_60"] =  round( ($stats["destroyed"] - $stats60["destroyed"]) / ($rs["battles_60"] - ($stats["survived"] - $stats60["survived"])),2);
			else 
				$rs["kd_60"] = round( ($stats["destroyed"] - $stats60["destroyed"]),2);
			
			$rs["dmg_60"] = round(($stats["damage"] - $stats60["damage"]) / $rs["battles_60"],2);
			$rs["exp_60"] = round(( $stats["experience"] - $stats60["experience"] ) / $rs["battles_60"],2);
		} else {
				$rs["battles_60"] = 0;
				$rs["victory_60"] = 0;
				$rs["kd_60"] = 0;
				$rs["dmg_60"] = 0;
				$rs["exp_60"] = 0;		
		}
	
		return $rs;
	} 
	return false;
}


function topTanks($stat)
{
	$topTanks = array();
	foreach ($stat['tanks'] as $tank) {
		$topTanks[$tank["battles"]][] = $tank;
	}
	asort($topTanks);
	
	$num = count($topTanks);
	
	if ($num > 20)
		$num = 20;
	$i = 0;
	$tops = array();
	foreach ($topTanks as $top) {
		foreach ($top as $t){
			if ($i < $num) {
				//$i++;
				$f = 0;
				foreach ($tops as $tt)
					if ($tt["name"] == $t["name"])
						$f=1;
				if (!$f) {
					array_push($tops,$t);
					$i++;
				}
			}
		}
	}
	
	return($tops);
	
	
}



// Grabs stats and adjusts them for a specified period
// Interval can be 
// d - days
// h - hours
// w - weeks
// m - months
//
// getStatPeriod(name, 0,1,d)
// loads stats for name between 0 days and 1 day ago


function getStatPeriod($account_id, $period_start,$period_end,$interval="d")
{
	global $db;
	if ($interval == "d")
		$intval = " DAY ";
	else if ($interval == "h")
		$intval = " HOUR ";
	else if ($interval == "w")
		$intval = " WEEK ";
	else if ($interval == "m")
			$intval = " MONTH ";
	else
		$intval = " DAY ";
		
	//$account_id = getPlayerIdFromNick($_GET["name"]);
	$statsA = array();
	$statsB = array();
	$statsC = array();
	// Try to load stats for given period start
	if ($interval == "d") { // 1001775453
		$q = $db->prepare("select * from account_stats where account_id=:aid and updateTime <= DATE_SUB(CONCAT(CURDATE(),' 23:59:59'), INTERVAL :t $intval) and updateTime >= DATE_SUB(CONCAT(CURDATE(),' 23:59:59'), INTERVAL :tb $intval) order by updateTime DESC limit 1");
			$q->execute(array(":aid"=>$account_id,":t"=>$period_start,":tb"=>$period_start+1));
	 } else {
	$q = $db->prepare("select * from account_stats where account_id=:aid and updateTime <= DATE_SUB(NOW(),INTERVAL :t $intval) order by updateTime DESC limit 1");
		$q->execute(array(":aid"=>$account_id,":t"=>$period_start));
	}
		if ($q->rowCount()) { 
			$statsA = $q->fetch();
				$q2= $db->prepare("select * from tank_stats where account_stats_update=:update and account_id=:acid");
				$q2->execute(array(":update"=>$statsA["updated"],":acid"=>$account_id));
				$statsA["tanks"] = array();
				while($t=$q2->fetch()){
						array_push($statsA["tanks"],$t);
				}
		} else {
			//echo "StatsA<BR>";
			$statsA = false;
		}
	
	// Try to load stats for period end
	// 
		if ($period_start != $period_end) {
			
			if ($interval =="d") {
			$q = $db->prepare("select * from account_stats where account_id=:aid and updateTime <= DATE_SUB(CONCAT(CURDATE(),' 23:59:59'), INTERVAL :t $intval) and updateTime >= DATE_SUB(CONCAT(CURDATE(),' 23:59:59'), INTERVAL :tb $intval) order by updateTime DESC limit 1");
				$q->execute(array(":aid"=>$account_id,":t"=>$period_end,":tb"=>$period_end+1));
			} else {
					$q = $db->prepare("select * from account_stats where account_id=:aid and updateTime <= DATE_SUB(NOW(), INTERVAL :t $intval) order by updateTime DESC limit 1");
					$q->execute(array(":aid"=>$account_id,":t"=>$period_end));
	 }
					if ($q->rowCount()) { 
						$statsB = $q->fetch();
							$q2= $db->prepare("select * from tank_stats where account_stats_update=:update and account_id=:acid");
							$q2->execute(array(":update"=>$statsB["updated"],":acid"=>$account_id));
							$statsB["tanks"] = array();
							while($t=$q2->fetch()){
									array_push($statsB["tanks"],$t);
							}
					} else {
					//	echo "$period_start - $period_end - $intval = StatsB<BR>";
						$statsB = false;
					}
		}
		
		if (!$statsA or !$statsB)
			return false;
		
				
		// ok we've made it this far, we should have valid data in StatsA and statsB
		$statsC = $statsA;
		$tanks = array();
		$statsC["tanks"] = array();
		foreach($statsA as $key=>$val)
		{
			if (is_numeric($val))
				$statsC["$key"] = ($statsA["$key"] - $statsB["$key"]);
				
				
			if ($key == "tanks")
			{
				foreach($statsA["tanks"] as $keyb=>$valb) {
				
						$tank = array();
						if ($statsA["tanks"][$keyb]["name"] == $statsB["tanks"][$keyb]["name"]) {
							//echo "Processing " .$statsA["tanks"][$keyb]["name"] ."<BR>";
							foreach ($statsA["tanks"][$keyb] as $ky=>$vy)
							{
								if (!is_numeric($ky))
					
								if(($ky == "battles") or ($ky == "victories")) {
								//	echo "$ky == battles or victories<BR>";
									$tank[$ky] = $statsA["tanks"][$keyb][$ky] - $statsB["tanks"][$keyb][$ky];
								//	echo $tank[$ky] . "<BR>";
								//	if ($k != 0)
								//	echo "$ky => " . $tank["$ky"] ." = ". $statsA["tanks"][$keyb][$ky]." - ".$statsB["tanks"][$keyb][$ky]."<BR>";
								}
								else
									$tank["$ky"] = $statsA["tanks"][$keyb][$ky];

							}
							//echo "<pre>";
							//	print_r($tank);
							//echo "</pre>";
						//	print_r($tank);
							array_push($tanks,$tank);
						}
						
						
				}
				
			}
				
		}
	
	//	echo "<pre>";
	//		print_r($statsC["tanks"]);
	//	echo "</pre>";
	
			$statsC["damage"] = $statsA["damage"] - $statsB["damage"];
			//$statsC["defeat"] = $statsA["damage"] - $statsB["damage"];
			$statsC["efficiency"] = $statsA["efficiency"];
			$statsC["avg_exp"] = $statsA["avg_exp"];
			$statsC["max_exp"] = $statsA["max_exp"];
			$statsC["hitratio"] = $statsA["hitratio"];
			
			// Lets calculate some common ratio's just to make display easier
			if ($statsC["battles"]) {
				$statsC["victories_p"] = round(($statsC["victories"] / $statsC["battles"]) * 100,2);
				$statsC["defeats_p"] = round(($statsC["defeats"] / $statsC["battles"]) * 100,2);
				$statsC["draws"] = $statsC["battles"] - $statsC["victories"] - $statsC["defeats"];
				$statsC["draws_p"] =round(($statsC["draws"] / $statsC["battles"]) * 100,2);
				$statsC["survived_p"] = round(($statsC["survived"] / $statsC["battles"]) * 100,2);
				$statsC["destroyed_r"] = round(($statsC["destroyed"] / $statsC["battles"]),2);
				$statsC["damage_r"] = round(($statsC["damage"] / $statsC["battles"]),2);
				
				if ($statsC["battles"] - $statsC["survived"])
				$statsC["kd_r"] = round(($statsC["destroyed"] / ($statsC["battles"] - $statsC["survived"])),2);
				$statsC["detected_r"] = round(($statsC["detected"] / $statsC["battles"]) ,2);
				$statsC["capture_r"] = round(($statsC["capture"] / $statsC["battles"]) ,2);			
				$statsC["defense_r"] = round(($statsC["defense"] / $statsC["battles"]) ,2);
				$statsC["experience_r"] = round(($statsC["experience"] / $statsC["battles"]),2);
		
					$statC["tanks"] = array();
					$statsC["tanks"] = $tanks;
			
		}	
		
		
//		print_r($statsA);
	//	print_r($statsB);
		return $statsC;
}

//Valid Stats - based on totals
// "wr" == win rate
// "battles" == Battles
// "kd" == Kill to Death Ratio
// "dmg" == average damage
// "exp" == average experience
// "efficiency" == efficiency rating
function printStatOverDays($days,$account_name,$stat)
{
	global $db;
	$account_id = getPlayerIdFromNick($_GET["name"]);
	$i = 0;
	$stats = array();
//	$q = $db->prepare("select * from account_stats where account_id=:aid updateTime DESC limit 1");
	//$q->execute(array(":aid"=>$account_id);

//	$latest = $q->fetch();
	if(0) // old logic
	while ($i < $days)
	{
		if ($i == 0)
		{
					$q = $db->prepare("select * from account_stats where account_id=:aid order by updateTime DESC limit 1");
					$q->execute(array(":aid"=>$account_id));
		}else {
			$q = $db->prepare("select * from account_stats where account_id=:aid and updateTime <= NOW() - INTERVAL :t DAY order by updateTime DESC limit 1");
			$q->execute(array(":aid"=>$account_id,":t"=>$i));
		}
		if ($q->rowCount()) { 
			$result = $q->fetch();
			array_push($stats,$result);	
		}
		//return false;
		$i++;
	}
	
	
	$stats = array();
	
	// new logic, grab stats
	$i = 1;
	while($i <= $days)
	{
		
		array_push($stats,getStatPeriod($account_id, $i,$i-1,"d"));
		
		$i++;	
	}
	
	
	
	
	$last = array();
	$i = 0;
	$ar="";
	while($i < $days)
	{
		if (isset($stats[$i]))
			if ($stats[$i]["battles"]) {
				// We have data for this period
				// "wr" == win rate
				// "battles" == Battles
				// "kd" == Kill to Death Ratio
				// "dmg" == average damage
				// "exp" == average experience
				// "efficiency" == efficiency rating
				if ($stat == "wr")
					$ar .=  round(($stats[$i]["victories"] / $stats[$i]["battles"])*100,2) .",";
				else if ($stat == "battles")
					$ar.= $stats[$i]["battles"].",";
				else if ($stat == "kd") {
					if  ($stats[$i]["battles"] - $stats[$i]["survived"])
						$ar.= round($stats[$i]["destroyed"] / ($stats[$i]["battles"] - $stats[$i]["survived"]),2).",";
					else
						$ar .= $stats[$i]["destroyed"] .",";
				} else if ($stat == "dmg")
				  //	if ($stats[$i]["battles"])
				if  ($stats[$i]["battles"])
							$ar .= round(($stats[$i]["damage"] / $stats[$i]["battles"] ),2)   .",";
						else
							$ar .="0,";
				else if ($stat == "exp")
						$ar .= $stats[$i]["experience"].",";
				else if ($stat == "efficiency")
						$ar .= $stats[$i]["efficiency"].",";
				
			
			} else 
				$ar .= "0,";
		else 
			$ar .= "0,";
		$i++;
	}

	$ar = trim($ar,",");
	echo $ar;

}




//Valid Stats - based on totals
// "wr" == win rate
// "battles" == Battles
// "kd" == Kill to Death Ratio
// "dmg" == average damage
// "exp" == average experience
// "efficiency" == efficiency rating
function printStatOverPeriod($days,$account_name,$stat,$period="h")
{
	global $db,$statCache;
	$account_id = getPlayerIdFromNick($_GET["name"]);
	$i = 0;
	$stats = array();
	if (!is_array($statCache[$period]))
		$statCache[$period]=array();
	// new logic, grab stats
	$i = 1;
	while($i <= $days)
	{
			if (!is_array($statCache[$period][$i])) {
				$statz = getStatPeriod($account_id, $i-1,$i,"$period");
		  	array_push($stats,$statz);
				$statCache[$period][$i] = $statz; 
			} else
				array_push($stats,$statCache[$period][$i]);
			$i++;	
	}
	
	
	
	
	$last = array();
	$i = 0;
	$ar="";
	while($i < $days)
	{
		if (isset($stats[$i]))
			if ($stats[$i]["battles"]) {
				// We have data for this period
				// "wr" == win rate
				// "battles" == Battles
				// "kd" == Kill to Death Ratio
				// "dmg" == average damage
				// "exp" == average experience
				// "efficiency" == efficiency rating
				if ($stat == "wr")
					$ar .=  round(($stats[$i]["victories"] / $stats[$i]["battles"])*100,2) .",";
				else if ($stat == "survival")
						$ar .=  round(($stats[$i]["survived"] / $stats[$i]["battles"])*100,2) .",";
				else if ($stat == "battles")
					$ar.= $stats[$i]["battles"].",";
				else if ($stat == "kd") {
					if  ($stats[$i]["battles"] - $stats[$i]["survived"])
						$ar.= round($stats[$i]["destroyed"] / ($stats[$i]["battles"] - $stats[$i]["survived"]),2).",";
					else
						$ar .= $stats[$i]["destroyed"] .",";
				} else if ($stat == "dmg")
				  	if ($stats[$i]["battles"])
							$ar .= round(($stats[$i]["damage"] / $stats[$i]["battles"] ),2).",";
						else
							$ar .="0,";
				else if ($stat == "exp")
						if ($stats[$i]["battles"])
							$ar .= round(($stats[$i]["experience"] / $stats[$i]["battles"] ),2).",";
						else
							$ar .="0,";
						
				else if ($stat == "efficiency")
						$ar .= $stats[$i]["efficiency"].",";
				
			
			} else 
				$ar .= "0,";
		else 
			$ar .= "0,";
		$i++;
	}

	$ar = trim($ar,",");
	echo $ar;

}



function printPast24Hours()
{
	$i=0;
	$str = "";
	while ($i < 24)
	{
		$str .="'$i',";
		$i++;
	}
	echo $str;
}
function printPast7days(){
	$m= date("m");
	$de= date("d");
	$y= date("Y");
	$str="";
	for($i=0; $i<=7; $i++){
	$str .= date("'D',",mktime(0,0,0,$m,($de-$i),$y)); 
	//echo "<br>";
	}
	echo trim($str,",");
}

function printPast30Days(){
	$m= date("m");
	$de= date("d");
	$y= date("Y");
	$str="";
	for($i=0; $i<=30; $i++){
	//$str .= date("'j',",mktime(0,0,0,$m,($de-$i),$y)); 
	$str.="' ',";
	//echo "<br>";
	}
	echo trim($str,",");
}

function getLastUpdates($number=25)
{
	global $db;
	if (!is_numeric($number))
		return array();
	$q = $db->prepare("select * from account_stats order by updateTime DESC limit $number");
	$q->execute();
	$results = array();
	if ($q->rowCount()) { 
			while ($result = $q->fetch()) {
				$result["name"]=getAccountName($result["account_id"]);
				array_push($results,$result);
			}
	}
	return $results;
}



function prettyStats($stats,$latest,$html=true)
{
	
	foreach ($latest as $key=>$val) 
	{
		$stats[$key."_color"] = "default";
		if (isset($stats[$key])) {
			if (strstr($key,"_p")){
				if ($key !="draws_p")
					if ($key !="defeats_p") {
						if ($stats[$key] >= $latest[$key])
							$class="green";
						else
							$class="red";
					} else {
						if ($stats[$key] >= $latest[$key])
							$class="red";
						else
							$class="green";
					}
				
			
				if ($html)
					$stats[$key] ="<span class=\"$class\">". $stats["$key"]."%</span>";
				else {
					$stats[$key] = $stats["$key"] ."%";
					$stats[$key."_color"] =$class;
				}
			
					
			}
			if (strstr($key,"_r")){
				if ($stats[$key] >= $latest[$key])
					$class="green";
				else
					$class="red";
					
			if ($html)
				$stats[$key] ="<span class=\"$class\">".  $stats["$key"]."</span>";
				else 
					$stats[$key."_color"] = $class;
			}
		} else {
			if (strstr($key,"_p"))
				$stats[$key] = "-";
			else if (strstr($key,"_r"))
				$stats[$key] = "-";
			else
				$stats[$key] = "0";
		
		}
	}

	return $stats;
}


function loadOld()
{
	global $db;
	$q = $db->prepare("select * from accounts");
	$q->execute();

	if ($q->rowCount()) { 
			//Stats Found!
			while ($result = $q->fetch()) {
				echo "Loading data for " . $result["account_name"];
				importOldData($result["account_id"]);
				echo "\n";
			}
	}
}


function importOldData($account_id){
	global $db;
//account_id = "1001775453";
	$old = array();
	$q = $db->prepare("select * from stats where account_id=:acid ");
	$q->execute(array(":acid"=>$account_id));
	$results = array();
	if ($q->rowCount()) { 
			//Stats Found!
			while ($result = $q->fetch()) {
			
				array_push($old,$result);
			}
	}
	if ($old) {
		// we have all of the old results, lets merge them
		
		
		//Convert object to array
		
		foreach ($old as $val) {
			$first = json_decode($val["data"]);
			$wotStats = array();
			
			foreach ($first as $k=>$v)
			{
				if ($k != "tanks")	
					$wotStats["$k"]= $v;
				else {
						//$tank = array();
						$wotStats["tanks"] = array();
					foreach ($v as $kt=>$vt)
					{
							$tank = array();
							$tank["name"] = $vt->name;
							$tank["url"] = $vt->url;
							$tank["image"] = $vt->image;
							$tank["level"] = romanToInt($vt->level);
							$tank["battles"] = $vt->battles;
							$tank["victories"] = $vt->victories;
							$tank["name"] = $vt->name;
							$tank["account_stats_update"] = $wotStats["updated"];
							
								
							array_push($wotStats["tanks"],$tank);
					}
						
				}
			}
		
							//print_r($wotStats);
							
							
			$q = $db->prepare("select * from account_stats where account_id=:acid and updated=:updated limit 1");
			$q->execute(array(":acid"=>$account_id,":updated"=>$wotStats["updated"]));
			$results = array();
			if ($q->rowCount()) { 
					//Stats Found!
					echo ".";
			}	 else {
					echo "+";
		//	}
			
						//	echo $["updateTime"];
					//exit(0);
			
				//	if (0) {
		
		
						//Store split data into new DB TAble
						$q2 = $db->prepare("
						insert into account_stats (updateTime,
							defeats,
							clan_tag,
							registered,
							updated,
							account_id,
							clan_url,
							clan_img,
							clan_name,
							clan_motto,
							clan_days,
							clan_enrolled,
							battles,
							victories,
							survived,
							destroyed,
							detected,
							hitratio,
							damage,
							capture,
							defense,
							experience,
							avg_exp,
							max_exp,
							global_rating_val,
							global_rating_place,
							vb_val,
							vb_place,
							avg_exp_val,
							avg_exp_place,
							victories_val,
							victories_place,
							battles_val,
							battles_place,
							capture_val,
							capture_place,
							defense_val,
							defense_place,
							frag_val,
							frag_place,
							detect_val,
							detect_place,
							experience_val,
							experience_place,
							efficiency) VALUES (
								FROM_UNIXTIME(:updateTime),
								:defeats,
								:clan_tag,
								:registered,
								:updated,
								:account_id,
								:clan_url,
								:clan_img,
								:clan_name,
								:clan_motto,
								:clan_days,
								:clan_enrolled,
								:battles,
								:victories,
								:survived,
								:destroyed,
								:detected,
								:hitratio,
								:damage,
								:capture,
								:defense,
								:experience,
								:avg_exp,
								:max_exp,
								:global_rating_val,
								:global_rating_place,
								:vb_val,
								:vb_place,
								:avg_exp_val,
								:avg_exp_place,
								:victories_val,
								:victories_place,
								:battles_val,
								:battles_place,
								:capture_val,
								:capture_place,
								:defense_val,
								:defense_place,
								:frag_val,
								:frag_place,
								:detect_val,
								:detect_place,
								:experience_val,
								:experience_place,
								:efficiency
							) 
						");
						$wotQuery = array();
						$f = explode(",","defeats,clan_tag,registered,
						updated,
						account_id,
						clan_url,
						clan_img,
						clan_name,
						clan_motto,
						clan_days,
						clan_enrolled,
						battles,
						victories,
						survived,
						destroyed,
						detected,
						hitratio,
						damage,
						capture,
						defense,
						experience,
						avg_exp,
						max_exp,
						global_rating_val,
						global_rating_place,
						vb_val,
						vb_place,
						avg_exp_val,
						avg_exp_place,
						victories_val,
						victories_place,
						battles_val,
						battles_place,
						capture_val,
						capture_place,
						defense_val,
						defense_place,
						frag_val,
						frag_place,
						detect_val,
						detect_place,
						experience_val,
						experience_place,
						efficiency");
						foreach ($wotStats as $key=>$value)
						{
							foreach ($f as $b) {
								$wotQuery[trim($b)] = "";
								if ($key == trim($b))
									$wotQuery[":".$key] = $value;
							}
				
						}
						$wotQuery[":updateTime"]=$wotStats["updated"];
			
						//Import the tank data too
						$wotQuery["account_id"] = $account_id;
						$q2->execute($wotQuery);
						foreach($wotStats["tanks"] as $tank)
						{

					
								$t = $db->prepare("insert into tank_stats (account_id,name,url,image,level,battles,victories,account_stats_update) values (:id,:name,:url,:image,:level,:battles,:victories,:updated)");
								$t->execute(array(
									":id"=>$account_id,
									":name"=>$tank["name"],
									":url"=>$tank["url"],
									":image"=>$tank["image"],
									":level"=>romanToInt($tank["level"]),
									":battles"=>$tank["battles"],
									":victories"=>$tank["victories"],
									":updated"=>$wotStats["updated"]
								));
					
					
							
						}
					} // if 0
		
		}// if old
	}
	return $results;
}




function printClassPercentage($name,$class,$periodStat=false)
{
	global $db,$latestStats,$tankList;
	$name =ereg_replace("[^A-Za-z0-9_]", "", $name );
	$account_id = getPlayerIdFromNick($name);
	if($periodStat)
		$stats = $periodStat;
	else if ($latestStats)
		$stats = $latestStats;
	else
		$stats = loadStatistics($account_id);
	
	
	//$
	$latestStats = $stats;
	
	
	//figure out our averages per class / country
	$countries = array("Soviet Vehicles",
											"German Vehicles",
											"USA Vehicles",
											"French Vehicles",
											"Chinese Vehicles",
											"UK Vehicles"
											);
	$classes = array("Light Tanks",
	"Heavy Tanks",
	"Medium Tanks",
	"Tank Destroyers",
	"SPGs"
	);
	$tiers=array('1','2','3','4','5','6','7','8','9','10');
	
	
	if (!$tankList)
	{
		//load the tank list from the db
		$q = $db->prepare("select * from tank_list");
		$q->execute();
		$tanks = array();
		if ($q->rowCount()) { 
				//Stats Found!
				while ($result = $q->fetch()) {
					$tankList[$result["name"]] = $result;
				}
		} 
	}
	//$tankList = $tanks;
	$tanks = $tankList;
if(!is_array($latestStats["class_tank_list"])) {
				foreach ($stats["tanks"] as $tank)
				{
					$tanks[$tank["name"]]["battles"] = $tank["battles"];
				}
				$latestStats["class_tank_list"] = array();
				$latestStats["tier_tank_list"] = array();
				foreach ($classes as $c) {
					foreach($countries as $s) {
						$latestStats["class_tank_list"][$c][$s] = 0;
						
					}
				}
					foreach ($classes as $c) {
						foreach($tiers as $s) {
							$latestStats["tier_tank_list"][$c][$s] = 0;
						}
					}
				
				foreach ($tanks as $tank) {
					$latestStats["class_tank_list"][$tank["class"]][$tank["country"]] += round( ( $tank["battles"] / $stats["battles"]) * 100,2);
					$latestStats["tier_tank_list"][$tank["class"]][$tank["level"]] += round( ( $tank["battles"] / $stats["battles"]) * 100,2);
			//	echo "---" . $latestStats["class_tank_list"][$tank["class"]][$tank["country"]] ."----";
				}			
	}
	
	

	
	
	
	
	
	
	
	$output ="";
	
	
	
	if ($class =="h_t") {
		//print_r($latestStats["class_tank_list"]);
		foreach ($latestStats["tier_tank_list"]["Heavy Tanks"] as  $i)
			$output .=$i.",";
			
	}else if ($class =="m_t") {

			foreach ($latestStats["tier_tank_list"]["Medium Tanks"] as $i)
				$output .=$i.",";

		}
		else if ($class =="l_t") {

			foreach ($latestStats["tier_tank_list"]["Light Tanks"] as $i)
				$output .=$i.",";

		}
		else if ($class =="s_t") {

			foreach ($latestStats["tier_tank_list"]["SPGs"] as $i)
				$output .=$i.",";

		}
		else if ($class =="t_t") {

			foreach ($latestStats["tier_tank_list"]["Tank Destroyers"] as $i)
				$output .=$i.",";

		}
		else if ($class =="tt_t") {

			foreach ($latestStats["tier_tank_list"]["Tank Destroyers"] as $i)
				$output +=$i;

		}
		else if ($class =="st_t") {

			foreach ($latestStats["tier_tank_list"]["SPGs"] as $i)
				$output +=$i;

		}	else if ($class =="lt_t") {

				foreach ($latestStats["tier_tank_list"]["Light Tanks"] as $i)
					$output +=$i;

		}	else if ($class =="mt_t") {

					foreach ($latestStats["tier_tank_list"]["Medium Tanks"] as $i)
						$output +=$i;

		}	else if ($class =="ht_t") {

					foreach ($latestStats["tier_tank_list"]["Heavy Tanks"] as $i)
						$output +=$i;

		}
	
	
	
	
	
	
	if ($class =="h") {
		//print_r($latestStats["class_tank_list"]);
		foreach ($latestStats["class_tank_list"]["Heavy Tanks"] as  $i)
			$output .=$i.",";
			
	}else if ($class =="m") {

			foreach ($latestStats["class_tank_list"]["Medium Tanks"] as $i)
				$output .=$i.",";

		}
		else if ($class =="l") {

			foreach ($latestStats["class_tank_list"]["Light Tanks"] as $i)
				$output .=$i.",";

		}
		else if ($class =="s") {

			foreach ($latestStats["class_tank_list"]["SPGs"] as $i)
				$output .=$i.",";

		}
		else if ($class =="t") {

			foreach ($latestStats["class_tank_list"]["Tank Destroyers"] as $i)
				$output .=$i.",";

		}
		else if ($class =="tt") {

			foreach ($latestStats["class_tank_list"]["Tank Destroyers"] as $i)
				$output +=$i;

		}
		else if ($class =="st") {

			foreach ($latestStats["class_tank_list"]["SPGs"] as $i)
				$output +=$i;

		}	else if ($class =="lt") {

				foreach ($latestStats["class_tank_list"]["Light Tanks"] as $i)
					$output +=$i;

		}	else if ($class =="mt") {

					foreach ($latestStats["class_tank_list"]["Medium Tanks"] as $i)
						$output +=$i;

		}	else if ($class =="ht") {

					foreach ($latestStats["class_tank_list"]["Heavy Tanks"] as $i)
						$output +=$i;

		}
	//print_r($latestStats["class_tank_list"]);
	echo trim($output,",");
	
}



function createForumSignature($account_id,$name,$dark=0,$fg=false,$bg=false)
{
	global $server;
		$name =ereg_replace("[^A-Za-z0-9_]", "", $name );
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822,strtotime(" 1 hour")));
		
		$fg_color = html2rgb($fg);
		$bg_color = html2rgb($bg);
		
		if (!$dark)
			$img = "forum_sig/$server-$account_id.png";
		else if ($dark == 2)
			$img = "forum_sig/dark-$server-$account_id.png";
		else if ($dark == 3){
			if ($fg_color and $bg_color)
			{
				$img = "forum_sig/color-".$fg_color[0].$fg_color[1].$fg_color[2]."-".$bg_color[0].$bg_color[1].$bg_color[2]."-$server-$account_id.png";
			}
			else
				$img = "forum_sig/$server-$account_id.png";
		}
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
		       && 
		  (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($img))) {
		  // send the last mod time of the file back
		  header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($img)).' GMT', 
		  true, 304);
		  exit;
		}
		
		header( "Content-type: image/png");
	if (filemtime($img) >= strtotime(" -1 hour"))
	{
		if (is_file($img)) {
			readfile($img);
			exit;
		}
	}
		
	// 468x100
	 $image = imagecreatetruecolor(936, 200);
	$line = 0;
	$white = imagecolorallocate($image, 255,255,255);
	$yellow = imagecolorallocate($image, 240, 150,   50);      // create color R=255, G=255, B=0
	  $cyan   = imagecolorallocate($image,   0, 255, 255);      // create color R=0, G=255, B=255
	  $red    = imagecolorallocate($image, 255,   0,   0);      // create color red
	  $blue   = imagecolorallocate($image,   0,   0, 255);      // create color blue
		$green = imagecolorallocate($image, 24, 150, 13);
		$black = imagecolorallocate($image, 0 , 0 ,0);
		$purple = imagecolorallocate($image, 150 , 13 ,171);
		//$white = imagecolorallocate($image, 255,255,255);
		
		$navy = imagecolorallocate($image,0,0,102);
		
		if ($dark == 2) {
			imagefilledrectangle($image, 0, 0, 936, 200, $black);
			$color["default"] = $white;
		} else if ($dark == 3) {
			if ($fg_color and $bg_color) {
				$nfg = imagecolorallocate($image,$fg_color[0],$fg_color[1],$fg_color[2]);
				$nbg = imagecolorallocate($image,$bg_color[0],$bg_color[1],$bg_color[2]);
				imagefilledrectangle($image, 0, 0, 936, 200, $nbg);
				$color["default"] = $nfg;
			} else {
					imagefilledrectangle($image, 0, 0, 936, 200, $white);
					$color["default"] = $navy;
			}
			
		} else {
			imagefilledrectangle($image, 0, 0, 936, 200, $white);
			$color["default"] = $navy;
		}
		
	//	$font = 'libs/VeraMono.ttf';
		$font = 'libs/Arial.ttf';
		
		$line=1;
		
		
		$s0 = loadStatistics($account_id);
		date_default_timezone_set('America/Los_Angeles');
		$s0["registered"] = date("Y-m-d g:i A",$s0["registered"]) . " PST";
		$s0["lastUpdate"] = date("Y-m-d g:i A",$s0["updated"]) . " PST";
		$s0["name"] = $name;
		$stats = $s0;
		$id = $account_id;
		$s60 = getStatPeriod($id, 0,1,"h");
		$s24 = getStatPeriod($id, 0,24,"h");
	//	print_r($s24);
	$days = 7;
	$s7 = false;
	while (!$s7 and $days > 0) {
		$s7 = getStatPeriod($id, 0,$days * 24,"h");
		$days--;
	}
	$days = 30;
	$s30 = false;
	while (!$s30 and $days > 0) {
		$s30 = getStatPeriod($id, 0,$days * 24,"h");
		$days--;
	}
	
		//$s7 = getStatPeriod($id, 0,168,"h");
		//$s30 = getStatPeriod($id, 0,720,"h");

		//$s0 = prettyStats($s0,$s0,false);
		$s60 = prettyStats($s60,$s0,false);
		$s60["efficiency"] = calcPeriodEfficiency($s0,$s60);
		$s24 = prettyStats($s24,$s0,false);
		$s24["efficiency"] = calcPeriodEfficiency($s0,$s24);
		$s7 = prettyStats($s7,$s0,false);
		$s7["efficiency"] = calcPeriodEfficiency($s0,$s7);
		$s30 = prettyStats($s30,$s0,false);
		$s30["efficiency"] = calcPeriodEfficiency($s0,$s30);
		
		$c[0] = 5*2;
		$c[1] = 100*2;
		$c[2] = 175*2;
		$c[3] = 250*2;
		$c[4] = 325*2;
		$c[5] = 400*2;
		
		$r[0] = 45*2;
		$r[1] = 58*2;
		$r[2] = 71*2;
		$r[3] = 84*2;
		$r[4] = 97*2;
		//$color["default"] = $navy;
		$color["red"] = $red;
		$color["green"] = $green;
		
		imagettftextmultisampled($image, 15*2, 0, 15*2, 19*2, $color["default"], $font, $name);
		$effColor = $red;
		if ($stats["efficiency"] > 900)
			$effColor = $yellow;
		if ($stats["efficiency"] > 1200)
				$effColor = $green;
		if ($stats["efficiency"] > 1500)
				$effColor = $purple;
		
		$textSize = 7*2;
		
		imagettftextmultisampled($image, $textSize, 0, $c[0], 34*2, $effColor, $font, "Efficiency: ".$stats["efficiency"]."");
		if ($stats["clan_tag"])
			imagettftextmultisampled($image, 10*2, 0, 275*2, 19*2, $color["default"], $font, $stats["clan_days"] . " days in clan " . trim($stats["clan_tag"],"[]"));
		// Line 1 Headers (column lables)
		imagettftextmultisampled($image, $textSize, 0, $c[1], 34*2, $color["default"], $font, "Total");
		imagettftextmultisampled($image, $textSize, 0, $c[2], 34*2, $color["default"], $font, "Past 60 Mins");
		imagettftextmultisampled($image, $textSize, 0, $c[3], 34*2, $color["default"], $font, "Past 24 Hours");
		imagettftextmultisampled($image, $textSize, 0, $c[4], 34*2, $color["default"], $font, "Past 7 Days");
		imagettftextmultisampled($image, $textSize, 0, $c[5], 34*2, $color["default"], $font, "Past 30 Days");
		// row labels
		imagettftextmultisampled($image, $textSize, 0, $c[0], $r[0], $color["default"], $font, "Battles:");
		imagettftextmultisampled($image, $textSize, 0, $c[0], $r[1], $color["default"], $font, "Victory %:");
		imagettftextmultisampled($image, $textSize, 0, $c[0], $r[2], $color["default"], $font, "K/D Ratio:");
		imagettftextmultisampled($image, $textSize, 0, $c[0], $r[3], $color["default"], $font, "Damage Rate:");
		imagettftextmultisampled($image, $textSize, 0, $c[0], $r[4], $color["default"], $font, "Exp. Rate:");

		
		//Battles Data
		
		imagettftextmultisampled($image, $textSize, 0, $c[1], $r[0], $color["default"], $font, $s0["battles"]);
		imagettftextmultisampled($image, $textSize, 0, $c[2], $r[0], $color[$s60["battles_color"]], $font, $s60["battles"]);
		imagettftextmultisampled($image, $textSize, 0, $c[3], $r[0], $color[$s24["battles_color"]], $font, $s24["battles"]);
		imagettftextmultisampled($image, $textSize, 0, $c[4], $r[0], $color[$s7["battles_color"]], $font, $s7["battles"]);
		imagettftextmultisampled($image, $textSize, 0, $c[5], $r[0], $color[$s30["battles_color"]], $font, $s30["battles"]);
		
		imagettftextmultisampled($image, $textSize, 0, $c[1], $r[1], $color["default"], $font, $s0["victories_p"]."%");
		imagettftextmultisampled($image, $textSize, 0, $c[2], $r[1], $color[$s60["victories_p_color"]], $font, $s60["victories_p"]);
		imagettftextmultisampled($image, $textSize, 0, $c[3], $r[1], $color[$s24["victories_p_color"]], $font, $s24["victories_p"]);
		imagettftextmultisampled($image, $textSize, 0, $c[4], $r[1], $color[$s7["victories_p_color"]], $font, $s7["victories_p"]);
		imagettftextmultisampled($image, $textSize, 0, $c[5], $r[1], $color[$s30["victories_p_color"]], $font, $s30["victories_p"]);
		
		imagettftextmultisampled($image, $textSize, 0, $c[1], $r[2], $color["default"], $font, $s0["kd_r"]);
		imagettftextmultisampled($image, $textSize, 0, $c[2], $r[2], $color[$s60["kd_r_color"]], $font, $s60["kd_r"]);
		imagettftextmultisampled($image, $textSize, 0, $c[3], $r[2], $color[$s24["kd_r_color"]], $font, $s24["kd_r"]);
		imagettftextmultisampled($image, $textSize, 0, $c[4], $r[2], $color[$s7["kd_r_color"]], $font, $s7["kd_r"]);
		imagettftextmultisampled($image, $textSize, 0, $c[5], $r[2], $color[$s30["kd_r_color"]], $font, $s30["kd_r"]);
		
		imagettftextmultisampled($image, $textSize, 0, $c[1], $r[3], $color["default"], $font, $s0["damage_r"]);
		imagettftextmultisampled($image, $textSize, 0, $c[2], $r[3], $color[$s60["damage_r_color"]], $font, $s60["damage_r"]);
		imagettftextmultisampled($image, $textSize, 0, $c[3], $r[3], $color[$s24["damage_r_color"]], $font, $s24["damage_r"]);
		imagettftextmultisampled($image, $textSize, 0, $c[4], $r[3], $color[$s7["damage_r_color"]], $font, $s7["damage_r"]);
		imagettftextmultisampled($image, $textSize, 0, $c[5], $r[3], $color[$s30["damage_r_color"]], $font, $s30["damage_r"]);
		
			imagettftextmultisampled($image, $textSize, 0, $c[1], $r[4], $color["default"], $font, $s0["experience_r"]);
			imagettftextmultisampled($image, $textSize, 0, $c[2], $r[4], $color[$s60["experience_r_color"]], $font, $s60["experience_r"]);
			imagettftextmultisampled($image, $textSize, 0, $c[3], $r[4], $color[$s24["experience_r_color"]], $font, $s24["experience_r"]);
			imagettftextmultisampled($image, $textSize, 0, $c[4], $r[4], $color[$s7["experience_r_color"]], $font, $s7["experience_r"]);
			imagettftextmultisampled($image, $textSize, 0, $c[5], $r[4], $color[$s30["experience_r_color"]], $font, $s30["experience_r"]);
			
			
			
	
	//imagestring($image, 5, 15, $line++ * 12, 'This is a test.', $navy);
	//imagestring($image, 2, 15, $line++ * 12, 'This is a test.', $navy);
	//imagestring($image, 2, 15, $line++ * 12, 'This is a test.', $navy);
//	imagestring($image, 2, 15, $line++ * 12, 'This is a test.', $navy);
//	imagestring($image, 2, 15, $line++ * 12, 'This is a test.', $navy);
//	imagestring($image, 2, 15, $line++ * 12, 'This is a test.', $navy);
	
	$image_p = imagecreatetruecolor(936/2, 100);
	imagecopyresampled($image_p,$image,0,0,0,0,936/2,100,936,200);
	
	imagepng($image_p,$img);
	imagepng($image_p);
	
	imagedestroy($image);
	imagedestroy($image_p);
}


function imagettftextmultisampled(&$hImg, $iSize, $sAngle, $iX, $iY, $cColor, $sFont, $sText, $iMultiSampling=2){
 $iWidth  = imagesx($hImg);
 $iHeight = imagesy($hImg);
 $hImgCpy = imagecreatetruecolor(ceil($iWidth*$iMultiSampling), ceil($iHeight*$iMultiSampling));
 $cColor  = imagecolorsforindex($hImg, $cColor);
 $cColor  = imagecolorallocatealpha($hImgCpy, $cColor['red'], $cColor['green'], $cColor['blue'], $cColor['alpha']);
 imagesavealpha($hImgCpy, true);
 imagealphablending($hImgCpy, false);
 $cTransparent = imagecolortransparent($hImgCpy, imagecolorallocatealpha($hImgCpy, 0, 0, 0, 127));
 imagefill($hImgCpy, 0, 0, $cTransparent);
 $aBox = imagettftext($hImgCpy, $iSize*$iMultiSampling, $sAngle, ceil($iX*$iMultiSampling), ceil($iY*$iMultiSampling), $cColor, $sFont, $sText);
 imagecopyresampled($hImg, $hImgCpy, 0, 0, 0, 0, $iWidth, $iHeight, ceil($iWidth*$iMultiSampling), ceil($iHeight*$iMultiSampling));
 imagedestroy($hImgCpy);
 foreach($aBox as $iKey => $iCoordinate)
  $aBox[$iKey] = $iCoordinate/$iMultiSampling;
 return($aBox);
}

function imagettfbboxmultisampled($iSize, $iAngle, $sFont, $sText, $iMultiSampling){
 $aBox = imagettfbbox($iSize*$iMultiSampling, $iAngle, $sFont, $sText);
 foreach($aBox as $iKey => $iCoordinate)
  $aBox[$iKey] = $iCoordinate/$iMultiSampling;
 return($aBox);
}

function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

?>