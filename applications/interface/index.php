<? $s->print_header(); ?>
<div id="body-container">
	<?
	if ($LOGGED_IN) {
		print "You are logged in as $_SESSION[username] <a href='$ROOT"."index.php?app=login&action=logout'>LOGOUT</a>";
		$apps = $s->get_user_apps($USER["name"]);
		print "<ul>\n";
		foreach ($apps as $app) {
			print "<li><a href='".$s->app_link($app)."'>$app</a></li>\n";
		}
		print "</ul>\n";
	} else {
		print "You are not logged in <a href='$ROOT"."index.php?app=login'>LOGIN</a>";
	}
	?>
	<div style="clear:both;"></div>
</div> <!-- End Home Container -->
<? $s->print_footer(); ?>
