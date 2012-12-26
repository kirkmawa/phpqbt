<?php

// Database query library
print ("database: Database library loaded.\r\n");

$db0 = mysql_connect ("localhost", "user", "password");
mysql_select_db ("phpqbt");

function qbtdbq ($qry) {
	return (mysql_query ($qry));
	}

?>