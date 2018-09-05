<?php
require 'settings.inc.php';

//echo $LOG_FILE_ERROR; //Temporary code to help debug why $LOG_FILE_ERROR is reported as undefined in the logs (only when no POST data is received)
//echo '</br>';
//echo isset($LOG_FILE_ERROR); //This line contradits the Apache error logs...
//echo '</br>';

//Log errors
/* Commented out due to problems
if ($LOG_ERROR_ENABLED) {
	//Function for error handling (remember errors are suppressed on remote hosts)
	function customError($errno, $errstr) {
		//echo "<br><b>Error: </b> [$errno] $errstr<br>";
		error_log(date (DATE_RSS)." Error: [$errno] $errstr".chr(13).chr(10),3,$LOG_FILE_ERROR); //To do: Find out why  $LOG_FILE_ERROR is reported as undefined in the error logs (only when no POST data is received)
	}
	set_error_handler('customError');
}
*/


//Log who accessed this page
if ($LOG_ACCESS_ENABLED) {
	file_put_contents("$LOG_FILE_ACCESS", date('Y-m-d H:i:s') . ' - ' .$_SERVER['REMOTE_ADDR']. PHP_EOL, FILE_APPEND);
}

//echo var_dump($_SERVER);
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	//echo var_dump($_POST);
	if (isset($_POST['Action'])) {
		if ($_POST['Action'] == 'on') {
			$message = 'Turning on server...';
			$output = shell_exec('wakeonlan "' .$DMP_SERVER_MAC_ADDRESS. '"');
		} elseif ($_POST['Action'] == 'off') {
			$message = 'Turning off server...';
			$output = shell_exec('(ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i \'' .$DMP_SERVER_SSH_PRIVATE_KEY_PATH. '\' ' .$DMP_SERVER_SSH_USERNAME. '@' .$DMP_SERVER_SSH_HOSTNAME_IP. ' poweroff) 2>&1');
		} elseif ($_POST['Action'] == 'status') {
			$message = 'Checking server status...';
			$output = ' ';
		} elseif ($_POST['Action'] == 'clearUnknownObjectAsteroids') {
			$message = 'Clearing unknown objects... (Untracked asteroids)';
			//Commented out for testing: $output = shell_exec'(ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i \'' .$DMP_SERVER_SSH_PRIVATE_KEY_PATH. '\' ' .$DMP_SERVER_SSH_USERNAME. '@' .$DMP_SERVER_SSH_HOSTNAME_IP. ' grep -rlZ PotatoRoid ' .$DMP_SERVER_UNIVERSE_VESSELS_DIRECTORY. ' | xargs --null rm -f) 2>&1';
			//For testing: (print shell command, don't execute
			$output = '(ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i \'' .$DMP_SERVER_SSH_PRIVATE_KEY_PATH. '\' ' .$DMP_SERVER_SSH_USERNAME. '@' .$DMP_SERVER_SSH_HOSTNAME_IP. ' grep -rlZ PotatoRoid ' .$DMP_SERVER_UNIVERSE_VESSELS_DIRECTORY. ' | xargs --null rm -f) 2>&1';
			//To do: Implement alternative option and move unknown objects to another location instead of deleting them. Remember to execute command after testing is complete
		}
		if ($LOG_ACTION_ENABLED) {
			$logFileMessage = trim(preg_replace('/\s+/', ' ',  date('Y-m-d H:i:s') . ' - IP: ' .$_SERVER['REMOTE_ADDR']. ' - ' .$_SERVER['REQUEST_METHOD']. ' - Message: ' .$message. ' - Output: ' .$output));
			file_put_contents("$LOG_FILE_ACTION", $logFileMessage . PHP_EOL, FILE_APPEND);
		}
	}
	if (isset($_POST['Command'])) {
		if (in_array($_POST['Command'], $VALID_KSP_DMP_COMMANDS_ARRAY)) { //Check if $_POST["Command"] is in an array of valid commands
			//Execute the command, $_POST['Command'], as stdin for DMPServer.exe by commecting to the tmux session in which DMPServer.exe is running
			$output = shell_exec('(ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i \'' .$DMP_SERVER_SSH_PRIVATE_KEY_PATH. '\' ' .$DMP_SERVER_SSH_USERNAME. '@' .$DMP_SERVER_SSH_HOSTNAME_IP. ' "tmux send-keys \'' .$_POST['Command']. '\' C-m ; sleep 1 ; tmux capture-pane -pJ") | sed \'/^\s*$/d\' | tail -n 2');
			//To do: Allow te above command to handle multiline-outputs from DMPServer.exe. For example, the command '/connectionstats'
			$message = 'Executing command: ' .$_POST['Command'];
		} else {
			$message = 'Command not valid: "' .$_POST['Command']. '"'; //If command is not valid, return an error
		}
		if ($LOG_ACTION_ENABLED) {
			$logFileMessage = trim(preg_replace('/\s+/', ' ',  date('Y-m-d H:i:s') . ' - IP: ' .$_SERVER['REMOTE_ADDR']. ' - ' .$_SERVER['REQUEST_METHOD']. ' - Message: ' .$message. ' - Output: ' .$output));
			file_put_contents("$LOG_FILE_ACTION", $logFileMessage . PHP_EOL, FILE_APPEND);
		}
	}
	if (isset($message)) {
		if ($output == '' && $message != '') { //If output is empty, but message contains something
			$message .= '\nError. Command failed'; //Display an error because the command had no output
		}
	}
}
?>
<html>
<head>
<title>KSP DarkMultiplayer Status & Control</title>
<style>
	* {
		font-family: Consolas;
	}
	h1 {
		margin: 10px;
	}
	.button {
		background-color: #e7e7e7; /* Grey */
		padding: 10px 30px;
		border: none;
		border-radius: 10px;
		margin: 5px 5px;
		color: #fff;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-size: 14pt;
		cursor: pointer;
		outline: none;
		width: 280px;
		height: 80px;
		box-shadow: 0 9px #999;
	}

	.button:active {
		box-shadow: 0 5px #666;
		transform: translateY(4px);
	}

	.buttonred        {background-color: #f44336;} /* Red */
	.buttonred:hover  {background-color: #c44336;}
	.buttonred:active {background-color: #c44336;}

	.buttongreen         {background-color: #4eaf50;} /* Green */
	.buttongreen:hover   {background-color: #3e8e41;}
	.buttongreen:active  {background-color: #3e8e41;}

	.buttonblue        {background-color: #6479fc;} /* Blue */
	.buttonblue:hover  {background-color: #5061c9;}
	.buttonblue:active {background-color: #5061c9;}

	.buttonamber        {background-color: #ffbf00;} /* Amber */
	.buttonamber:hover  {background-color: #efaf00;}
	.buttonamber:active {background-color: #efaf00;}
</style>
</head>
<body>
<div class="center">
<h1>KSP DarkMultiPlayer Control<?php
if (isset($_POST['Action'])) {
	if ($_POST['Action'] == 'status') {
		echo '. Status: ';
		if (shell_exec('nc -z -v -w 2 ' .$DMP_SERVER_SSH_HOSTNAME_IP. ' ' .$DMP_SERVER_PORT. ' > /dev/null 2>&1; echo -n $?') == '0') { //To do: Find a way to test if port is open/closed in PHP, without netcat
			echo '<span style="color:#4eaf50">Online</span>';
		} else {
			echo '<span style="color:#f44336">Offline</span>';
		}
	}
}
?></h1>
</div>
<table>
	<tr>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttongreen" type="submit" name="Action" value="on" title="Send 'wakeonlan' request to KSP DarkMultiPlayer server">Turn KSP Server On</button>
			</form>
		</td>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonred" type="submit" name="Action" value="off" title="Shutdown KSP DarkMultiPlayer server" onclick="return confirm('Are you sure you want to turn off the server?');">Turn KSP Server Off</button>
			</form>
		</td>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonamber" type="submit" name="Action" value="status" title="Check if KSP DarkMultiPlayer server is online or offline">Check Server Status</button>
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttongreen" type="submit" name="Action" value="startservice" title="Start DarkMultiPlayer Service (WIP)" disabled="disabled">Start DarkMultiPlayer Service (WIP)</button>
			</form>
		</td>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonred" type="submit" name="Action" value="stopservice" title="Stop DarkMultiPlayer Service" disabled="disabled">Stop DarkMultiPlayer service (WIP)</button>
			</form>
		</td>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonamber" type="submit" name="Action" value="backup" title="Create a backup of the KSP DarkMultiPlayer universe" disabled="disabled">Backup the KSP Universe (WIP)</button>
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="4"></td>
		<td colspan="4"></td>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonamber" type="submit" name="Action" value="update" title="Run DMPUpdater.exe" disabled="disabled">Update KSP DarkMultiPlayer (WIP)</button>
			</form>
		</td>
	</tr>
	<tr><td><br /></td></tr>
	<tr>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonblue" type="submit" name="Command" value="/dekessler" title="Clears out debris from the server">/dekessler</button>
			</form>
		</td>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonblue" type="submit" name="Command" value="/nukeksc" title="Clears ALL vessels from KSC and the Runway">/nukeksc</button>
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonblue" type="submit" name="Command" value="/listclients" title="Lists connected clients">/listclients</button>
			</form>
		</td>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonblue" type="submit" name="Command" value="/countclients" title="Counts connected clients">/countclients</button>
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonblue" type="submit" name="Command" value="/connectionstats" title="Displays network traffic usage" disabled="disabled">/connectionstats (WIP)</button>
			</form>
		</td>
		<td colspan="4">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<button class="button buttonblue" type="submit" name="Action" value="clearUnknownObjectAsteroids" title="Clear unknown objects (Untracked asteroids) from the universe" disabled="disabled">Clear unknown objects (WIP)</button>
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="6"><b>Messages:</b></td>
		<td colspan="6"><b>Command Output:</b></td>
	</tr>
	<tr>
		<td colspan="6">
			<textarea style="height:150px; width:100%" name="TextareaMessages" disabled="disabled"><?php
			if (isset($message)) {
				echo $message;
			} ?></textarea>
		</td>
		<td colspan="6">
			<textarea style="height:150px; width:100%" name="TextareaOutput" disabled="disabled"><?php
			if (isset($output)) {
				echo $output;
			} ?></textarea>
		</td>
	</tr>
</table>
</body>
</html>
