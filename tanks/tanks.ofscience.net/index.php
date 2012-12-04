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

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

session_start();


if ($_GET["forceUpdate"])
	$forceUpdate= true;

if ($_GET["server"] == "eu"){
	$_SESSION["server"] = "eu";
	$server = "eu";
} else 	if ($_GET["server"] == "sea"){
	$_SESSION["server"] = "sea";
	$server = "sea";
} else if ($_GET["server"] == "na") {
	$_SESSION["server"] ="na";
$server = "na";
}

//$server = $_SESSION["server"];
if (!$server)
	$server ="na";


if (!$_GET["sig"]) {
	header("Content-Type: text/html; charset=utf-8");
	
if ($_GET["debug"]){
	$debug=true;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Tanker Stats <?if($_GET["name"]) echo " - ". $_GET["name"]; else if ($_GET["clanName"]) echo " - " .$_GET["clanName"];?></title>
	
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery.dataTables.css" >
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
  <meta name="viewport" content="width=600px" />
 
	<script src="/js/jquery.js" type="text/javascript"></script>
	<script src="/js/jquery.dataTables.min.js" type="text/javascript"></script>
	<script src="/js/jquery.infieldlabel.min.js" type="text/javascript"></script>
	<script src="/js/highcharts.js" type="text/javascript"></script>
	<script src="/js/modules/exporting.js" type="text/javascript"></script>
	<script src="/js/listscroll.js" type="text/javascript"></script>
	
	<style type="text/css">
		.topbar_nocolor {

			padding:2px;
			color:#FFFFFF;
			margin-top:0px;
			width:80%;
			margin:0 auto;

		}
		
		.show_cust:hover .cust_color {
			display:block;
			
		}
		
		.cust_color {
			display:none;
			z-index: 10;
			width: 600px;
			border: 1px solid black;
			padding: 2px;
			background: #636462;
			margin-top: 0px;
			position: relative;
			margin: 0px auto;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			border-radius: 3px;
			color: white;
			
		}
		
		
		.buttons {
			margin-top:-1px;
		}
		.right_align {
			margin-right:0px;
		}
		.right_space {
			margin-right:5px;
		}
		@media only screen and (max-device-width: 480px) {
					.buttons li,.buttons li a:link, .buttons li a:visited	{
						float:none;
						width:auto;
						margin-bottom:5px;
						
					}
					.buttons ul
					{
						list-style-type:none;
					}
					#mainContainer{
							width:100%;
							border:0px;
							padding:0px;
						}
						.tickercontainer {
							width:auto;
						}
			.generalStats {
				width:100%;
			}
			#logo {
				display:none;
			}
			#topTanks {
				width:49%;
			}
			#newTanks {
				width:49%;
			}
		
			#mainContainerFrontPage {
				width:100%;
				border:0px;
				padding:0px;
				margin:0px;
				margin:0 auto;
			}
			#tankerStats{
				margin:0 auto;
				width:100%;
			}
			.topbar,.topbar_nocolor
			{
				width:100%;
			}
			body{
				width:100%;
				margin:0px;
			}
			.generalStats {
				width:100%;
					border:0px;
					padding:0px;
			}
			#tankerStats{
				width:100%;
				border:0px;
				padding:0px;
			}
		}
	
	
	
	
	</style>
	

	
	
	<script type="text/javascript">
	var chart;
	$(document).ready(function() {
			
	    $('#list').dataTable({"bPaginate": false});
			$("label").inFieldLabels();
			<? if ($_GET["name"]) {?>
				initChart();
			<?}?>
			$("ul#ticker").liScroll();
			
			$('.show_cust').click(function(){
				$('.cust_color').fadeIn();
				
			});
			//	$('.show_cust').mouseout(function(){
			//		$('.cust_color').fadeOut();
			//	});
			
	} );
	</script>
</head>
<body>
	

	
	
	<div class="topbar_nocolor">
		<? if($server == "eu") {?>
		<span class="right">Current Server: <span class="flair flair-eu" style="margin-right:.5em">&nbsp;</span>EU</span>
		<? } else if ($server == "sea") {?>
				<span class="right">Current Server: <span class="flair flair-sea" style="margin-right:.5em">&nbsp;</span>SEA</span>
				<? } else {?>
					
			<span class="right">Current Server: <span class="flair flair-us" style="margin-right:.5em">&nbsp;</span>NA</span>
			
			<?}?>
	</div>
	<div style="clear:both"></div>
	<div class="topbar">
		<form method="GET" action="/">	
		<table style="width:100%" ><TR><TD style="width:50%">
		<ul class="buttons">
		<li class="buttons"><a href="/">Home</a></li>
		<? if($server == "eu") {?>
			<li class="buttons"><a href="/?server=na">NA Server</a></li>
			<input type="hidden" name="server" value="eu">
			<li class="buttons"><a href="/?server=sea">SEA Server</a></li>
			
			<?} else if ($server =="sea"){?>
					<li class="buttons"><a href="/?server=eu">EU Server</a></li>
					<input type="hidden" name="server" value="sea">
					<li class="buttons"><a href="/?server=na">NA Server</a></li>
					
			<?} else {?>
				<li class="buttons"><a href="/?server=eu">EU Server</a></li>
			
				<li class="buttons"><a href="/?server=sea">SEA Server</a></li>
				<input type="hidden" name="server" value="na">
				<?}?>
		</ul>
		</td><TD style="width:50%">
		
			<div class="right right_space">

				<p>
				  <label for="clanName">Clan Name</label>
				  <input type="text" name="clanName" value="" id="clanName">	
					<input type="submit" value="View">
				</p>
		 </div>
		
		<div class="right right_space">
			<p>
			  <label for="name">Tanker Name</label>
			  <input type="text" name="name" value="" id="name">
				<input type="submit" value="View">
			</p>
		</div>
		
	
		</td></tr></table>
		</form>

