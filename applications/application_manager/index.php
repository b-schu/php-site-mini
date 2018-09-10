<?php

$APP = "Application Manager";

if (!$m->check_auth($_SESSION["username"],$APP)) {
	header("Location: $ROOT");
}

$m->print_header();

function print_menu() {
	global $APP;
	global $m;
	?>
	<h2>Application Manager</h2>
	| <a href="<? print $m->app_link($APP); ?>&action=view" style="color:black">View Applications</a> |
	| <a href="<? print $m->app_link($APP); ?>&action=add" style="color:black">Add Applications</a> |
	| <a href="<? print $m->app_link($APP); ?>&action=edit" style="color:black">Edit Applications</a> |
	| <a href="<? print $m->app_link($APP); ?>&action=delete" style="color:black">Delete Applications</a> |
	<hr>
	<?php
}

$action = $_REQUEST["action"];
if($action == ""){
	$action = "view";
}

?>
<div id="body-container">
<?

if($action == "add"){
	print_menu();
	$path = $_REQUEST["path"];
	if($path != ""){
		$path = str_replace("|","/",$path);
	}
	?>
	<p>
	To add an application you must first upload the applications to the "applications" directory.
	</p>
	<p>
	<a href="<? print $m->app_link($APP); ?>&action=findapps" style="color:blue">Click Here</a> to find products not installed.
	</p>
	<form action="<? print $m->app_link($APP); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="saveapplication">
	<p>
	<b>Application Name:</b><br><input type="text" size="50" name="name">
	</p>
	<p>
	<b>Path:</b><br>
	<? print $ROOT."applications/"; ?><input type="text" size="20" name="path" value="<? print $path; ?>">
	</p>
	<p>
	<b>Allow to User Types:</b><br>
	<?
	$types = $m->get_user_types();
	foreach($types as $type){
		print "<input type='checkbox' name='allowto[]' value='".$type."' checked> ".$type."<br>\n";
	}
	?>
	</p>
	<p>
	<b>Description:</b><br>
	<textarea name="description" style="width:100%;box-sizing:border-box;height:75px;"></textarea><br>
	<p>
	<input type="submit" value="Save">
	</form>
	<?
}

if($action == "findapps"){
	print_menu();

	$path = $ROOTPATH."applications/";
	$apps = $m->get_applications();
	$app_paths = array();
	foreach($apps as $app){
		$app_paths[] = $app["app_path"];
	}
	$dir = opendir($path);
	while($file = readdir($dir)){
		if(is_dir($ROOTPATH."applications/".$file) && $file != "." && $file != ".."){
			$path = $file;
			if(in_array($path,$app_paths)){
					print $file." - <span style='color:blue'>already installed</span><br>";
			}else{
					print $file." - <a href=\"".$m->app_link($APP)."&action=add&path=".str_replace("/","|",$path)."\" style='color:red'>install?</a><br>";
			}
		}
	}
}

if($action == "saveapplication"){
	print_menu();
	$app_name = $_POST["name"];
	$usertypes = $_POST["allowto"];
	$path = $_POST["path"];
	$description = $_POST["description"];
	if($m->insert_application($app_name,$path,$description)){
		foreach($usertypes as $type){
			$m->insert_type_app($type,$app_name);
		}
		$m->message($_POST["name"]." successfully installed.");
	}else{
		$m->message("An application with that name already exists.  Can not install.");
	}
}

if($action == "delete"){
	print_menu();
	?>
	Delete which product?
	<br><br>
	<?
	$apps = $m->get_apps();
	print "<ul>\n";
	for($i=0; $i<count($apps); $i++){
		print "<li><a href=\"".$m->app_link($APP)."&action=subdelete&name=".$apps[$i]["name"]."\">".$apps[$i]["name"]."</a></li>";
	}
	print "</ul>\n";
}

if($action == "subdelete"){
	print_menu();
	$name = $_REQUEST["name"];
	$m->delete_application($name);
	print "$name deleted from Applications.<br><br>\n";
}

if($action == "edit"){
	print_menu();
	$apps = $m->get_apps();
	print "<ul>\n";
	for ($i=0; $i<count($apps); $i++) {
		print "<li><a href=\"".$m->app_link($APP)."&action=editapp&appname=".$apps[$i]["name"]."\">".$apps[$i]["name"]."</a></li>";
	}
	print "</ul>\n";
}

if($action == "editapp"){
	print_menu();
	$appname = $_REQUEST["appname"];
	$app = $m->get_app($appname);

	print "<form action='".$m->app_link($APP)."' method='POST'>\n";
	print "<input type='hidden' name='action' value='update'>\n";
	print "<b>Name:</b> ".$app["name"]."<br>\n";
	print "<input type='hidden' size=25 name='name' value='".$app["name"]."'><br>\n";
	print "<b>Path:</b><br><br>\n";
	print "$ROOT"."applications/<input type=text size=40 name='path' value='".$app["app_path"]."'><br><br>\n";
	print "<b>Description:</b><br>\n<textarea name='description' rows='10' cols='50'>".$app["description"]."</textarea><br>";
	print "<input type='submit' value='Save'>\n";
	print "</form>\n";
}

if($action == "update"){
	print_menu();
	$m->update_application(trim($_POST["name"]),trim($_POST["path"]),addslashes($_POST["description"]));
	print "updated...";

}

if($action == "view"){
	print_menu();
	$apps = $m->get_apps();
	for($i=0; $i<count($apps); $i++){
		$name = $apps[$i]["name"];
		$description = $apps[$i]["description"];
		print "\n\n<table border=0 width=\"800\" style=\"border-color:black;border-width:1px;border-style:solid\">\n";
		print "<tr>\n";
		print "<td width=\"50%\" align=\"center\">\n";
		print "<strong>$name</strong><br>\n";
		print "</td>\n";
		print "<td width=\"50%\" valign=\"center\">\n";
		print "$description\n";
		print "</td>\n";
		print "</tr>\n";
		print "</table>\n";
		print "<br>\n";
	}
}

?>
</div>
<?
$m->print_footer();
?>
