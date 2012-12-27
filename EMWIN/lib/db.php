<?php

// Database query library
print ("database: Database library loaded.\r\n");

$db0 = mysql_connect ($config['db']['host'], $config['db']['user'], $config['db']['password']);
mysql_select_db ($config['db']['db']);

function qbtdbq ($qry) {
	return (mysql_query ($qry));
	}

?>