</div>
<div <?if ($_GET["clanName"] or $_GET["name"]) echo "id='mainContainer'"; else echo "id='mainContainerFrontPage'";?>>
<?php



if (!$_GET["debug"])
	error_reporting(0);
else 
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//World of Tanks Stats Generator
require_once '/home/tanks/tanks.ofscience.net/libs/WOTLib.php';
//http://worldoftanks.com/community/accounts/?type=account_table_search&at_search=azd&offset=0&limit=25&order_by=name&id=accounts_index&echo=1


} else {
	if ($_GET["name"])
	{
		if (!$_GET["debug"])
			error_reporting(0);
		else 
			error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
		require_once 'libs/WOTLib.php';
		$id = getPlayerIdFromNick($_GET["name"]);
		if ($_GET["sig"] == "dark")
			createForumSignature($id,$_GET["name"],2);
		else if ($_GET["sig"] == "color")
			createForumSignature($id,$_GET["name"],3,$_GET["fg"],$_GET["bg"]);
		else
			createForumSignature($id,$_GET["name"]);
		exit(0);
	}
}


if($_GET["clanName"]) {
	if ($_GET["forceUpdate"])
		updateClanStats($_GET["clanName"]);
	$clanUsers = loadClanMembers($_GET["clanName"]);
	$clan = getClan($_GET["clanName"]);
	if ($clan) {
		echo "<div class=\"contentDiv\">";
		
		if (!$clan["stats"])
			echo "<h1>" . $_GET["clanName"]."</h1>";
		if ($clan["stats"]) {
			echo "<div class=\"left\">";
			echo "<h2>" . $clan["stats"]["name"]."</h2><i>" . $clan["stats"]["motto"]."</i><hr><br>";
			echo "Members: " . $clan["stats"]["member_count"]."<BR>";
		//		date_default_timezone_set('America/Los_Angeles');
				$clan_update_time = $clan["stats"]["updateTime"] . " PST";
			echo "</div><div> &nbsp;</div><div class=\"right\">";
			echo "<img src=\"http://worldoftanks.com". $clan["stats"]["clan_emblem_url"] ."\"><br> ";
			echo "Last Updated: ". $clan["stats"]["updateTime"];
			echo "</div>";
		}
		echo "<table id=\"list\" class=\"gridtable\">";
		echo "<thead><TR><TD colspan>Name</td><td>Battles</td><td>Win Rate</td><TD>Efficiency</td></tr></thead>";
		echo "<tbody>";
		foreach($clanUsers as $user)
		{
			echo "<tr><TD><a href=?server=$server&amp;name=" . $user["name"] .">".$user["name"]."</a></td>";
			echo "<td>".$user["battles"]."</td>";	
			echo "<td>".$user["wr"]."%</td>";
			echo "<td>".$user["eff"]."</td>";
		
			echo "</tr>";
		
		}
		echo "</tbody>";
		echo "</table><BR><BR><BR></div>";
	} else {
		echo "<h1>Clan not found</h1>";
	}
} else 
if($_GET["name"]) {
	echo "<div class=\"contentDiv\">";
	$id = getPlayerIdFromNick($_GET["name"]);
	//$stats = loadGeneralStats($id);
	
	$s0 = loadStatistics($id);
	date_default_timezone_set('America/Los_Angeles');
	$s0["registered"] = date("Y-m-d g:i A",$s0["registered"]) . " PST";
	$s0["lastUpdate"] = date("Y-m-d g:i A",$s0["updated"]) . " PST";
	$s0["name"] = $_GET["name"];
	$stats = $s0;
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
	
	
	if ($_GET["top10"])
	{
		echo "<pre>";
		$topTanks = topTanks($s7);
		print_r($topTanks);
		echo "</pre>";
	}
	
	
	
	
	
	
	
	
	
	//$s0 = prettyStats($s0,$s0);
	$s60 = prettyStats($s60,$s0);
	$s60["efficiency"] = calcPeriodEfficiency($s0,$s60);
	$s24 = prettyStats($s24,$s0);
		$s24["efficiency"] = calcPeriodEfficiency($s0,$s24);
	$s7 = prettyStats($s7,$s0);
		$s7["efficiency"] = calcPeriodEfficiency($s0,$s7);
	$s30 = prettyStats($s30,$s0);
		$s30["efficiency"] = calcPeriodEfficiency($s0,$s30);
	
	if ($s0["web_check"]) {
		echo "<i>WOT Server checked for fresh data</i><BR>";
	}
	
	if ($s0["battles"])
	{
			$filteredName =ereg_replace("[^A-Za-z0-9_]", "", $_GET["name"] );
			echo "<div id='tankerStats'>
			<span class='left'><h1>&nbsp;&nbsp;&nbsp;".$filteredName."</h1></span>
			<table class=\"gridtable generalStats\">";
			
			echo "<TR><td>&nbsp;</td><td colspan=2>Total</td><TD colspan=2>Past Hour</td><TD colspan=2>Past 24 Hours</td><td colspan=2>Past 7 days</td><TD colspan=2>Past 30 days</td></tr>";
			echo "<TR><TD>Battles</td>
																<TD colspan=2>".$s0['battles']."</td>
																<TD colspan=2>".$s60['battles']."</td>
																<TD colspan=2>".$s24['battles']."</td>
																<TD colspan=2>".$s7['battles']."</td>
																<TD colspan=2>".$s30['battles']."</td></tr>";
			echo "<TR><TD>Victories</td>
																	<TD>".$s0['victories']."</td><TD>".$s0['victories_p']."%</td>
																	<TD>".$s60['victories']."</td><TD>".$s60['victories_p']."</td>
																	<TD>".$s24['victories']."</td><TD>".$s24['victories_p']."</td>
																	<TD>".$s7['victories']."</td><TD>".$s7['victories_p']."</td>
																	<TD>".$s30['victories']."</td><TD>".$s30['victories_p']."</td></tr>";
			echo "<TR><TD>Defeats</td>
																<TD>".$s0['defeats']."</td><TD>".$s0['defeats_p']."</td>
																<TD>".$s60['defeats']."</td><TD>".$s60['defeats_p']."</td>
																<TD>".$s24['defeats']."</td><TD>".$s24['defeats_p']."</td>
																<TD>".$s7['defeats']."</td><TD>".$s7['defeats_p']."</td>
																<TD>".$s30['defeats']."</td><TD>".$s30['defeats_p']."</td></tr>";		
																												
			echo "<TR><TD>Draws</td>
																<TD>".($s0['draws'])."</td><TD>".($s0['draws_p'])."</td>
																<TD>".($s60['draws'])."</td><TD>".($s60['draws_p'])."</td>
																<TD>".($s24['draws'])."</td><TD>".($s24['draws_p'])."</td>
																<TD>".($s7['draws'] )."</td><TD>".($s7['draws_p'])."</td>
																<TD>".($s30['draws'])."</td><TD>".($s30['draws_p'])."</td></tr>";
																
																
																
			echo "<TR><TD>Survived</td>
																<TD>".$s0['survived']."</td><TD>".$s0['survived_p']."</td>
																<TD>".$s60['survived']."</td><TD>".$s60['survived_p']."</td>
																<TD>".$s24['survived']."</td><TD>".$s24['survived_p']."</td>
																<TD>".$s7['survived']."</td><TD>".$s7['survived_p']."</td>
																<TD>".$s30['survived']."</td><TD>".$s30['survived_p']."</td></tr>";
//
			echo "<TR><TD>Destroyed</td>
																<TD>".$s0['destroyed']."</td><TD>".$s0['destroyed_r']."</td>
																<TD>".$s60['destroyed']."</td><TD>".$s60['destroyed_r']."</td>
																<TD>".$s24['destroyed']."</td><TD>".$s24['destroyed_r']."</td>
																<TD>".$s7['destroyed']."</td><TD>".$s7['destroyed_r']."</td>
																<TD>".$s30['destroyed']."</td><TD>".$s30['destroyed_r']."</td></tr>";
			echo "<TR><TD>K/D Ratio</td>
																<TD colspan=2>".$s0["kd_r"]."</td>
																<TD colspan=2>".$s60["kd_r"]."</td>
																<TD colspan=2>".$s24["kd_r"]."</td>
																<TD colspan=2>".$s7["kd_r"]."</td>
																<TD colspan=2>".$s30["kd_r"]."</td></tr>";
			echo "<TR><TD>Detected</td>
																<TD>".$s0['detected']."</td><TD>".$s0['detected_r']."</td>
																<TD>".$s60['detected']."</td><TD>".$s60['detected_r']."</td>
																<TD>".$s24['detected']."</td><TD>".$s24['detected_r']."</td>
																<TD>".$s7['detected']."</td><TD>".$s7['detected_r']."</td>
																<TD>".$s30['detected']."</td><TD>".$s30['detected_r']."</td></tr>";
//
			echo "<TR><TD>Damage</td>
																<TD>".$s0["damage"]."</td><TD>".$s0["damage_r"]."</td>
																<TD>".$s60["damage"]."</td><TD>".$s60["damage_r"]."</td>
														  	<TD>".$s24["damage"]."</td><TD>".$s24["damage_r"]."</td>
																<TD>".$s7["damage"]."</td><TD>".$s7["damage_r"]."</td>
																<TD>".$s30["damage"]."</td><TD>".$s30["damage_r"]."</td></tr>";
//
			echo "<TR><TD>Capture Points</td>
															<TD>".$s0["capture"]."</td><TD>".$s0["capture_r"]."</td>
															<TD>".$s60["capture"]."</td><TD>".$s60["capture_r"]."</td>
													  	<TD>".$s24["capture"]."</td><TD>".$s24["capture_r"]."</td>
															<TD>".$s7["capture"]."</td><TD>".$s7["capture_r"]."</td>
															<TD>".$s30["capture"]."</td><TD>".$s30["capture_r"]."</td></tr>";
//
			echo "<TR><TD>Defense Points</td>
															<TD>".$s0["defense"]."</td><TD>".$s0["defense_r"]."</td>
															<TD>".$s60["defense"]."</td><TD>".$s60["defense_r"]."</td>
													  	<TD>".$s24["defense"]."</td><TD>".$s24["defense_r"]."</td>
															<TD>".$s7["defense"]."</td><TD>".$s7["defense_r"]."</td>
															<TD>".$s30["defense"]."</td><TD>".$s30["defense_r"]."</td></tr>";
//																																		
			echo "<TR><TD>Experience</td>
															<TD>".$s0["experience"]."</td><TD>".$s0["experience_r"]."</td>
															<TD>".$s60["experience"]."</td><TD>".$s60["experience_r"]."</td>
													  	<TD>".$s24["experience"]."</td><TD>".$s24["experience_r"]."</td>
															<TD>".$s7["experience"]."</td><TD>".$s7["experience_r"]."</td>
															<TD>".$s30["experience"]."</td><TD>".$s30["experience_r"]."</td></tr>";
//																													
																						
			echo "<TR><TD>Efficiency</td>
															<TD colspan=2>".$s0["efficiency"]."</td>
															<TD colspan=2>".$s60["efficiency"]."</td>
															<TD colspan=2>".$s24["efficiency"]."</td>
															<TD colspan=2>".$s7["efficiency"]."</td>
															<TD colspan=2>".$s30["efficiency"]."</td>
															</tr>";											
													
																									
																									
												echo "</table>";
												if ($stats["clan_tag"])
												{ echo "<span><h4>&nbsp;&nbsp;&nbsp;Member of clan <a href='?server=$server&amp;clanName=".trim($stats["clan_tag"],"[]")."'>".  trim($stats["clan_tag"],"[]") ."</a> for ". $stats["clan_days"]." days</h4></span>";
												}
												echo "</div>";													
																
																
	} else {
	//	echo "<h1>No data found for " . $_GET["name"]."</h1>";
	}
	

	if ($stats["battles"]){
		echo "<div class=\"updated generalStats\">	Tracking tanker since: " . getTrackingDate($id) ."<br><B>WOT Server Updated: " . $stats["lastUpdate"]." </b><br> ";
		echo "<i>Last Check: ". getLastUpdate($id)."<BR></i>";
	
		echo "<span><a href='http://tanks.ofscience.net/sig/$server/".$_GET["name"]."/signature.png'>Forum Signature Image</a> [ <a href='http://tanks.ofscience.net/sig_dark/$server/".$_GET["name"]."/signature.png'>dark</a>  <a href='#' class='show_cust'>custom</a> ]</span><BR>
		<div class='cust_color'>Custom Signature Colors:<BR><BR>Any Valid 3 or 6 digit hex code (html color codes)<BR> Format: http://tanks.ofscience.net/sig_cust/text_color/background_color/$server/".$_GET["name"]."/signature.png<BR><BR>
		eg: http://tanks.ofscience.net/sig_cust/000000/FFFFFF/$server/".$_GET["name"]."/signature.png
		</div>
		<div><BR>";
		?>
		
		<h3>24hr Stats</h3>
		<div id="win24" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		<div id="dam24" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		<div id="bat24" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		
		
		
		<h3>Weekly Stats</h3>
		<div id="container" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		<div id="container2" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		<div id="container3" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		<h3>Monthly Stats</h3>
		<div id="win30" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		<div id="dam30" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		<div id="bat30" style="min-width: 200px; height: 200px; margin: 0 auto"></div>
		
		<h3>Overall Stats</h3>
		<div id="container4" style="min-width: 300px; height: 300px; margin: 0 auto"></div>
		<div id="container5" style="min-width: 300px; height: 300px; margin: 0 auto"></div>
		
		
		<?php
		
		
		printChartJS();
			echo "<i>Please note that stats are polled hourly, WOT does not always provide up-to-date stats and newly added accounts will require 30 days of stat history before all of the columns are accurate.</i><BR>";
	} else {
		echo "<h1><span class='center'>No data found for tanker " .$_GET["name"] ."</span></h1><BR>";	
	
	
	}
	
	
	echo "<BR ><BR ></div></div>
	</div>";
} else if ($_GET["feedback"]){
	?>
	
	<iframe src="https://docs.google.com/spreadsheet/embeddedform" width="100%" height="600" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe>
	<?
	
} else { 
	require_once '/home/tanks/tanks.ofscience.net/libs/WOTLib.php';
	$lastUpdate = getLastUpdates();
	if ($_GET["sort_top"]=="wr")
		$topTankers = getTopTankers(2);
	else if ($_GET["sort_top"] =="battles")
		$topTankers = getTopTankers(3);
	else
		$topTankers = getTopTankers(1);
	
	$newTankers = getRecentlyAdded();
	
?>


<div id="topTanks">
	<h3>Top Tankers</h3>
	<table>
		<TR><TD>Tanker</td><td><a class="green_link" href="/?sort_top=battles">Bat.</a></td><TD><a class="green_link" href="/?sort_top=eff">Eff.</a></td><td><a class="green_link" href="/?sort_top=wr">WR</a></td></tr>
	<? 
	foreach ($topTankers as $tanker) {
			echo "<tr><TD><a href='/?server=$server&amp;name=".$tanker["account_name"]."'>".$tanker["account_name"]."</a></td><td>".round($tanker["battles"]/1000,1) ."K</td><TD>" .colorEff($tanker["eff"]) ."</td><TD>".colorWR($tanker["wr"]) ."%</td></tr>"; 
		
	}
	?>
	</table>
</div>
<div id="newTanks">
	<h3>Recently Added Tankers</h3>
	<table>
	<? 
	foreach ($newTankers as $tanker) {
			echo "<tr><TD><a href='/?server=$server&amp;name=".$tanker["account_name"]."'>".$tanker["account_name"]."</a></td><TD>Eff: " .colorEff($tanker["eff"]) ."</td><TD>WR: ". colorWR($tanker["wr"]) ."%</td></tr>"; 
		
	}
	?>
	</table>
</div>
<div id="logo">&nbsp;</div>







<?


}
?>


