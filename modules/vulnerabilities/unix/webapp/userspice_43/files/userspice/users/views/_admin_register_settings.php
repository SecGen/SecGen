<form class="" action="admin.php?tab=3" name="register" method="post">
<h2>Registration Settings</h2>
<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group">
    				<label for="registration">Registration <a class="nounderline" data-toggle="tooltip" title="Registration Switcher! Also controls OAuth (Social Logins). Default: Enabled.">?</a></label>
    				<select id="registration" class="form-control" name="registration">
    								<option value="1" <?php if($settings->registration==1) echo 'selected="selected"'; ?> >Enabled</option>
    								<option value="0" <?php if($settings->registration==0) echo 'selected="selected"'; ?> >Disabled</option>
    				</select>
    </div>

    <!-- Allow users to change Usernames -->
    <div class="form-group">
    <label for="change_un">Allow users to change their Usernames <a class="nounderline" data-toggle="tooltip" title="Does as it says. Default: Disabled.">?</a></label>
    <select id="change_un" class="form-control" name="change_un">
      <option value="0" <?php if($settings->change_un==0) echo 'selected="selected"'; ?> >Disabled</option>
      <option value="1" <?php if($settings->change_un==1) echo 'selected="selected"'; ?> >Enabled</option>
      <option value="2" <?php if($settings->change_un==2) echo 'selected="selected"'; ?> >Only once</option>
    </select>
    </div>

    <div class="form-group">
    				<label for="auto_assign_un">Auto Assign Usernames <a class="nounderline" data-toggle="tooltip" title="Don't want users to choose their usernames? No worries! Enable this and a users default username will be their first initial and last name. This already exists? Okay! Lets do first name and last initial. Still not good? Email address it is since you won't be duplicating emails in your database! This will remove the username field from both the external and internal registration forms. Default: Disabled.">?</a></label>
    				<select id="auto_assign_un" class="form-control" name="auto_assign_un">
    								<option value="1" <?php if($settings->auto_assign_un==1) echo 'selected="selected"'; ?> >Enabled</option>
    								<option value="0" <?php if($settings->auto_assign_un==0) echo 'selected="selected"'; ?> >Disabled</option>
    				</select>
    </div>

    <div class="form-group">
    <label for="min_pw">Minimum Password Length</label>
    <input type="text" class="form-control" name="min_pw" id="min_pw" value="<?=$settings->min_pw?>">
    </div>
    <div class="form-group">
    <label for="max_pw">Maximum Password Length</label>
    <input type="text" class="form-control" name="max_pw" id="max_pw" value="<?=$settings->max_pw?>">
    </div>
  </div>

<div class="col-xs-12 col-sm-6">
  <!-- right -->

  <div class="form-group">
          <label for="twofa">Two FA <a class="nounderline" data-toggle="tooltip" title="Two Factor Auth is here! Default: Disabled.">?</a></label>
          <select id="twofa" class="form-control" name="twofa">
                  <option value="1" <?php if($settings->twofa==1) echo 'selected="selected"'; ?> >Enabled</option>
                  <option value="0" <?php if($settings->twofa==0) echo 'selected="selected"'; ?> >Disabled</option>
                  <option value="-1">Disabled and Reset All Users Two FA</option>
          </select>
  </div>

  <div class="form-group">
  <label for="req_num">Recommend a Number in the Password?</label>
  <select id="req_num" class="form-control" name="req_num">
          <option value="1" <?php if($settings->req_num==1) echo 'selected="selected"'; ?> >Yes</option>
          <option value="0" <?php if($settings->req_num==0) echo 'selected="selected"'; ?> >No</option>
  </select>
  </div>
  <div class="form-group">
  <label for="req_cap">Recommend a Capital Letter in the Password?</label>
  <select id="req_cap" class="form-control" name="req_cap">
          <option value="1" <?php if($settings->req_cap==1) echo 'selected="selected"'; ?> >Yes</option>
          <option value="0" <?php if($settings->req_cap==0) echo 'selected="selected"'; ?> >No</option>
  </select>
  </div>
  <div class="form-group">
  <label for="min_un">Minimum Username Length</label>
  <input type="text" class="form-control" name="min_un" id="min_un" value="<?=$settings->min_un?>">
  </div>
  <div class="form-group">
  <label for="max_un">Maximum Username Length</label>
  <input type="text" class="form-control" name="max_un" id="max_un" value="<?=$settings->max_un?>">
  </div>

</div>


</div>

<input type="hidden" name="csrf" value="<?=$token?>" />

<p><input class='btn btn-large btn-primary' type='submit' name="register" value='Save Registration Settings'/></p>
</form>
