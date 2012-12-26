<?php
#
# Known to work on the following examples
# $string = "KYZ068-069-079-080-083>087-114-116-111200-";
# $string = "ILZ001>003-005-IAC045-163-051200";
#
#  Use like so:
#
#  my @zones = parse_UGC("ILZ001>003-005-IAC045-163-051200");
#  foreach $zone (@zones) {
#     print "$zone.txt\n";
#  }
# KYZ 								State
# 068-079-080-083>087-114-116 		Each zone
# 111200 	expiration time that is already given inside the V-tech (inside product)
# Return the array of KYZ068, KYZ079 etc etc with the last element = time

function parse_UGC ($inp) {

	$zones = array ();//Initialize our zones
	foreach (explode ("\n", $inp) as $string) {//Get rid of all new lines
	//Search for Three letters and any number of digits and put the first 2 in state, the next letter in z and the rest in stuff
	if (preg_match ("/^(\D{2})(\D)(\d.*)$/", $string, $zwork1)) {
		$state = strtolower($zwork1[1]);
		$z = strtolower($zwork1[2]);
		$stuff = $zwork1[3];
		// echo ("state: $state   Z/C:$z   string: $string\n");
		$stuffa = explode("-", $stuff); // break out each number seperated by -'s
	
		foreach ($stuffa as $thing) {
			if (!preg_match ("/\d/", $thing, $throwaway)) { //if it isn't a digit then continue in the foreach
				continue;
			}
			// echo ("Got a zone: $thing\n");
			if (preg_match("/\d\d\d\d\d/", $thing, $throwaway)) { //If it is 5 digits it is the time at the end
				$expires = $thing;
				// echo ("Forecast expires $expires\n");
				continue;
			}
			if (preg_match ("/\w\w\w\d.*/", $thing, $throwaway)) { //if it is three characters, a digit, and any number of characters
				preg_match ("/(\w\w)(\w)(\d.*)/", $thing, $zwork2);//match (two characters)(one character)(and a digit and any number of characters
				$state = strtolower($zwork2[1]); //Store stuff like we did earlier
				$z = strtolower($zwork2[2]);
				$thing = $zwork2[3];
			}
	
			if (preg_match ("/>/", $thing, $throwaway)) { //this looks for a > and looks at the before and after using explode.
				// echo ("Got a string of zones: $thing\n"); // Then it figures out how many zeros it will have and puts each element
				$startend = explode (">", $thing);	//between the start and end of the range into the zones array
				$start = (int) $startend[0];
				while ($start <= $startend[1]) {
					$zeros = array ("", "0", "00");
					$numzero = 3 - strlen ($start);
					$startpad = $zeros[$numzero] . $start;
					$file = $state . $z . $startpad;
					$start++;
					$zones[] = $file;
				}
			} else { //If all else fails it is a normal digit and it is stored as KYZ089
				$file = $state . $z . $thing;
				$zones[] = $file;
			}
		}
	} else {
		# we got passed an unknown UGC format
		echo ("Got an unknown UGC format \"" . $inp . "\"\n");
		return 0;
	}
	}
	return $zones;
}

?>
