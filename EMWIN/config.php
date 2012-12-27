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
	
	
?>
