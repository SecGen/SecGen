<form class="" action="admin.php?tab=4" name="social" method="post">
<h2>Social Login Settings</h2>
<strong>Please note:</strong> Social logins require that you do some configuration on your own with Google and/or Facebook.<br>It is strongly recommended that you <a href="http://www.userspice.com/documentation-social-logins/" target="_blank">check the documentation at UserSpice.com.</a><br><br>
<div class="row">
  <div class="col-xs-12 col-sm-6">
    <!-- left -->
    <div class="form-group">
      <label for="glogin">Enable Google Login</label>
      <select id="glogin" class="form-control" name="glogin">
        <option value="1" <?php if($settings->glogin==1) echo 'selected="selected"'; ?> >Enabled</option>
        <option value="0" <?php if($settings->glogin==0) echo 'selected="selected"'; ?> >Disabled</option>
      </select>
    </div>

    <div class="form-group">
      <label for="fblogin">Enable Facebook Login</label>
      <select id="fblogin" class="form-control" name="fblogin">
        <option value="1" <?php if($settings->fblogin==1) echo 'selected="selected"'; ?> >Enabled</option>
        <option value="0" <?php if($settings->fblogin==0) echo 'selected="selected"'; ?> >Disabled</option>
      </select>
    </div>

    <div class="form-group">
      <label for="gid">Google Client ID</label>
      <input type="password" autocomplete="off" class="form-control" name="gid" id="gid" value="<?=$settings->gid?>">
    </div>

    <div class="form-group">
      <label for="gsecret">Google Client Secret</label>
      <input type="password" autocomplete="off" class="form-control" name="gsecret" id="gsecret" value="<?=$settings->gsecret?>">
    </div>

    <div class="form-group">
      <label for="ghome">Full Home URL of Website - include the final /</label>
      <input type="text" class="form-control" name="ghome" id="ghome" value="<?=$settings->ghome?>">
    </div>

    <div class="form-group">
      <label for="gredirect">Google Redirect URL (Path to oauth_success.php)</label>
      <input type="text" class="form-control" name="gredirect" id="gredirect" value="<?=$settings->gredirect?>">
    </div>
  </div>


  <div class="col-xs-12 col-sm-6">
    <!-- right -->
    <div class="form-group">
      <label for="fbid">Facebook App ID</label>
      <input type="password" class="form-control" name="fbid" id="fbid" value="<?=$settings->fbid?>">
    </div>

    <div class="form-group">
      <label for="fbsecret">Facebook Secret</label>
      <input type="password" class="form-control" name="fbsecret" id="fbsecret" value="<?=$settings->fbsecret?>">
    </div>

    <div class="form-group">
      <label for="fbcallback">Facebook Callback URL</label>
      <input type="text" class="form-control" name="fbcallback" id="fbcallback" value="<?=$settings->fbcallback?>">
    </div>

    <div class="form-group">
      <label for="graph_ver">Facebook Graph Version - Formatted as v2.2</label>
      <input type="text" class="form-control" name="graph_ver" id="graph_ver" value="<?=$settings->graph_ver?>">
    </div>

    <div class="form-group">
      <label for="finalredir">Redirect After Facebook Login</label>
      <input type="text" class="form-control" name="finalredir" id="finalredir" value="<?=$settings->finalredir?>">
    </div>
  </div>

</div>
<input type="hidden" name="csrf" value="<?=$token?>" />
<p><input class='btn btn-large btn-primary' type='submit' name="social" value='Save Social Login Settings'/></p>
</form>
