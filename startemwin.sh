#!/bin/bash
cd
nohup EMWIN/emwin.php > ~/emlog.txt &
nohup tail -F emlog.txt | nc -k -l 9944 &