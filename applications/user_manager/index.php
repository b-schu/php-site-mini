<?PHP

$APP = "User Manager";

$ROWCOUNT = 20;

if (!$m->check_auth($_SESSION["username"], $APP)) {
	header("Location: $ROOT");
}

function print_users_table($users) {
	global $APP;
	global $s;
	?>
	<table style="border-top:1px solid #555555;" width="100%" cellspacing="0">
	<tr>
	<td class="header">ID</td>
	<td class="header">Name</td>
	<td class="header">Display Name</td>
	<td class="header">E-mail</td>
	<td class="header">Joined<br>(mm/dd/yyyy)</td>
	<td class="header">Logins</td>
	<td class="header">User Type</td>
	<td class="header">Status</td>
	<td class="header">Actions</td>
	</tr>
	<?
	for ($i=0; $i<count($users); $i++) {
		if ($color == "background-color:#eeeeee;") {
			$color = "background-color:white;";
		} else {
			$color = "background-color:#eeeeee;";
		}
		print "<tr>\n";
		print "<td class='cell' style='$color'>".$users[$i]["id"]."</td>\n";
		print "<td class='cell' style='$color'>".$users[$i]["name"]."</td>\n";
		print "<td class='cell' style='$color'>".$users[$i]["display_name"]."</td>\n";
		print "<td class='cell' style='$color'>".$users[$i]["email"]."</td>\n";
		print "<td class='cell' style='$color'>".$users[$i]["joined"]."</td>\n";
		print "<td class='cell' style='$color'>".$users[$i]["logins"]."</td>\n";
		print "<td class='cell' style='$color'>".$users[$i]["user_type"]."</td>\n";
		print "<td class='cell' style='$color'>".$users[$i]["status"]."</td>\n";
		print "<td class='cell' style='$color'>\n";
		?>
		<a href="<? print $m->app_link($APP); ?>&action=edituser&user=<? print $users[$i]["name"]; ?>" style='color:blue;'>[e]</a>dit  
		<a href="<? print $m->app_link($APP); ?>&action=delete&user=<? print $users[$i]["name"]; ?>" style='color:red;'>[d]</a>elete
		<?
		print "</td>\n";
		print "</tr>\n";
	}
	?>
	</table>
	<?
}

function print_menu() {
	global $APP;
	global $s;
	?>
	<div style='text-align:center;font-size:18px;'>
	<a href="<? print $m->app_link($APP); ?>" style="color:green;">View Users</a> | 
	<a href="<? print $m->app_link($APP); ?>&action=addgroup" style='color:black'>Add Users</a> | 
	<a href="<? print $m->app_link($APP); ?>&action=type" style='color:blue'>Manage Types</a>
	<br><br>
	<form action="<? print $m->app_link($APP); ?>" method="post">
	<input type="hidden" name="action" value="search">
	<input type="text" name="args" size="10">
	<input type="submit" value="Search">
	</form>
	</div>
	<hr />
	<?
}

function search($args) {
	global $s;
	$result = $m->dbiquery("SELECT * FROM users;");
	$retval = array();
	while($row = mysql_fetch_assoc($result)) {
		$argsx = explode(" ", $args);
		foreach($argsx as $a) {
			if (trim($a) != "") {
				if (preg_match("/$a/",$row["name"])) {
					$retval[] = $row;
				}
			}
		}
	}
	return $retval;
}

function print_group_registration_form() {
	global $APP;
	global $s;
	?>
	<br>
	<center>
	<div style="text-align:left;width:300px;">
	<b>Registration:</b><br>
	<br>
	<form action="<? print $m->app_link($APP); ?>" method="POST">
	<input type="hidden" name="action" value="submitreg_group">
	Enter user(s)' names <br>
	separated by a comma:<br>
	<textarea rows="5" cols="30" name="usernames"></textarea><br>
	Password: <br>
	<input type="password" size="25" name="password"><br>
	Password Confirm: <br>
	 <input type="password" size="25" name="passconfirm"><br>

	<?php
	$types = $m->get_user_types();
	?>
	<br>User Type: 
	<select name="type">
	<?php
	for($i=0; $i<count($types); $i++) {	
		if ($types[$i] != "superuser") {
			print "<option value=\"".$types[$i]."\">".$types[$i]."\n";
		}
	}
	?>
	</select>
	<br><br>

	<input type=submit value="Register">
	</form>
	</div>
	</center>
	<?PHP
}

