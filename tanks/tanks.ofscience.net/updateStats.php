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
//World of Tanks Stats Generator
require_once 'libs/WOTLib.php';

$lockFile = "/home/tanks/update.lock";

function isProcessRunning($pidFile = '/var/run/myfile.pid') {
    if (!file_exists($pidFile) || !is_file($pidFile)) return false;
    $pid = file_get_contents($pidFile);
    return posix_kill($pid, 0);
}

if (is_file($lockFile)) {
	if (isProcessRunning($lockFile)){
	
	} else {
		unlink($lockFile);
	}
}

	$lockFile2 = "/home/tanks/update_na_2.lock";
	if (is_file($lockFile2)) {
		if (isProcessRunning($lockFile2)){
	
		}	else {
				unlink($lockFile2);
		}
	}


if (!is_file($lockFile)) {

//if (isProcessRunning($lockFile))
	//die('Already Running');
	
		$fh = fopen($lockFile,"w") or die("Can't open lock file");
		fwrite($fh,getmypid());
		fclose($fh);

		refreshStats();

		unlink($lockFile);
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);
		//echo "NA_1 Exec Time: " . round($total_time / 60,1) ." minutes\n";
} else {
	
	//Run secondary thread
	$lockFile = "/home/tanks/update_na_2.lock";
	if (isProcessRunning($lockFile))
		die();

	if (!is_file($lockFile)) {
			$fh = fopen($lockFile,"w") or die("Can't open lock file");
			fwrite($fh,getmypid());
			fclose($fh);

			refreshStats();

			unlink($lockFile);
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish = $time;
			$total_time = round(($finish - $start), 4);
		//	echo "NA_2 Exec Time: " . round($total_time / 60,1) ." minutes\n";
	
	} else {
	//running Two of them
	}
	
	//echo "NA - Update already running.\n";
}
?>
