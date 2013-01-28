<?php

	// phpqbt global configuration
	
	// Initialize the global config array
	$config = array ();
	
	// DATABASE SETTINGS
	$config['db']['host'] = "localhost";
	$config['db']['user'] = "user";
	$config['db']['password'] = "password";
	$config['db']['db'] = "phpqbt";
	
	// INPUT PLUGIN CONTROL
	// Set the input plugin here. phpqbt will append _get to the name and 
	// call that function to get a chunk from the source, be it serial, byteblaster, etc
	$config['inputplugin'] = "byteblaster";
	
	// BYTEBLASTER PLUGIN
	
	// Set your email address. This will be sent to the EMWIN servers as a login string.
	$config['byteblaster']['email'] = "phpqbt@drewkirkman.com";
	
	// If no complete product is received from a ByteBlaster server in the specified 
	// number of seconds, bail and move to the next server. 0 to disable.
	$config['byteblaster']['product_timeout'] = 0;
	
	// Update ByteBlaster server lists from ServerList packets received in the stream.
	// Set to false to never update the server list automatically
	$config['byteblaster']['speedcheck'] = true;
	
	
?>
