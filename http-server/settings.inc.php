<?php
//Note: Web server user (e.g. www-data) must have read/write access to log file and read access to id_rsa file

$LOG_ERROR_ENABLED                     = True;
$LOG_FILE_ERROR                        = '/var/www/html/ksp_dmp_Error.log'; //The location where a log of errors should be stored

$LOG_ACCESS_ENABLED                    = True;
$LOG_FILE_ACCESS                       = '/var/www/html/ksp_dmp_Access.log'; //The location where a log of access to the webpage should be stored

$LOG_ACTION_ENABLED                    = True;
$LOG_FILE_ACTION                       = '/var/www/html/ksp_dmp_Action.log'; //The location where a log of all actions (button presses / commands) should be stored

$DMP_SERVER_MAC_ADDRESS                = '01:23:45:67:89:ab'; //The DarkMultiPlayer server's mac address, used for wakeonlan
$DMP_SERVER_SSH_PRIVATE_KEY_PATH       = '/var/www/html/ksp_dmp_sshkey/id_rsa'; //The location of a private key which can be used to SSH into the KSP DarkMultiPlayer server
$DMP_SERVER_SSH_USERNAME               = 'root'; //A username on the DarkMultiPlayer server which is running DarkMultiPlayer in a tmux session and has read/write access to the DMP server folder. If using the root user, make sure root login is enabled over SSH with a key (root login with password is not required).
$DMP_SERVER_SSH_HOSTNAME_IP            = '192.168.1.64'; //Hostname or IP address of the DarkMultiPlayer server
$DMP_SERVER_PORT                       = '6702'; //The port which the DarkMultiPlayer server is running on
$DMP_SERVER_UNIVERSE_VESSELS_DIRECTORY = '/root/DMPServer/Universe/Vessels'; //The location of the KSP universe's vessels folder on the DarkMultiPlayer server

$VALID_KSP_DMP_COMMANDS_ARRAY          = array('/dekessler', '/nukeksc', '/listclients', '/countclients', '/connectionstats'); //List of valid commands which will be executed directly by DMPServer.exe. Any other commands will be ignored and return an error.
?>
