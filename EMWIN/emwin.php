#!/usr/bin/php
<?php
chdir (__DIR__);

declare (ticks=1);

require_once ("config.php");

// Load output plugins
foreach (glob ("./lib/*.php") as $libfile) {
	echo ("emwin: loading library file " . basename ($libfile) . "\n");
	require_once ($libfile);
}

// Include any input plugins from the input/ folder
foreach (glob ("./input/*.php") as $inputfile) {
	echo ("emwin: loading input plugin " . basename ($inputfile, ".php") . "\n");
	require_once ($inputfile);
}

// Include any processing plugins from the processing/ foler
foreach (glob ("./processing/*.php") as $procfile) {
	echo ("emwin: loading processing plugin " . basename ($procfile) . "\n");
	require_once ($procfile);
}
        
$products = array();
$lastclean = time ();

// Clear the speedcheck flag in the database. If the program is just starting, 
// there should be no speedcheck in progress.
$qbtdb->query ("UPDATE `phpqbt`.`state` SET `value` = 0 WHERE `name` = 'speedcheck'");

// If this process receives a SIGALRM, terminate the process. 
function alarm_handler ($signo) {
	echo ("EMWIN: caught SIGALRM, restarting...\n");
	exit;
}
pcntl_signal (SIGALRM, "alarm_handler");

while (true) {
	// Get the latest packet from the input plugin's function
	eval ('$chunk = ' . $config['inputplugin'] . '_get();');
	// Make sure it is a valid chunch and not a server list.
	if ($chunk && !$is_svr_list) {
		// Retrieve the header (first 86 bytes minus the 6 null bytes at the beginning)
		// We then explode by a / to get each element offsetting by 6 characters
		// the first entry is null and is trashed
		$chdr = substr ($chunk, 0, 86);
		$hdrent = explode ("/", $chdr, 6);
		$trash = array_shift ($hdrent);
		$filename = substr ($hdrent[0], 2);						//Filename
		preg_match ("/^PN (\\d+)+/", $hdrent[1], $numnow);		//Current Part
		preg_match ("/^PT (\\d+)+/", $hdrent[2], $numtotal);	//Total Parts
		preg_match ("/^CS (\\d+)+/", $hdrent[3], $csum);		//Checksum
		//If it is the first packet of a set we create a new array for each element
		if (!isset ($products[$filename])) {
			$products[$filename] = array();
			$products[$filename]["data"] = array();				//Data
			$products[$filename]["rcvdparts"] = 0;				//Recieved Parts
			$products[$filename]["totalparts"] = $numtotal[1];	//Total Parts
			$products[$filename]["checksum"] = array();			//Checksum
			$products[$filename]["last"] = time();				//Time it was last touched
		}
		
		// Now we get the data from the 86 bytes minus the last six into cdata
		// We store the packet data into the array based on its part number and increment our recieved parts

		echo ("EMWIN: got " . $filename . " part " . $numnow[1] . " of " . $numtotal[1] . "\n");
		$cdata = substr ($chunk, 86, -6);
		$idx = (string) $numnow[1]; //Get the packet number
		$products[$filename]["data"][$idx] = $cdata; // The actual data
		$products[$filename]["checksum"][$idx] = $csum[1]; // The expected checksum
		$compsum = 0; // Define a variable to hold the computed checksum
		for ($c=0;$c<strlen($cdata);$c++) {
			$compsum += ord ($cdata{$c});
		}
		if ($compsum == $csum[1]) {
			$products[$filename]["rcvdparts"]++;
		} else {
			echo ("EMWIN: Checksum mismatch (expected " . $csum[1] . "/got " . $compsum . "), ignoring " . $filename . " part " . $numnow[1] . "\n");
		}

		// If the file is done!
		if ($products[$filename]["rcvdparts"] == $products[$filename]["totalparts"]) {
			echo ("EMWIN: " . $filename . " completed*****\n");
			ksort ($products[$filename]["data"], SORT_NUMERIC);	// Sort out the index files to put them in order
			$fproduct = null;									// Full Product variable
			foreach ($products[$filename]["data"] as $fpiece) {
				$fproduct .= $fpiece;
			}
			$fproduct = rtrim ($fproduct, "\0");				// Righ trim the ascii null characters '\0'
			if (substr ($filename, -3) == "TXT") {
				// The following line breaks images. 
				$fproduct = str_replace ("\r", "", $fproduct);		//Convert to UNIX style line endings
			}
			if (substr ($filename, -3) == "ZIS") {
				// The right trim done above can kill ZIP archives. It shaves a few bytes off the EOCDR and
				// corrupts the file. However, we can fix this problem by detecting the length of the EOCDR
				// and re-adding the null bytes as needed.
				$eocdr = bin2hex (substr($fproduct, -22)); // EOCDR should be 22 bytes long, at the end of the file
        		$bytestoadd = strpos($eocdr, "504b0506") / 2; // the EOCDR's signature is 0x06054b50
        		if ($bytestoadd !== false && $bytestoadd > 0) {
        			echo ("EMWIN: fixing zip archive by adding " . $bytestoadd . " null bytes\n");
        			while ($bytestoadd > 0) {
        				$fproduct .= "\0";
        				$bytestoadd--;
        			}
        		}
			}
			unset ($products[$filename]);
			$lastcomplete = time();
			file_put_contents ("./products/" . $filename, $fproduct);
			// process via the filter if it's so defined
			if (function_exists ("proc_" . substr ($filename, 0, 3))) {
				eval ("proc_" . substr ($filename, 0, 3) . " (\$filename, \$fproduct);");
			}
		} else {// If not done :-(
			$products[$filename]["last"] = time();				// Last touch time
		}
	}
	
	// Clean up the products that fail to complete within 120 seconds to make way for a new products
	if (time () - $lastclean > 300) {
		echo ("EMWIN: performing cleanup...\n");
		foreach ($products as $fileent => $fdata) { //Fileent is the key and fdata is the array
			if (($fdata["rcvdparts"] < $fdata["totalparts"]) && (time () - $fdata["last"] > 120)) {
				echo ("EMWIN: cleanup: removing " . $fileent . "...\n");
				unset ($products[$fileent]);
			}
		}
		$lastclean = time();
	}
}
?>
