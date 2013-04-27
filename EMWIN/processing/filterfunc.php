<?php
	
	// This file has common code blocks for handling filter code tasks
	
	// array get_vtec (string $fulltext)
	// Use on all products
	function get_vtec ($fulltext) {
		// match all instances of /O.<whatever>Z/
		preg_match_all ("#/O.(.+)Z/#", $fulltext, $vtecout);
		return ($vtecout[0]);
	}
	
	// string get_ugc (string $fulltext)
	// Use on all products
	function get_ugc ($fulltext) {
		// get and parse all UGC zones and return in a database friendly format
		preg_match_all ("/\\w{2}[CZ]\\d{3}(.*)\\d{6}-\\n/msU", $fulltext, $wugc);
		$ugc = array();
		foreach ($wugc[0] as $ugcpt) {
			$ugcpt = str_replace ("\n", "", $ugcpt);
			$ugc[] = implode (parse_UGC ($ugcpt));
		}
		$ugc = implode ($ugc);
		return $ugc;
	}
	
	// string dbid_wng (string $vtec)
	// Use on: SVR TOR FFW
	function dbid_wng ($vtec) {
		// get the vtec and return the calculated database ID
		$vtdata = explode (".", $vtec);
		$wfo = $vtdata[2];
		$type = substr ($vtdata[3], 0, 1);
		$etn = $vtdata[5];
		$dbid = $wfo . $type . $etn;
		return $dbid;
	}
	
	// string get_polygon (string $fulltext)
	// Use on: SVR TOR FFW
	function get_polygon ($fulltext) {
		// Return polygon in database friendly format
		preg_match ("/LAT\\.\\.\\.LON (.+)\\w/ms", $fulltext, $polygon);
		preg_match_all ("/\\d{4,5}/", $polygon[1], $polygon2);
		$polygon = implode (" ", $polygon2[0]);
		return $polygon;
	}
	
	// array get_tmloc (string $fulltext)
	// Use on: SVR TOR
	function get_tmloc ($fulltext) {
		// Return motion vector and location in an array to be mapped
		$rtn2 = array();
		preg_match ("/TIME\\.\\.\\.MOT\\.\\.\\.LOC \\d+Z (\\d+)DEG (\\d+)KT (\\d{4,5}) (\\d{4,5})/m", $fulltext, $tml);
		$brng = $tml[1];
		$spd = $tml[2] * 1.85200;
		$y1 = $tml[3] / 100;
		$x1 = $tml[4] / 100;
		$xy2 = geo_destination (array($y1, $x1), $spd, $brng);
		$y2 = $xy2[0];
		$x2 = $xy2[1];
		$rtn2['lat1'] = $y1;
		$rtn2['lng1'] = $x1;
		$rtn2['lat2'] = $y2;
		$rtn2['lng2'] = $x2;
		return $rtn2;
	}
		
	
	// array evt_times (string $vtec)
	// Use on all products
	function evt_times ($vtec) {
		//return the effective and expired times in the vtec
		$vtdata = explode (".", $vtec);
		$efftimes = explode ("-", $vtdata[6]);
		$efftime = $efftimes[0];
		$exptime = $efftimes[1];
		$times = array();
		preg_match ("/(\\d{2})(\\d{2})(\\d{2})T(\\d{2})(\\d{2})Z/", $efftime, $efftmspl);
		$times['eff'] = gmmktime ($efftmspl[4], $efftmspl[5], 0, $efftmspl[2], $efftmspl[3], ($efftmspl[1] + 2000));
		preg_match ("/(\\d{2})(\\d{2})(\\d{2})T(\\d{2})(\\d{2})Z/", $exptime, $exptmspl);
		$times['exp'] = gmmktime ($exptmspl[4], $exptmspl[5], 0, $exptmspl[2], $exptmspl[3], ($exptmspl[1] + 2000));
		return $times;
	}
	
	// array all_ww_points (string $fulltext)
	// Use on: SEV
	function all_ww_points ($fulltext) {
	// returns the array of each point for each watch
		// return an array of 
		preg_match_all ("/SEVR.+;/msU", $fulltext, $watches);
		$wdata = array();
		foreach ($watches[0] as $watch) {
			preg_match ("/W[ST](\\d{4})/", $watch, $wnum);
			$wline = explode ("\n", $watch);
			preg_match_all ("/\\d{5}/", $wline[1], $pointsa);
			$points = "";
			foreach ($pointsa[0] as $point) {
				$point = ltrim ($point, "0");
				$points .= $point . " ";
			}
			$wdata[$wnum[1]] = substr ($points, 0, -1);
        }
		return $wdata;
	}
	
	// string get_pg_timezone (string $polygon)
	// Use on: SVR TOR
	function get_pg_timezone ($polygon) {
	// Returns the timezone for the center of the polygon
		global $ustzid;
		$plysplit = explode (" ", $polygon);
		$cnt = 1;
		$lat = array ();
		$longi = array ();
		foreach ($plysplit as $point) {
			$point = $point / 100;
			if ($cnt % 2 == 1) {
				//echo ("LAT: " . $point . " ");
				// This is a latitude point
				array_push ($lat, $point);
			} else {
				//echo ("LONG: -" . $point . "\n");
				// This is a longitude point
				array_push ($longi, $point);
			}
			$cnt++;
		}
		sort ($lat, SORT_NUMERIC);
		sort ($longi, SORT_NUMERIC);
		$lats = (count ($lat) - 1);
		$longis = (count ($longi) - 1);
		$min_lat = $lat[0];
		$max_lat = $lat[$lats];
		$min_long = $longi[0];
		$max_long = $longi[$longis];
		// Get ctr lat
		$avglat = (($min_lat + $max_lat) / 2);
		// Get ctr long
		$avglong = (($min_long + $max_long) / 2);
		// We mainly serve the western hemisphere, so this value needs to be negative
		$avglong = $avglong - ($avglong * 2);
		// Get timezone for this location
		$pointLoc = new pointLocation();
        foreach ($ustzid as $wdata) {
			$point = $avglong . " " . $avglat;
			$polygon = $wdata['points'];
			$locstatus = $pointLoc->pointInPolygon($point, $polygon);
			if ($locstatus === "inside" || $locstatus === "boundary") {
					return $wdata['name'];
			}
        }
	}