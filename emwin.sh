#!/bin/bash

killall nc
nohup tail -F emlog.txt | nc -k -l 9944 &
while true; do
killall emwin.php
killall speedcheck.php
cd
echo "starting EMWIN..."
EMWIN/emwin.php > ~/emlog.txt
done