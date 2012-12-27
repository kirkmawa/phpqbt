#!/bin/bash

while true; do
killall emwin.php
killall speedcheck.php
mysql -u user -ppassword < misc/resetspeedcheck.sql
echo "starting EMWIN..."
php EMWIN/emwin.php > ./emlog.txt
done