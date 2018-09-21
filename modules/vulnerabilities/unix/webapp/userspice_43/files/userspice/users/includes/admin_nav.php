<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

/*
Load main navigation menus
*/
$admin_nav_all = $db->query("SELECT * FROM menus WHERE menu_title='admin' ORDER BY display_order");

/*
Set "results" to true to return associative array instead of object...part of db class
*/
$admin_nav=$admin_nav_all->results(true);

/*
Make menu tree
*/
$prep=prepareMenuTree($admin_nav);

?>

<nav class="navbar navbar-default">
<div class="container-fluid">
  <div class="navbar-header">
	<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar_admin" aria-expanded="false" aria-controls="navbar">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</button>
  </div>
  <div id="navbar_admin" class="navbar-collapse collapse">
	<ul class="nav navbar-nav navbar-left">
<?php
foreach ($prep as $key => $value){
	/*
	Check if there are children of the current nav item...if no children, display single menu item, if children display dropdown menu
	*/
	if(sizeof($value['children'])==0){
		if ($user->isLoggedIn()){
			if (checkMenu($value['id'],$user->data()->id) && $value['logged_in']==1){
				echo prepareItemString($value);
			}
		}else{
			if ($value['logged_in']==0 || checkMenu($value['id'])){
				echo prepareItemString($value);
			}
		}
	}else{
		if ($user->isLoggedIn()){
			if (checkMenu($value['id'],$user->data()->id) && $value['logged_in']==1){
				$dropdownString=prepareDropdownString($value);
				$dropdownString=str_replace('{{username}}',$user->data()->username,$dropdownString);
				echo $dropdownString;
			}
		}else{
			if ($value['logged_in']==0 || checkMenu($value['id'])){
				$dropdownString=prepareDropdownString($value);
				$dropdownString=str_replace('{{username}}',$user->data()->username,$dropdownString);
				echo $dropdownString;
			}
		}
	}
}
?>
	</ul>
  </div><!--/.nav-collapse -->
</div><!--/.container-fluid -->
</nav>