$headerinfo = "
	<html>
	<head>
	<title>SoPi - Admin Panel</title>
	<style type='text/css'>
	.header{
		padding-left:10px;
		padding-right:10px;
		background-color:#999999;
		font-weight:bold;
		font-size: 18px;
	}
	.cell{
		padding-left:10px;
		padding-right:10px;
		background-color:#FFFFFF;
		font-size: 18px;
	}

	</style>	
	</head>
";
$m->print_header($headerinfo);
?>
<div id="body-container">
<?

$action = $_POST["action"];
if ($action == "") {
	$action = $_GET["action"];
}
if ($action == "") {
	$action = "home";
}

print_menu();

if ($action == "search") {
	$args = $_POST["args"];
	$users = search($args);
	if (count($users) == 0) {
		print "no results found...";
	} else {
		print_users_table($users);
	}
}

if ($action == "userdetails") {
	$username = $_POST['username'];
	$user = $m->get_user_by_name($username);
	$user_logins = $user["logins"];
	$user_products = get_user_apps($username);
	?>
	<!-- Login Table -->
	<strong><?php print $username; ?> has logged in <br><?php print count($user_logins); ?> times.</strong>
	<br><br>
	<!-- end Login Table -->
	<br>

	</td>
	<td valign="top" align="left">
	
	<strong>User Apps</strong>
	<table border=1>
	<?php
	foreach($user_apps as $app) {
		print "<tr>\n<td>\n";
		print $app."\n";
		print "</td>\n</tr>\n";
	}
	?>
	</table>

	<?php

}

if ($action == "submitreg_group") {
	$users_tmp = $_POST['usernames'];
	$users = preg_split("/[\,]/",$users_tmp);	
	$pass = $_POST['password'];
	$type = $_POST['type'];
	$passconfirm = $_POST['passconfirm'];
	
	//----------------------------------
	// confirm that they put the commas, don't have spaces or symbols, etc.
	$badthings = array(" ","~","!","#","$","%","^","&","*","(",")","+","|","`","=","\\",",","/","<",">","?","\"", ";", "'",":","[","]","{","}");

	// clear up spaces
	for($i=0; $i<count($users); $i++) {
		$users[$i] = trim($users[$i]);
	}

	// check for unwanted symbols
	foreach($users as $user) {
		$chars = preg_split("//",$user);
		foreach($chars as $char) {
			if (in_array($char,$badthings)) {
				if ($char == " ") {
					print "Sorry, you can not use a <strong>space</strong> in a user name, please click back and try again.<br>";
					print "<span style='color:red'>Users not created!</span>";
				} else {
					print "Sorry, you can not use the character <strong>$char</strong> in a user name, please click back and try again.<br>";
					print "<span style='color:red'>Users not created!</span>";
				}
				exit;
			}
		}
	}

	// check for backspace returns in name
	foreach($users as $user) {
		$chars = preg_split("//",$user);
		foreach($chars as $char) {
			if ($char == "\n" || $char == "\r") {
					print "Sorry, you can not use a <strong>backspace</strong> in a user name, please click back and try again.<br>";
					print "Did you forget to use commas to separate names?<br><br>";
					print "<span style='color:red'>Users not created!</span>";
					exit;
			}
		}
	}
	

	for($i=0; $i<count($users); $i++) {
		$users[$i] = trim($users[$i]);
	}
	if ($pass == "") {
		print "you didn't fill out the password, try again<br>";
		exit;
	}
	if ($pass != $passconfirm) {
		print "your password and password confirmation don't match<br>\n";
		print_group_registration_form();
		exit;
	}
	
	$no_repeats = false;
	foreach($users as $user) {
		if ($m->user_exists($user)) {
			print "<span style='color:red'>user name <strong>$user</strong> already exits</span><br>\n";
			$no_repeats = true;
		}
	}
	if ($no_repeats) {
		print "<span style='color:red'><strong>Users not added!  Try again!</strong></span><br>\n";
		print_group_registration_form();
		exit;
	}

	foreach($users as $user) {
		$m->insert_new_user($user,$pass,$type);
		print "The new user, <strong>$user</strong>, has been registered...<br><br>";
	}
}

