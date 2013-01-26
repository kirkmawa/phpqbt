<?php

// Database query library
print ("database: Database library loaded.\n");

$qbtdb = new mysqli ($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['db']);

?>
