#!/bin/bash
echo `/bin/date --rfc-3339=seconds` ksp-dmp-server-tmux.service stopping... >> /root/kspDmpServer.log
/usr/bin/tmux send-keys "/exit" C-m
sleep 5
/usr/bin/tmux send-keys C-d
echo `/bin/date --rfc-3339=seconds` ksp-dmp-server-tmux.service stopped >> /root/kspDmpServer.log