if ($action == "addgroup") {
	print_group_registration_form();

}

if ($action == "delete") {
	$user = $_GET["user"];
	if ($user == "") {
		$user = $_POST["user"];
	}
	if ($user == "") {
		$m->message("Error: No user selected...");
	} else {
		print "<span style='color:red;'>Click to confirm that you want to delete user: $user</span><br><br>\n";
		?>
		<form action="<? print $m->app_link($APP); ?>" method="POST">
		<input type="hidden" name="action" value="deleteuser">
		<input type="hidden" name="username_todelete" value="<? print $user; ?>">
		<input type="submit" value="Delete">
		</form>
		<?
	}
	
}

if ($action == "deleteuser") {
	$user = $_POST['username_todelete'];
	if ($m->get_user_type($user) != "superuser") {
		$m->delete_user($user);
		$m->message("$user deleted from database</span>");
	} else {
		$m->message("Error: You can not delete a superuser.</span>");
	}
	$action = "home";
}

if ($action == "type") {
	$user_types = $m->get_user_types();
	?>
	<a href="<? print $m->app_link($APP); ?>&action=addtype" style="color:black">Add Type</a>
	<br><br>
	<table width="100%" cellspacing="0">
	<tr>
	<td class="header">Type Name</td>
	<td class="header">Actions</td>
	</tr>
	<?php
	for($i=0; $i<count($user_types); $i++) {
		print "<tr>\n";
		if ($color == "background-color:#eeeeee;") {
			$color = "background-color:white;";
		} else {
			$color = "background-color:#eeeeee;";
		}
		print "<td class='cell' style='$color'>".$user_types[$i]."</td>\n";
		print "<td class='cell' style='$color'>";
		print "<a href=\"".$m->app_link($APP)."&action=edittype&typename=".$user_types[$i]."\" >edit</a>\n";
		print "</td>\n";
		print "</tr>\n";
	}
	?>
	</table>
	<?
}

if ($action == "addtype") {
	?>
	<form id="inserttype" action="<? print $m->app_link($APP); ?>" method="POST">
	<input type="hidden" name="action" value="inserttype">
	New Type Name:<br><input type="text" size="30" name="typename">
	<input type="submit" value="Save">
	</form>
	<?
}

if ($action == "inserttype") {
	if (!$m->type_exists($_POST["typename"])) {
		$m->insert_user_type($_POST["typename"]);
		print "type inserted";
	} else {
		print "type already exists, try again";
	}
}

if ($action == "edittype") {
	$type = $_REQUEST["typename"];
	print "<strong>$type Settings</strong><br>";
	?>
	<form action="<? print $m->app_link($APP); ?>" method="POST">
	<input type="hidden" name="action" value="inserttypeapps">
	<input type="hidden" name="typename" value="<?php print $type; ?>">
	<br>
	Allowed Applications:<br>
	<?php
	$type_apps = $m->get_type_apps($type);
	$applications = $m->get_application_names();
	foreach($applications as $application) {
		$checked = "";
		if (in_array($application,$type_apps)) {
			$checked="checked";
		}
		print "<input type=\"checkbox\" name=\"application[]\" value=\"$application\" $checked> $application<br>\n";
		$count++;
	}
	?>
	<br>
	<br>
	<input type="submit" value="Save">
	</form>
	<?
}

