<?php

// EMWIN ByteBlaster client
// Implements EMWIN's Quick Block Transfer protocol.

$bbsock = NULL;
$connected = false;
$serverpos = 0;
$lastcomplete = 0;
$loginsenttime = 0;
$is_svr_list = false;

echo ("ByteBlaster: ByteBlaster QBT plugin loaded.\n");

function byteblaster_get () {
	global $config, $qbtdb, $bbsock, $connected, $serverpos, $lastcomplete, $loginsenttime, $products, $is_svr_list;
	$slres = $qbtdb->query ("SELECT * FROM `bbservers` WHERE `speed` < 2500 ORDER BY `speed` DESC");
	while ($slentry = $slres->fetch_assoc()) {
		$serverlist[] = $slentry['ip'] . ":" . $slentry['port'];
	}
	$numservers = count($serverlist);
	
	if (!$connected) {
		if ($bbsock) {
			socket_close ($bbsock);
			unset ($products);
			$products = array ();
		}
	    // not connected, we need to connect
		if ($serverpos == $numservers) {
			// go back to the first server
			$serverpos = 0;
		}
		$ipport = explode (":", $serverlist[$serverpos]);
		echo ("ByteBlaster: connecting to " . $serverlist[$serverpos] . "...\n");
		$bbsock = msConnectSocket ($ipport[0], $ipport[1], 5);
		if ($bbsock) {
			// socket is connected
			$serverpos++;
			$connected = true;
			$lastcomplete = time();
			$loginsenttime = 0;
		} else {
			// socket is not connected!
			// SHUT.
			// DOWN.
			// EVERYTHING.
			echo ("ByteBlaster: I HAVE FAILED YOU, MASTER\n");
			$serverpos++;
			$connected = false;
			return false;
		}
		// our job is done here
	}
	
	// we're connected, now what?
	// I'M GLAD YOU ASKED. NOW TO PULL 1116 BYTES FROM THE SERVER
	
	// Most ByteBlaster servers expect a login string at connect and every two minutes thereafter.
	if (time () - $loginsenttime > 115) {
		$loginstring = "phpqbt|NM-" . $config['byteblaster']['email'] . "|";
		// We need to xor the outgoing login string
		$loginxor = null;
		for ($l=0;$l<strlen($loginstring);$l++) {
			$loginxor .= chr(ord ($loginstring{$l}) ^ 255);
		}
		if (socket_write ($bbsock, $loginxor)) {
			echo ("ByteBlaster: sent login string\n");
			$loginsenttime = time();
		}
	}
	$chunk = null;
	// Set an alarm. If it takes longer than three seconds to receive a packet; terminate the process.
	// The parent shell script will clear the database and restart the software.
	pcntl_alarm (3);
	if ($insbyte = socket_read ($bbsock, 1116)) {
		// Unset the alarm.
		pcntl_alarm(0);
		for($i=0;$i<strlen($insbyte);$i++) {
			// The EMWIN data comes xor'd, we must reverse this
			$chunk .= chr(ord ($insbyte{$i}) ^ 255);
		}
		
		$good_data = true;
		$is_svr_list = false;
		//print ("ByteBlaster: read " . strlen ($chunk) . " bytes from socket\n");
		if ($config['byteblaster']['product_timeout'] > 0) {
			$sincecomp = time() - $lastcomplete;
			if ($sincecomp > $config['byteblaster']['product_timeout']) {
				echo ("ByteBlaster: New product not detected in the last " . $config['byteblaster']['product_timeout'] . " seconds; moving to next server\n");
				$connected = false;
			}
		}
	
		if (strpos ($chunk, "/ServerList/") === 0) {
			echo ("ByteBlaster: got new server list...\n");
			if ($config['byteblaster']['speedcheck']) {
				// get the server lists out of the server list
				preg_match_all ("#/.+/(.+)\\\\#U", $chunk, $svrlist1);
				$regserver = explode("|", $svrlist1[1][0]);
				$trash = array_pop ($regserver);
				$satserver = explode ("+", $svrlist1[1][1]);
				$trash = array_pop ($satserver);
				$servers = array_merge ($regserver, $satserver);
				$svrlist = "";
				foreach ($servers as $server) {
					$svrlist .= $server . "-";
				}
				passthru ("../bin/start EMWIN/speedcheck.php \"" . $svrlist . "\" >> /dev/null 2>&1 &");
			}
			$good_data = false;
			$is_svr_list = true;
			return null;
		}
		if ($good_data) {
			return $chunk;
		} else {
			return false;
		}
	} else {
		$connected = false;
		return false;
	}
}
