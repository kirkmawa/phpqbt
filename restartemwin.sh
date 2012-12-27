#!/bin/bash

killall emwin.php
killall speedcheck.php
mysql -u user -ppassword < misc/resetspeedcheck.sql
cd
./startemwin.sh