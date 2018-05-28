<?

$APP = "Site Manager";
if (!$m->check_auth()) {
	header("Location: $ROOT");
}

if (!$m->table_exists("site_manager")) {
	$col_array = array();
	$col_array["header_title"] = "TINYTEXT";
	$col_array["analytics"] = "MEDIUMTEXT";
	$col_array["adverts_on"] = "VARCHAR(5)"; // yes or no
	$col_array["analytics_on"] = "VARCHAR(5)";
	$col_array["meta_description"] = "VARCHAR(255)";
	$col_array["meta_keywords"] = "MEDIUMTEXT";
	$col_array["meta_author"] = "VARCHAR(255)";
	$m->create_table("site_manager",$col_array);
	$m->message("Database Table Created");
}

$css = <<<EOF

<style text='text/css'>

input {
	width:100%;
}

textarea {
	width:100%;
	height:100px;
}

input[type='submit'] {
	width:auto;
}

</style>

EOF;

$m->print_header($css);

$action = $_REQUEST["action"];

if ($action == "save") {
	$header_title = addslashes($_REQUEST["header_title"]);
	$analytics = addslashes($_REQUEST["analytics"]);
	$adverts_on = addslashes($_REQUEST["adverts_on"]);
	$analytics_on = addslashes($_REQUEST["analytics_on"]);
	$meta_description = addslashes($_REQUEST["meta_description"]);
	$meta_keywords = addslashes($_REQUEST["meta_keywords"]);
	$meta_author = addslashes($_REQUEST["meta_author"]);
	$rows = $m->dbiarray("SELECT * FROM site_manager;");
	if (count($rows) < 1) {
		$m->dbiupdate("INSERT INTO site_manager (header_title, analytics, adverts_on, analytics_on, meta_description, meta_keywords, meta_author) VALUES (?,?,?,?,?,?,?);","sssssss",array($header_title,$analytics,$adverts_on,$analytics_on,$meta_description,$meta_keywords,$meta_author));
	} else {
		$m->dbiupdate("UPDATE site_manager SET header_title='$header_title', analytics='$analytics', adverts_on='$adverts_on', analytics_on='$analytics_on', meta_description='$meta_description', meta_keywords='$meta_keywords', meta_author='$meta_author';","sssssss",array($header_title,$analytics,$adverts_on,$analytics_on,$meta_description,$meta_keywords,$meta_author));
	}
	$m->message("Data Saved.");
}

$vars = $m->dbirow("SELECT * FROM site_manager;");

?>

<div style="background:#eee;border:1px solid #555;margin-top:2em;padding:1em;width:900px;margin-left:auto;margin-right:auto;">

<h1>Site Manager</h1>
<h2>Stats</h2>
Page Loads: <? print $vars["page_loads"]; ?>

<h2>Settings</h2>
<form method="post" action="<? print $m->app_link(); ?>" />
<input type="hidden" name="action" value="save" />
Header Title:<br />
<input type="text" name="header_title" value="<? print htmlentities($vars["header_title"]); ?>" /><br />
<br />
Google Analytics Code:<br />
<textarea name="analytics"><? print htmlentities(stripslashes($vars["analytics"])); ?></textarea><br />
Analytics Status: 
<?
$c = "";
if ($vars["analytics_on"] == "yes") {
	$c = "checked";
}
?>
<input type="radio" name="analytics_on"  value="yes" <? print $c; ?>  style="width:20px;" /> On
<?
$c = "";
if ($vars["analytics_on"] != "yes") {
	$c = "checked";
}
?>
<input type="radio" name="analytics_on" value="no" <? print $c; ?> style="width:20px;"  /> Off
<br /><br />
<br />
Meta Description:<br />
<input type="text" name="meta_description" value="<? print htmlentities($vars["meta_description"]); ?>" /><br />
Meta Keywords:<br />
<input type="text" name="meta_keywords" value="<? print htmlentities($vars["meta_keywords"]); ?>" /><br />
Meta Author:<br />
<input type="text" name="meta_author" value="<? print htmlentities($vars["meta_author"]); ?>" /><br />
<br />
<input type="submit" value="Save" /><br />
</form>

<br /><br />
</div>

<?

$m->print_footer();

?>
