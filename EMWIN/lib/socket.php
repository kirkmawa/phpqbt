<?php

echo ("Socket: socket library loaded\r\n");

	function msConnectSocket($remote, $port, $timeout = 30) {
		# this works whether $remote is a hostname or IP 
		$ip = ""; 
		if( !preg_match('/^\d+\.\d+\.\d+\.\d+$/', $remote) ) {
			$ip = gethostbyname($remote); 
			if ($ip == $remote) {
				// echo ("Error Connecting Socket: Unknown host\n");
				return NULL; 
			}
		} else {
			$ip = $remote; 
		}
		if (!($sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
			// echo ("Error Creating Socket: ".socket_strerror(socket_last_error()) . "\n"); 
			return NULL; 
		}
		socket_set_nonblock($sock); 
		$error = NULL; 
		$attempts = 0; 
		$timeout *= 1000;  // adjust because we sleeping in 1 millisecond increments 
		$connected; 
		while (!($connected = @socket_connect($sock, $remote, $port+0)) && $attempts++ < $timeout) {
			$error = socket_last_error(); 
			if ($error == SOCKET_EISCONN) {
				$connected = true;
				break;
			}
			if ($error != SOCKET_EINPROGRESS && $error != SOCKET_EALREADY) { 
				// echo ("Error Connecting Socket: ".socket_strerror($error) . "\n"); 
				socket_close($sock); 
				return NULL; 
			} 
			usleep(1000); 
		} 
		if (!$connected) { 
			// echo ("Error Connecting Socket: Connect Timed Out After $timeout seconds. ".socket_strerror(socket_last_error()) . "\n"); 
			socket_close($sock); 
			return NULL; 
		} 
		socket_set_block($sock); 
		return $sock;      
	}

?>