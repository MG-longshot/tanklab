<?php
//World of Tanks Stats Generator
/**
 * Website: https://github.com/pcarrigg
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Paul Carrigg <pcarrigg@gmail.com>
 * @version 1.00 
 */

$server = "sea";
require_once 'libs/WOTLib.php';

$lockFile = "/home/tanks/update_sea.lock";
function isProcessRunning($pidFile = '/var/run/myfile.pid') {
    if (!file_exists($pidFile) || !is_file($pidFile)) return false;
    $pid = file_get_contents($pidFile);
    return posix_kill($pid, 0);
}
if (is_file($lockFile)) {
	if (isProcessRunning($lockFile)){
		die();
	} else {
		unlink($lockFile);
	}
} 
if (!is_file($lockFile)) {
	
		$fh = fopen($lockFile,"w") or die("Can't open lock file");
		fwrite($fh,getmypid());
		fclose($fh);

		refreshStats();

		unlink($lockFile);
} else {
	//echo "EU - Update already running.\n";
}
?>
