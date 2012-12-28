#!/bin/bash

while true; do
killall emwin.php
killall speedcheck.php
echo "starting EMWIN..."
php EMWIN/emwin.php > ./emlog.txt
done