</div>
<div class="clearboth">&nbsp;</div>
<?if (!$_GET["name"] and !$_GET["clanName"] and !$_GET["feedback"]){?>
<ul id="ticker" class="newsticker">
<?	
	foreach($lastUpdate as $account)
	{
		echo "<li><span> [ <a href=\"?server=$server&amp;name=".$account["name"] ."\">".$account["name"]."</a> <i>".$account["updateTime"]."</i> ]</span></li>";
	}

?>
</ul>
<div class="topbar"><span>Updates:</span>
	<ul id="ticker2" style="background:white;color:black">
		<li> 11/14/12 Clan users lists now updating correctly - update check performed every 24 hours
		<li> 11/13/12 been really busy - clan search updated to reflect wot changes, stats charts now starting at zero. 
		<li> 9/20/12 - EU Server bug fixes - users added after 9/18/12 may be missing data</li>
		<li> 9/18/12 - SEA Server Support</li>
		<li> 9/5/12 - Custom Signature image colors</li>
		<li> 9/4/12 - Fixed WOT Official forum signature problem.</li>
	</ul>
</div>
<?}?>

<div class="topbar">
	<span><?php echo tracking();?></span><span class="right"><i><a class="green_link" href="/?feedback=1">Leave Feedback</a></i></span>
