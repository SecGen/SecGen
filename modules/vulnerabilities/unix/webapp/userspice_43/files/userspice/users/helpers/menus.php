<?php
function _assert( $expr, $msg){ if( !$expr ) print "<br/><b>ASSERTION FAIL: </b>{$msg}<br>";  }

function prepareMenuTree($menuResults){
	/*
	Get instance of tree manager and build the tree
	*/
	$treeManager = treeManager::get();
	$menuTree = $treeManager->getTree($menuResults, 'id','parent','display_order');
	/*
	Indent the tree
	*/
	//$menuTree = $treeManager->slapTree($recordsTree, 1 ); //1 for indent count

	return $menuTree;
}

function prepareIndentedMenuTree($menuResults){
	/*
	Get instance of tree manager and build the tree
	*/
	$treeManager = treeManager::get();
	$menuTree = $treeManager->getTree($menuResults, 'id','parent','display_order');
	/*
	Indent the tree
	*/
	$menuIndentedTree = $treeManager->slapTree($menuTree, 1,'menu_title' ); //1 for indent count

	return $menuIndentedTree;
}

function prepareDropdownString($menuItem,$user_id){
	$itemString='';
	$itemString.='<li class="dropdown">';
	$itemString.='<a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="'.$menuItem['icon_class'].'"></span> '.$menuItem['label'].' <span class="caret"></span></a>';
	$itemString.='<ul class="dropdown-menu">';
	foreach ($menuItem['children'] as $childItem){
		$authorizedGroups = array();
    foreach (fetchGroupsByMenu($childItem['id']) as $g) {
    	$authorizedGroups[] = $g->group_id;
    }
		if($childItem['logged_in']==0 || (hasPerm($authorizedGroups,$user_id) || in_array(0,$authorizedGroups))) {
		$itemString.=prepareItemString($childItem,$user_id); }
	}
	$itemString.='</ul></li>';
	return $itemString;
}

function prepareItemString($menuItem,$user_id){
	$itemString='';
	if($menuItem['label']=='{{hr}}') { $itemString = "<li class='divider'></li>"; }
	elseif($menuItem['link']=='users/verify_resend.php' || $menuItem['link']=='users/verify_resend.php') {
		$db = DB::getInstance();
		$query = $db->query("SELECT * FROM email");
		$results = $query->first();
		$email_act=$results->email_act;
		if($email_act==1) {
			$itemString.='<li><a href="'.US_URL_ROOT.$menuItem['link'].'"><span class="'.$menuItem['icon_class'].'"></span> '.$menuItem['label'].'</a></li>'; }
	}
	elseif($menuItem['link']=='users/join.php' || $menuItem['link']=='users/join.php') {
		$db = DB::getInstance();
		$query = $db->query("SELECT * FROM settings");
		$results = $query->first();
		$registration=$results->registration;
		if($registration==1) {
			$itemString.='<li><a href="'.US_URL_ROOT.$menuItem['link'].'"><span class="'.$menuItem['icon_class'].'"></span> '.$menuItem['label'].'</a></li>'; }
	}
	else {
	$itemString.='<li><a href="'.US_URL_ROOT.$menuItem['link'].'"><span class="'.$menuItem['icon_class'].'"></span> '.$menuItem['label'].'</a></li>'; }
	return $itemString;

}
?>
