#!/usr/bin/php
<?php
chdir (__DIR__);

$inputplugins = array ("byteblaster");
$getfunc = "bb_get";

// Include any other files from the lib/ folder
if ($handle = opendir('./lib/')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && $entry != ".svn") {
            require_once ("./lib/" . $entry);
        }
    }
    closedir($handle);
}

// Include any input plugins from the input/ folder
if ($inhandle = opendir('./input/')) {
    while (false !== ($entry = readdir($inhandle))) {
        if ($entry != "." && $entry != ".." && $entry != ".svn") {
            require_once ("./input/" . $entry);
        }
    }
    closedir($inhandle);
}

// Include any processing plugins from the processing/ foler
if ($prochandle = opendir('./processing/')) {
    while (false !== ($entry = readdir($prochandle))) {
        if ($entry != "." && $entry != ".." && $entry != ".svn") {
            require_once ("./processing/" . $entry);
        }
    }
    closedir($prochandle);
}

$products = array();
$lastclean = time ();

// If this process receives a SIGALRM, terminate the process. 
function alarm_handler ($signo) {
	echo ("EMWIN: caught SIGALRM, restarting...\n");
	exit;
}
pcntl_signal (SIGALRM, "alarm_handler");

while (true) {
	// Get the latest packet from the input plugin's function
	eval ('$chunk = ' . $getfunc . '();');
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
			$products[$filename]["checksum"] = $csum[1];		//Checksum
			$products[$filename]["last"] = time();				//Time it was last touched
		}
		
		// Now we get the data from the 86 bytes minus the last six into cdata
		// We store the packet data into the array based on its part number and increment our recieved parts

		echo ("EMWIN: got " . $filename . " part " . $numnow[1] . " of " . $numtotal[1] . "\r\n");
		$cdata = substr ($chunk, 86, -6);
		$idx = (string) $numnow[1]; //Get the packet number
		$products[$filename]["data"][$idx] = $cdata;
		$products[$filename]["rcvdparts"]++;

		// If the file is done!
		if ($products[$filename]["rcvdparts"] == $products[$filename]["totalparts"]) {
			echo ("EMWIN: " . $filename . " completed*****\r\n");
			ksort ($products[$filename]["data"], SORT_NUMERIC);	// Sort out the index files to put them in order
			$fproduct = null;									// Full Product variable
			foreach ($products[$filename]["data"] as $fpiece) {
				$fproduct .= $fpiece;
			}
			$fproduct = rtrim ($fproduct, "\0");				// Righ trim the ascii null characters '\0'
			$fproduct = str_replace ("\r", "", $fproduct);		//Convert to UNIX style line endings
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
		echo ("EMWIN: performing cleanup...\r\n");
		foreach ($products as $fileent => $fdata) { //Fileent is the key and fdata is the array
			if (($fdata["rcvdparts"] < $fdata["totalparts"]) && (time () - $fdata["last"] > 120)) {
				echo ("EMWIN: cleanup: removing " . $fileent . "...\r\n");
				unset ($products[$fileent]);
			}
		}
		$lastclean = time();
	}
}
?>