if ($action == "inserttypeapps") {
	$type = $_REQUEST["typename"];
	print "<strong>User Type: </strong> ".$type."<br>\n";
	print "<strong>Allowed Products: </strong> <br>\n";

	$m->dbiupdate("DELETE FROM user_types WHERE row_type='application' AND type_name=?;","s",array($type));
	$allowed = $_POST["application"];
	foreach($allowed as $app) {
		$m->insert_type_app($type,$app);
		print $app."<br>";
	}
	print "<br><br>User Type Applications Updated.<br>\n";
}

if ($action == "edituser") {
	$user = $_GET["user"];
	if ($user == "") {
		$user = $_POST["user"];
	}
	if ($user == "") {
		$m->message("Error: user name not found");
	} else {
		?>
		<br><br>
		<center>
		<div style="width:300px;text-align:left;">
		<b>Change <? print $user; ?>'s User Type:</b><br>
		<form action="<? print $m->app_link($APP); ?>" method="POST">
		<input type="hidden" name="action" value="change_user_type_3">
		<input type="hidden" name="name" value="<?php print $user; ?>">
		<br>
		<select name="newtype">
		<?php
			$types = $m->get_all_types();
			foreach($types as $type) {
				if ($type != "superuser") {
					$selected = "";
					if ($m->get_user_type($_POST["name"]) == $type) {
						$selected = "selected";
					}
					print "<option value=\"$type\" $selected>$type</option>\n";
				}
			}
		?>
		</select>
		<input type="submit" value="Save">
		</form>
		
		<br>
		<hr>
		<br>

		<b>Change <? print $user; ?>'s Password:</b><br>
		<form action="<? print $m->app_link($APP); ?>" method="POST">
		<input type="hidden" name="action" value="change_user_password">
		<input type="hidden" name="name" value="<?php print $user; ?>">
		<br>
		New Password:<br>
		<input type="password" size="25" name="password1"><br>
		New Password Confirm:<br>
		<input type="password" size="25" name="password2"><br>
		<input type="submit" value="Save">
		</form>
		</div>
		</center>
		<?
	}
}

if ($action == "change_user_type_3") {
	if (isset($_POST["name"]) && isset($_POST["newtype"])) {
		$name = $_POST["name"];
		$newtype = $_POST["newtype"];
		if ($m->get_user_type($name) != "superuser") {
			$m->change_user_type($name,$newtype);
		} else {
			$m->message("Error: you can not change the type of a 'superuser'");
		}
	} else {
		print "error, name or type not selected";
	}
	$action = "home";
}


if ($action == "change_user_password") {
	$user = $_POST["name"];
	$password1 = $_POST["password1"];
	$password2 = $_POST["password2"];

	if ($user == "") {
		print "There was a problem selecting the user name, try again.";
		exit;
	}
	if ($password1 == "" || $password2 == "") {
		print "It seems you didn't fill out both password forms, try again.";
		exit;
	}
	if ($password1 != $password2) {
		print "It seems the password and password-confirmation don't match, try again.";
		exit;
	}
	$m->reset_password($user,$password1);
	print "Password reset!";
}

if ($action == "home") {
	$startpoint = $_GET["startpoint"];
	if ($startpoint == "") {
		$startpoint = 0;
	}
	$users = $m->dbiarray("SELECT * FROM users ORDER BY id LIMIT ?,?;","ii",array($startpoint,$ROWCOUNT));
	$total_row = $m->dbirow("SELECT count(*) FROM users;");
	$total = $total_row["count(*)"];
	if ($startpoint + $ROWCOUNT < $total) {
		$next = $startpoint + $ROWCOUNT;
		?>
		<a href="<? print $m->app_link($APP); ?>&action=home&startpoint=<? print $next; ?>" style="color:green;float:right;">Next</a>
		<?
		
	}

	if ($startpoint > 0) {
		$prev = $startpoint - $ROWCOUNT;
		?>
		<a href="<? print $m->app_link($APP); ?>&action=home&startpoint=<? print $prev; ?>" style="color:green;">Previous</a>
		<?
	}
	print_users_table($users);
}

?>
</div>
<?
$m->print_footer();
?>