</div>



</body>
</html>











<?



function printChartJS() {
	
	
	
	?>
	
	<script type="text/javascript">
		function initChart() {
			var chart1,chart2,chart3,chart4,chart5,chart6,chart7,win24,dam24,bat24,win30,dam30,bat30;
					chart = new Highcharts.Chart({
          chart: {
              renderTo: 'container',
              type: 'line',
              marginRight: 130,
              marginBottom: 25
          },credits: { enabled: false },
          title: {
              text: 'Win % / Survival %',
              x: -20 //center
          },
          subtitle: {
              text: 'Source: WorldofTanks.com',
              x: -20
          },
          xAxis: {
              categories: [<?printPast7days();?>]
          },
          yAxis: {
              title: {
                  text: 'Winrate'
              },
              plotLines: [{
                  value: 0,
                  width: 1,
                  color: '#808080'
              }]		,
									min: 0
          },
          tooltip: {
              formatter: function() {
                      return ''+ this.series.name +'<br/>'+
                      this.x +': '+ this.y +'%';
              }
          },
          legend: {
              layout: 'vertical',
              align: 'right',
              verticalAlign: 'top',
              x: -10,
              y: 100,
              borderWidth: 0
          },
          series: [{
              name: 'Victories',
              data: [ <?printStatOverPeriod(7,$_GET["name"],"wr","d");?>]
          },		{
		              name: 'Survival Rate',
		              data: [ <?printStatOverPeriod(7,$_GET["name"],"survival","d");?>]
		          }
			]
      });




		
				chart1 = new Highcharts.Chart({
        chart: {
            renderTo: 'container2',
            type: 'line',
            marginRight: 130,
            marginBottom: 25
        },credits: { enabled: false },
        title: {
            text: 'Damage / Experience',
            x: -20 //center
        },
        subtitle: {
            text: 'Source: WorldofTanks.com',
            x: -20
        },
        xAxis: {
            categories: [<?printPast7days();?>]
        },
        yAxis: {
            title: {
                text: 'Average'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]		,
								min: 0
        },
        tooltip: {
            formatter: function() {
                    return ''+ this.series.name +'<br/>'+
                    this.x +': '+ this.y +'';
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -10,
            y: 100,
            borderWidth: 0
        },
        series: [	
						{
				        name: 'Damage',
				        data: [ <?printStatOverPeriod(7,$_GET["name"],"dmg","d");?>]
				    },
						{
						    name: 'Experience',
						    data: [ <?printStatOverPeriod(7,$_GET["name"],"exp","d");?>]
						}
		
		]
    });
					chart3 = new Highcharts.Chart({
	        chart: {
	            renderTo: 'container3',
	            type: 'line',
	            marginRight: 130,
	            marginBottom: 25
	        },credits: { enabled: false },
	        title: {
	            text: ' Battles ',
	            x: -20 //center
	        },
	        subtitle: {
	            text: 'Source: WorldofTanks.com',
	            x: -20
	        },
	        xAxis: {
	            categories: [<?printPast7days();?>]
	        },
	        yAxis: {
	            title: {
	                text: 'Average'
	            },
	            plotLines: [{
	                value: 0,
	                width: 1,
	                color: '#808080'
	            }]		,
									min: 0
	        },
	        tooltip: {
	            formatter: function() {
	                    return ''+ this.series.name +'<br/>'+
	                    this.x +': '+ this.y +'';
	            }
	        },
	        legend: {
	            layout: 'vertical',
	            align: 'right',
	            verticalAlign: 'top',
	            x: -10,
	            y: 100,
	            borderWidth: 0
	        },
	        series: [	{
			            name: 'Battles',
			            data: [ <?printStatOverPeriod(7,$_GET["name"],"battles","d");?>]
			        }
							

			]
	    });

	
	//24 hours
	
	
	
		win24 = new Highcharts.Chart({
    chart: {
        renderTo: 'win24',
        type: 'line',
        marginRight: 130,
        marginBottom: 25
    },credits: { enabled: false },
    title: {
        text: 'Win % / Survival %',
        x: -20 //center
    },
    subtitle: {
        text: 'Source: WorldofTanks.com',
        x: -20
    },
    xAxis: {
        categories: [<?printPast24Hours();?>]
    },
    yAxis: {
        title: {
            text: 'Winrate'
        },
        plotLines: [{
            value: 0,
            width: 1,
            color: '#808080'
        }]		,
						min: 0
    },
    tooltip: {
        formatter: function() {
                return ''+ this.series.name +'<br/>'+
                this.x +': '+ this.y +'%';
        }
    },
    legend: {
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'top',
        x: -10,
        y: 100,
        borderWidth: 0
    },
    series: [{
        name: 'Victories',
        data: [ <?printStatOverPeriod(24,$_GET["name"],"wr","h");?>]
    },		{
            name: 'Survival Rate',
            data: [ <?printStatOverPeriod(24,$_GET["name"],"survival","h");?>]
        }
]
});


dam24 = new Highcharts.Chart({
chart: {
    renderTo: 'dam24',
    type: 'line',
    marginRight: 130,
    marginBottom: 25
},credits: { enabled: false },
title: {
    text: 'Damage / Experience',
    x: -20 //center
},
subtitle: {
    text: 'Source: WorldofTanks.com',
    x: -20
},
xAxis: {
    categories: [<?printPast24Hours();?>]
},
yAxis: {
    title: {
        text: 'Average'
    },
    plotLines: [{
        value: 0,
        width: 1,
        color: '#808080'
    }]		,
				min: 0
},
tooltip: {
    formatter: function() {
            return ''+ this.series.name +'<br/>'+
            this.x +': '+ this.y +'';
    }
},
legend: {
    layout: 'vertical',
    align: 'right',
    verticalAlign: 'top',
    x: -10,
    y: 100,
    borderWidth: 0
},
series: [	
		{
        name: 'Damage',
        data: [ <?printStatOverPeriod(24,$_GET["name"],"dmg","h");?>]
    },
		{
		    name: 'Experience',
		    data: [ <?printStatOverPeriod(24,$_GET["name"],"exp","h");?>]
		}

]
});
	bat24 = new Highcharts.Chart({
  chart: {
      renderTo: 'bat24',
      type: 'line',
      marginRight: 130,
      marginBottom: 25
  },credits: { enabled: false },
  title: {
      text: ' Battles ',
      x: -20 //center
  },
  subtitle: {
      text: 'Source: WorldofTanks.com',
      x: -20
  },
  xAxis: {
      categories: [<?printPast24Hours();?>]
  },
  yAxis: {
      title: {
          text: 'Average'
      },
      plotLines: [{
          value: 0,
          width: 1,
          color: '#808080'
      }]		,
					min: 0
  },
  tooltip: {
      formatter: function() {
              return ''+ this.series.name +'<br/>'+
              this.x +': '+ this.y +'';
      }
  },
  legend: {
      layout: 'vertical',
      align: 'right',
      verticalAlign: 'top',
      x: -10,
      y: 100,
      borderWidth: 0
  },
  series: [	{
          name: 'Battles',
          data: [ <?printStatOverPeriod(24,$_GET["name"],"battles","h");?>]
      }
			

]
});
	
	
	
	
	
	
	
	
	
	
	
	//30 days
	
		win30 = new Highcharts.Chart({
    chart: {
        renderTo: 'win30',
        type: 'line',
        marginRight: 130,
        marginBottom: 25
    },credits: { enabled: false },
    title: {
        text: 'Win % / Survival %',
        x: -20 //center
    },
    subtitle: {
        text: 'Source: WorldofTanks.com',
        x: -20
    },
    xAxis: {
        categories: [<?printPast30Days();?>]
    },
    yAxis: {
        title: {
            text: 'Winrate'
        },
        plotLines: [{
            value: 0,
            width: 1,
            color: '#808080'
        }]		,
						min: 0
    },
    tooltip: {
        formatter: function() {
                return ''+ this.series.name +'<br/>'+
                this.x +': '+ this.y +'%';
        }
    },
    legend: {
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'top',
        x: -10,
        y: 100,
        borderWidth: 0
    },
    series: [{
        name: 'Victories',
        data: [ <?printStatOverPeriod(30,$_GET["name"],"wr","d");?>]
    },		{
            name: 'Survival Rate',
            data: [ <?printStatOverPeriod(30,$_GET["name"],"survival","d");?>]
        }
]
});


dam30 = new Highcharts.Chart({
chart: {
    renderTo: 'dam30',
    type: 'line',
    marginRight: 130,
    marginBottom: 25
},credits: { enabled: false },
title: {
    text: 'Damage / Experience',
    x: -20 //center
},
subtitle: {
    text: 'Source: WorldofTanks.com',
    x: -20
},
xAxis: {
    categories: [<?printPast30Days();?>]
},
yAxis: {
    title: {
        text: 'Average'
    },
    plotLines: [{
        value: 0,
        width: 1,
        color: '#808080'
    }]		,
				min: 0
},
tooltip: {
    formatter: function() {
            return ''+ this.series.name +'<br/>'+
            this.x +': '+ this.y +'';
    }
},
legend: {
    layout: 'vertical',
    align: 'right',
    verticalAlign: 'top',
    x: -10,
    y: 100,
    borderWidth: 0
},
series: [	
		{
        name: 'Damage',
        data: [ <?printStatOverPeriod(30,$_GET["name"],"dmg","d");?>]
    },
		{
		    name: 'Experience',
		    data: [ <?printStatOverPeriod(30,$_GET["name"],"exp","d");?>]
		}

]
});
	bat30 = new Highcharts.Chart({
  chart: {
      renderTo: 'bat30',
      type: 'line',
      marginRight: 130,
      marginBottom: 25
  },credits: { enabled: false },
  title: {
      text: ' Battles ',
      x: -20 //center
  },
  subtitle: {
      text: 'Source: WorldofTanks.com',
      x: -20
  },
  xAxis: {
      categories: [<?printPast30days();?>]
  },
  yAxis: {
      title: {
          text: 'Average'
      },
      plotLines: [{
          value: 0,
          width: 1,
          color: '#808080'
      }]		,
					min: 0
  },
  tooltip: {
      formatter: function() {
              return ''+ this.series.name +'<br/>'+
              this.x +': '+ this.y +'';
      }
  },
  legend: {
      layout: 'vertical',
      align: 'right',
      verticalAlign: 'top',
      x: -10,
      y: 100,
      borderWidth: 0
  },
  series: [	{
          name: 'Battles',
          data: [ <?printStatOverPeriod(30,$_GET["name"],"battles","d");?>]
      }
			

]
});
	
	
	
	
	
	
	
	//global
	
	var colors = Highcharts.getOptions().colors,
			categories = ['Heavy', 'Medium', 'Light', 'SPG', 'TD'],
			name = 'Tank Classes',
			data = [{
					y: <?php printClassPercentage($_GET["name"],"ht",$s);?>,
					color: colors[0],
					drilldown: {
						name: 'Heavy Countries',
						categories: ['Russian', 'German', 'USA', 'French','Chinese','UK'],
						data: [<?php printClassPercentage($_GET["name"],"h");?>],
						color: colors[0]
					}
				}, {
					y: <?php printClassPercentage($_GET["name"],"mt");?>,
					color: colors[1],
					drilldown: {
						name: 'Medium Countries',
						categories: ['Russian', 'German', 'USA', 'French','Chinese','UK'],
						data: [<?php printClassPercentage($_GET["name"],"m");?>],
						color: colors[1]
					}
				}, {
					y: <?php printClassPercentage($_GET["name"],"lt");?>,
					color: colors[2],
					drilldown: {
						name: 'Light Countries',
						categories: ['Russian', 'German', 'USA', 'French','Chinese','UK'],
						data: [<?php printClassPercentage($_GET["name"],"l");?>],
						color: colors[2]
					}
				}, {
					y:<?php printClassPercentage($_GET["name"],"st");?>,
					color: colors[3],
					drilldown: {
						name: 'SPG Countries',
						categories: ['Russian', 'German', 'USA', 'French','Chinese','UK'],
					
						data: [<?php printClassPercentage($_GET["name"],"s");?>],
						color: colors[3]
					}
				}, {
					y: <?php printClassPercentage($_GET["name"],"tt");?>,
					color: colors[4],
					drilldown: {
						name: 'TD Countries',
						categories: ['Russian', 'German', 'USA', 'French','Chinese','UK'],
							data: [<?php printClassPercentage($_GET["name"],"t");?>],
						color: colors[4]
					}
				}];


		// Build the data arrays
		var browserData = [];
		var versionsData = [];
		for (var i = 0; i < data.length; i++) {

			// add browser data
			browserData.push({
				name: categories[i],
				y: data[i].y,
				color: data[i].color
			});

			// add version data
			for (var j = 0; j < data[i].drilldown.data.length; j++) {
				var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5 ;
				versionsData.push({
					name: data[i].drilldown.categories[j],
					y: data[i].drilldown.data[j],
					color: Highcharts.Color(data[i].color).brighten(brightness).get()
				});
			}
		}

		// Create the chart
		chart4 = new Highcharts.Chart({
			chart: {
				renderTo: 'container4',
				type: 'pie'
			},credits: { enabled: false },
			title: {
				text: 'Battles by Class / Country'
			},
			yAxis: {
				title: {
					text: ''
				},
				min: 0
			},
			plotOptions: {
				pie: {
					shadow: false
				}
			},
			tooltip: {
				formatter: function() {
					return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
				}
			},
			series: [{
				name: 'Classes',
				data: browserData,
				size: '60%',
				dataLabels: {
					formatter: function() {
						return this.y > 6 ? this.point.name : null;
					},
					color: 'white',
					distance: -30
				}
			}, {
				name: 'Countries',
				data: versionsData,
				innerSize: '60%',
				dataLabels: {
					formatter: function() {
						// display only if larger than 1
						return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y +'%'  : null;
					}
				}
			}]
		});
	
	
	//
	// Battles by Tier / Class
	//
	//
	
	var colors = Highcharts.getOptions().colors,
			categories = ['Heavy', 'Medium', 'Light', 'SPG', 'TD'],
			name = 'Tank Classes',
			data = [{
					y: <?php printClassPercentage($_GET["name"],"ht_t");?>,
					color: colors[0],
					drilldown: {
						name: 'Heavy Tiers',
						categories: ['1', '2', '3', '4','5','6','7','8','9','10'],
						data: [<?php printClassPercentage($_GET["name"],"h_t");?>],
						color: colors[0]
					}
				}, {
					y: <?php printClassPercentage($_GET["name"],"mt_t");?>,
					color: colors[1],
					drilldown: {
						name: 'Medium Tiers',
							categories: ['1', '2', '3', '4','5','6','7','8','9','10'],
						data: [<?php printClassPercentage($_GET["name"],"m_t");?>],
						color: colors[1]
					}
				}, {
					y: <?php printClassPercentage($_GET["name"],"lt_t");?>,
					color: colors[2],
					drilldown: {
						name: 'Light Tiers',
							categories: ['1', '2', '3', '4','5','6','7','8','9','10'],
						data: [<?php printClassPercentage($_GET["name"],"l_t");?>],
						color: colors[2]
					}
				}, {
					y:<?php printClassPercentage($_GET["name"],"st_t");?>,
					color: colors[3],
					drilldown: {
						name: 'SPG Tiers',
							categories: ['1', '2', '3', '4','5','6','7','8','9','10'],
					
						data: [<?php printClassPercentage($_GET["name"],"s_t");?>],
						color: colors[3]
					}
				}, {
					y: <?php printClassPercentage($_GET["name"],"tt_t");?>,
					color: colors[4],
					drilldown: {
						name: 'TD Tiers',
							categories: ['1', '2', '3', '4','5','6','7','8','9','10'],
							data: [<?php printClassPercentage($_GET["name"],"t_t");?>],
						color: colors[4]
					}
				}];


		// Build the data arrays
		var browserData = [];
		var versionsData = [];
		for (var i = 0; i < data.length; i++) {

			// add browser data
			browserData.push({
				name: categories[i],
				y: data[i].y,
				color: data[i].color
			});

			// add version data
			for (var j = 0; j < data[i].drilldown.data.length; j++) {
				var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5 ;
				versionsData.push({
					name: data[i].drilldown.categories[j],
					y: data[i].drilldown.data[j],
					color: Highcharts.Color(data[i].color).brighten(brightness).get()
				});
			}
		}

		// Create the chart
		chart5 = new Highcharts.Chart({
			chart: {
				renderTo: 'container5',
				type: 'pie'
			},credits: { enabled: false },
			title: {
				text: 'Battles by Class / Tier'
			},
			yAxis: {
				title: {
					text: ''
				}
			},
			plotOptions: {
				pie: {
					shadow: false
				}
			},
			tooltip: {
				formatter: function() {
					return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
				}
			},
			series: [{
				name: 'Classes',
				data: browserData,
				size: '60%',
				dataLabels: {
					formatter: function() {
						return this.y > 5 ? this.point.name : null;
					},
					color: 'white',
					distance: -30
				}
			}, {
				name: 'Tiers',
				data: versionsData,
				innerSize: '60%',
				dataLabels: {
					formatter: function() {
						// display only if larger than 1
						return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y +'%'  : null;
					}
				}
			}]
		});
	
	
	
	
}
</script>	
	
<?

	
}

$lockFile = "/home/tanks/update.lock";
$na_running=false;
if (is_file($lockFile))
	$na_running = true;
$lockFile = "/home/tanks/update_eu.lock";
$eu_running=false;
if (is_file($lockFile))
	$eu_running = true;	
$lockFile = "/home/tanks/update_na_2.lock";
$na_2_running=false;
if (is_file($lockFile))
	$na_2_running = true;
$lockFile = "/home/tanks/update_sea.lock";
$sea_running=false;
if (is_file($lockFile))
	$sea_running = true;


$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
echo '<div class="topbar"><span>Page generated in '.$total_time.' seconds.</span> <span class="right">Updating: ';
if ($na_running)
	echo '<span class="flair flair-us" style="margin-right:.5em">&nbsp;</span>NA ';
if ($na_2_running)
		echo '<span class="flair flair-us" style="margin-right:.5em">&nbsp;</span>NA_2 ';
if ($eu_running)
	echo '<span class="flair flair-eu" style="margin-right:.5em">&nbsp;</span>EU ';
if ($sea_running)
		echo '<span class="flair flair-sea" style="margin-right:.5em">&nbsp;</span>SEA ';
if (!$na_running and !$eu_running and !$na_2_running and !$sea_running)
	echo "Idle";
echo '</span></div>';
?>
<div class="topbar_nocolor" style="text-align:center">
	<BR><BR>
		<div style="display:inline-block">
	<iframe src="http://rcm.amazon.com/e/cm?t=tanklab-20&o=1&p=20&l=ur1&category=computers_accesories&banner=1JTZQ1BZ5NEPQ6QHSHG2&f=ifr" width="120" height="90" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>

	<iframe src="http://rcm.amazon.com/e/cm?t=tanklab-20&o=1&p=20&l=ur1&category=electronics&f=ifr" width="120" height="90" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>

	<iframe src="http://rcm.amazon.com/e/cm?t=tanklab-20&o=1&p=20&l=ur1&category=videogames&banner=1G3GV8H6WKRG674Y29R2&f=ifr" width="120" height="90" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>
	</div>
</div>