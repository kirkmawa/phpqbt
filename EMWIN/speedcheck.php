#!/usr/bin/php -q
<?php
	error_reporting ('E_NONE');
	
	require_once ("config.php");
	require_once ("lib/db.php");
	
	// Check to see if an instance of this file is already running. If not, set the flag
	$stres = $qbtdb->query("SELECT * FROM `state` WHERE `name` = 'speedcheck' LIMIT 1");
	$state = $stres->fetch_assoc();
	if ($state['value']) {
		echo ("BBSERVERS: already processing a server list\n");
		exit;
	}
	$qbtdb->query("UPDATE `state` SET `value` = 1 WHERE `name` = 'speedcheck' LIMIT 1");
/*

1. Take server list from EMWIN client
2. Process server list into array called $servers with elements containing server:port
3. Leave the rest alone

*/
	require_once ("lib/socket.php");
	
	//server list is passed as the only commandline argument
	$serverlist = $argv[1];
	$servers = array ();
	$svdatalen = array ();
	
	/*
	// get the server lists out of the server list
	preg_match ("#/ServerList/(.+)\\\\ServerList\\\\/SatServers/(.+)\\\\SatServers\\\\#", $serverlist, $svrlist1);
	$regserver = explode("|", $svrlist1[1]);
	$trash = array_pop ($regserver);
	$satserver = explode ("+", $svrlist1[2]);
	$trash = array_pop ($satserver);
	
	$servers = array_merge ($regserver, $satserver);
	*/
	if (strpos ($serverlist, "-") === FALSE) {
		echo ("BBSERVERS: did not get a server list; aborting...\n");
		exit;
	}
	$servers = explode ("-", $serverlist);
	$trash = array_pop ($servers);
	
	echo ("BBSERVERS: Got server list... " . count ($servers) . " servers found\n");
	// Now that we have the servers, we need to connect to each for five seconds and see how much data we pull
	
	foreach ($servers as &$server) {
		$ipport = explode (":", $server);
		$ip = ""; 
		if( !preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ipport[0]) ) {
			$ip = gethostbyname($ipport[0]); 
			if ($ip == $ipport[0]) {
				echo ("Tossed out invalid server " . $server . "\n");
				continue; 
			}
		} else {
			$ip = $ipport[0];
		}
		$emdata = "";
		echo ("BBSERVERS: Connecting to " . $server . " (" . $ip . ":" . $ipport[1] . ")... ");
		$server = $ip . ":" . $ipport[1];
		$bbstest = msConnectSocket ($ip, $ipport[1], 5);
		if ($bbstest === NULL) {
			echo ("connection failed or timed out\n");
		} else {
			$curtime = microtime (true);
			while (strlen ($emdata) < 5120) {
				$emdata .= socket_read ($bbstest, 1500);
				if ((strlen ($emdata) < 2560) && ((microtime (true) - $curtime) > 5)) {
					echo ("connected but too slow!... ");
					break;
				}
			}
			$newtime = microtime (true);			
			$tottime = $newtime - $curtime;
			$datalen = strlen ($emdata);
			$coeff = round ($datalen / $tottime);
			socket_close ($bbstest);
			echo ("downloaded " . $datalen . " bytes in " . $tottime . " seconds. Speed rating is " . $coeff . "\n");
			// Check for already existing server
			$svrcheck = $qbtdb->query("SELECT * FROM `bbservers` WHERE `ip` = '" . $ip . "'");
			if ($svrcheck->num_rows > 0) {
				// exists
				$qbtdb->query ("UPDATE `bbservers` SET `port` = " . $ipport[1] . ", `speed` = " . $coeff . ", `updated` = '" . date ('Y-m-d H:i:s') . "' WHERE `ip` = '" . $ip . "' LIMIT 1");
			} else {
				// doesn't exist, add new server
				$qbtdb->query ("INSERT INTO `bbservers` (`ip`, `port`, `speed`, `updated`) VALUES ('" . $ip . "', " . $ipport[1] . ", " . $coeff . ", '" . date ('Y-m-d H:i:s') . "')");
			}
		}
	}
	
	// Clean up servers that do not exist
	echo ("BBSERVERS: Cleaning up database...\n");
	$svrdb = array ();
	$res9 = $qbtdb->query("SELECT * FROM `bbservers` WHERE 1");
	while ($svrent = $res9->fetch_assoc()) {
		$svrdb[] = $svrent['ip'] . ":" . $svrent['port'];
	}
	
	$svrdiff = array_diff ($svrdb, $servers);
	echo ("Here are the two lists!\n");
	echo ("Our server actual server list\n");
	print_r($svrdb);
	echo ("Our server list recieved\n");
	print_r($servers);
	echo ("Here is what is different\n");
	print_r($svrdiff);
	foreach ($svrdiff as $svr2del) {
		$svr2del = explode (":", $svr2del);
		echo ("We are going to delete " . $svr2del[0] . "\n");
		$qbtdb->query ("DELETE FROM `bbservers` WHERE `ip` = '" . $svr2del[0] ."' AND `port` = " . $svr2del[1] . " LIMIT 1");
	}
	
	//Unset the in-use flag
	$qbtdb->query ("UPDATE `state` SET `value` = 0 WHERE `name` = 'speedcheck' LIMIT 1");

?>
