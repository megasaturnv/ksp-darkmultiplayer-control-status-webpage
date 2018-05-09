#!/bin/bash
echo `/bin/date --rfc-3339=seconds` ksp-dmp-server-tmux.service starting... >> /root/kspDmpServer.log
/usr/bin/tmux -2 new-session -d -s 'ksp_serv'
/usr/bin/tmux send-keys 'mono /root/DMPServer/DMPServer.exe' C-m
echo `/bin/date --rfc-3339=seconds` ksp-dmp-server-tmux.service started >> /root/kspDmpServer.log
