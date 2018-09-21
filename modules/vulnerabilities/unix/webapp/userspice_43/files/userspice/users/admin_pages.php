<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
?>
<?php require_once 'init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>
<?php if (!securePage($_SERVER['PHP_SELF'])){die();} ?>
<?php

$errors = [];
$successes = [];

//Get line from z_us_root.php that starts with $path
$file = fopen($abs_us_root.$us_url_root."z_us_root.php","r");
while(!feof($file)){
	$currentLine=str_replace(" ", "", fgets($file));
	if (substr($currentLine,0,5)=='$path'){
		//echo $currentLine;
		//if here, then it found the line starting with $path so break to preserve $currentLine value
		break;
	}
}
fclose($file);

//sample text: $path=('/','/users/','/usersc/');
//Get array of paths, with quotes removed
$lineLength=strlen($currentLine);
$pathString=str_replace("'","",substr($currentLine,7,$lineLength-11));
$paths=explode(',',$pathString);

$pages=[];

//Get list of php files for each $path
foreach ($paths as $path){
	$rows=getPathPhpFiles($abs_us_root,$us_url_root,$path);
	foreach ((array)$rows as $row){
		$pages[]=$row;
	}
}

$dbpages = fetchAllPages(); //Retrieve list of pages in pages table

$count = 0;
$dbcount = count($dbpages);
$creations = array();
$deletions = array();

foreach ($pages as $page) {
	$page_exists = false;
	foreach ($dbpages as $k => $dbpage) {
		if ($dbpage->page === $page) {
			unset($dbpages[$k]);
			$page_exists = true;
			break;
		}
	}
	if (!$page_exists) {
		$creations[] = $page;
	}
}

// /*
//  * Remaining DB pages (not found) are to be deleted.
//  * This function turns the remaining objects in the $dbpages
//  * array into the $deletions array using the 'id' key.
//  */
$deletions = array_column(array_map(function ($o) {return (array)$o;}, $dbpages), 'id');

$deletes = '';
for($i = 0; $i < count($deletions);$i++) {
	$deletes .= $deletions[$i] . ',';
}
$deletes = rtrim($deletes,',');
//Enter new pages in DB if found
if (count($creations) > 0) {
	createPages($creations);
}
// //Delete pages from DB if not found
if (count($deletions) > 0) {
	deletePages($deletes);
}

//Update $dbpages
$dbpages = fetchAllPages();

?>
<div id="page-wrapper">

	<div class="container">

		<!-- Page Heading -->
		<div class="row">
			<div class="col-xs-12">

				<h1>Manage Page Access</h1>

				<!-- Content goes here -->

				<hr>
				<table id="paginate" class='table table-hover table-list-search'>
					<thead>
						<th>Id</th><th>Page</th><th>Page Name</th><th>ReAuth</th><th>Access</th>
					</thead>

					<tbody>


						<?php
						//Display list of pages
						$count=0;
						foreach ($dbpages as $page){
							?>
							<tr><td><?=$dbpages[$count]->id?></td>
								<td><a class="nounderline" href ='admin_page.php?id=<?=$dbpages[$count]->id?>'><?=$dbpages[$count]->page?></a></td>
								<td><a class="nounderline" href ='admin_page.php?id=<?=$dbpages[$count]->id?>'><?=$dbpages[$count]->title?></a></td>
								<td>
									<?php if($dbpages[$count]->re_auth == 1){
										echo "<i class='glyphicon glyphicon-ok'></i>";
									} ?>
								</td>
								<td>
									<a class="nounderline" href ='admin_page.php?id=<?=$dbpages[$count]->id?>'>
										<?php
										//Show public/private setting of page
										if($dbpages[$count]->private == 0){
											echo "<font color='green'>Public</font>";
										}else {
											echo "<font color='red'>Private</font>";
										}
										?>
									</a>
								</td></tr>
								<?php
								$count++;
							}?>
						</tbody>
					</table>



				</div>
				<!-- /.row -->
			</div>
		</div>
	</div>


	<!-- Content Ends Here -->
	<!-- footers -->
	<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

	<!-- Place any per-page javascript here -->

	<script>
	$(document).ready(function() {
	    $('#paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], "aaSorting": []});
	} );
	</script>
	<script src="js/pagination/jquery.dataTables.js" type="text/javascript"></script>
	<script src="js/pagination/dataTables.js" type="text/javascript"></script>

	<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
