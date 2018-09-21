<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/
?>
<?php require_once 'init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();}

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}

/*
Query available menus
*/
$navs_all = $db->query("SELECT DISTINCT menu_title FROM menus");
$navs_all = $navs_all->results();
?>
<div id="page-wrapper">
<div class="container">

<div class="row">
	<div class="col-xs-12">
	<h2>Menus</h2>
<?php if($settings->navigation_type !=1){bold("<br>Please note that you have database-driven menus disabled in your dashboard.");} ?>


<div class="table-responsive">
	<table class="table table-bordered table-hover">
		<thead><tr><th>Menu Title</th><th>Item Count</th></tr></thead>
		<tbody>
		<?php
		foreach ($navs_all as $nav){
		?>
			<tr>
				<td><a href="admin_menu.php?menu_title=<?=$nav->menu_title?>"><?=$nav->menu_title?></a></td>
				<td>
					<?php echo $db->query("SELECT * FROM menus WHERE menu_title = ?",array($nav->menu_title))->count();?>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
</div>

</div>
</div>

</div>
</div>

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
