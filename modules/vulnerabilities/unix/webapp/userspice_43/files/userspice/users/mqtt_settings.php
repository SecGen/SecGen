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
<!-- NOTE: This file also serves as an example tutorial on how to do One Click Edit (OCE) -->
<!-- NOTE: All credit goes to Curtis Parham for OCE.  -->
<!-- NOTE: Checkout the youtube playlist for a detailed explanation. -->
<!-- NOTE: https://www.youtube.com/playlist?list=PLFPkAJFH7I0krh2_P80RhPVHc3zPDuAhO -->
<!-- NOTE: To use One Click Edit (OCE), you must call the js file as shown below.-->
<!-- NOTE: See the "existing servers" form for more notes -->

<script type="text/javascript" src="<?=$us_url_root?>users/js/oce.js"></script>

<?php
$servers = $db->query("SELECT * FROM MQTT")->results();
if(!empty($_POST)){

  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }
  $fields = array(
    'server'      => Input::get('server'),
    'port'        => Input::get('port'),
    'username'    => Input::get('username'),
    'password'    => Input::get('password'),
    'nickname'    => Input::get('nickname'),
  );

  $db->insert("mqtt",$fields);
  Redirect::to("mqtt_settings.php?err=New+server+added");

}

?>
<div id="page-wrapper">

  <div class="container">

    <!-- Page Heading -->
    <div class="row">
      <div class="col-sm-12">

        <!-- Content Goes Here. Class width can be adjusted -->

        <h1>Setup your MQTT servers</h1>
        <p>
          MQTT serves two purposes in UserSpice.  It is the "wiring" of the Internet of Things world and now UserSpice can be part of that world. Additionally, this page serves to provde demo code on how to use our OCE (One Click Edit) system. Feel free to look at the PHP on this page to see how to use OCE.
        </p>
        <p>To use MQTT in your code, the syntax is mqtt(id_number_of_server,topic,message);<br>
        For example: mqtt(2,"Hello","World!"); //sends Msg of "World!" with topic of "Hello" to MQTT server 2.</p>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-4">
        <form name='update' action='mqtt_settings.php' method='post'>
          <h3>Create a new MQTT Server Connection</h3>
          <label>Server IP or Hostname:*</label>
          <input required size='50' class='form-control' type='text' name='server' value='' />

          <label>Server Port:*</label>
          <input required size='50' class='form-control' type='number' min="0" max="99999" step="1" name='port' value='' />

          <label>Username:</label>
          <input size='50' class='form-control' type='text' name='username' value='' />

          <label>Password:</label>
          <input size='50' class='form-control' type='password' name='password' value='' />

          <label>Server Nickname:</label>
          <input size='50' class='form-control' type='text' name='nickname' value='' />
          <input type="hidden" name="csrf" value="<?=Token::generate();?>" /><br>
          <input class='btn btn-primary' name="update_only" type='submit' value='Add MQTT Server' class='submit' /><br>

        </form>

      </div>

      <div class="col-sm-8">
        <h3>Existing Servers</h3>
        <!-- This msg div is here for posting One Click Edit response messages -->
        <div id="msg" class="bg-info text-info"></div>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Server</th>
              <th>Port</th>
              <th>Username</th>
              <th>Password</th>
              <th>Nickname</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach($servers as $s){ ?>
              <tr>
                <td><?=$s->id?></td>
<!-- NOTE: To use OCE, you need to have a class to grab onto.  I like using a paragraph tag and the class OCE.   -->
<!-- NOTE: data-id is the id of the row you will be editing. -->
<!-- NOTE: You also need a data-field which is your column name in the database. -->
<!-- NOTE: Finally, you need a data-input which is your data type (such as input or dropdown) -->
<!-- NOTE: Once you do this, go to the bottom of the page to see the script call -->
                <td><p class="oce" data-id="<?=$s->id?>" data-field="server" data-input="input"><?=$s->server?></p></td>

                <td><p class="oce" data-id="<?=$s->id?>" data-field="port" data-input="input"><?=$s->port?></p></td>

                <td><p class="oce" data-id="<?=$s->id?>" data-field="username" data-input="input"><?=$s->username?></p></td>

                <td>
                  <p class="oce" data-id="<?=$s->id?>" data-field="password" data-input="input"><input type="text" name="" value="<?=$s->password?>">
                    </p>
                </td>
                <td><p class="oce" data-id="<?=$s->id?>" data-field="nickname" data-input="input"><?=$s->nickname?></p></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div><!-- /.row --><br />
  </div>
</div>
<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>


<script>
// This script below parses response messages from Once Click Edit and sends them to the div above the servers table. It MUST be loaded before the OCE parser file script(s).
	function oceSuccess(data) {
		var r = JSON.parse(data);
		jQuery('#msg').html(r.msg);
    jQuery('#msg').html(r.msg);
    location.reload();
	}
</script>

<?php
//if you want to require someone to have a certain permission for the OCE to kick in, you can comment out the if statement.  hasPerm lets you pass in an array of permission levels.

//if(hasPerm([2,3],$user->data()->id)): ?>


<script>
// This is the actual OCE script. Note that I am calling that OCE oce class I passed in the paragraph tags above.

//NOTE: After this, you create a parser file like the one referenced below to do the actual processing.

	var oceOpts = {
		url:'<?=$us_url_root?>users/parsers/editMQTT.php',
    allowNull : true}
	jQuery('.oce').oneClickEdit(oceOpts, oceSuccess);
</script>

<?php
//endif;
?>




<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
