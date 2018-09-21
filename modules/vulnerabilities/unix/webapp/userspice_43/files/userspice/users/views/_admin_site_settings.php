<!-- Site Settings -->
<form class="" action="admin.php?tab=2" name="settings" method="post">
<h2 >Site Settings</h2>
<div class="row">
	<div class="col-xs-12 col-sm-6">
		<!-- Left -->

		<!-- Site Name -->
		<div class="form-group">
		<label for="site_name">Site Name <a class="nounderline" data-toggle="tooltip" title="Modify this to change the name of your site, including in the <title> tag, the maintenance page and some system emails.">?</a></label>
		<input type="text" class="form-control" name="site_name" id="site_name" value="<?=$settings->site_name?>">
		</div>

	<!-- Copyright Option -->
		<div class="form-group">
		<label for="copyright">Copyright Message <a class="nounderline" data-toggle="tooltip" title="This message will be at the bottom of every page. The copyright symbol and year are automatically added.">?</a></label>
		<input type="text" class="form-control" name="copyright" id="copyright" value="<?=$settings->copyright?>">
		</div>

		<!-- Recaptcha Option -->
		<div class="form-group">
			<label for="recaptcha">Recaptcha <a class="nounderline" data-toggle="tooltip" title="Use the Google Recpatcha to protect yourself from spam registrations and logins, and to verify the legitimacy of a users session. You can set this to Enabled for Registration and Logins, or just Registrations. Default: Disabled.">?</a></label>
			<select id="recaptcha" class="form-control" name="recaptcha">
				<option value="1" <?php if($settings->recaptcha==1) echo 'selected="selected"'; ?> >Enabled</option>
				<option value="0" <?php if($settings->recaptcha==0) echo 'selected="selected"'; ?> >Disabled</option>
				<option value="2" <?php if($settings->recaptcha==2) echo 'selected="selected"'; ?> >For Join Only</option>
			</select>
		</div>

	<div class="form-group">
    <label for="min_pw">Recaptcha Public (Site) Key</label> <?php if(in_array($user->data()->id, $master_account)) {?><a href="#" class="nounderline" id="recapatcha_public_show"><span class="glyphicon glyphicon-eye-open"></span> show</a><?php } ?>
    <input type="password" autocomplete="off" class="form-control" name="recap_public" id="recap_public" value="<?=$settings->recap_public?>">
    </div>

	<div class="form-group">
    <label for="max_pw">Recaptcha Private (Secret) Key</label> <?php if(in_array($user->data()->id, $master_account)) {?><a href="#" class="nounderline" id="recapatcha_private_show"><span class="glyphicon glyphicon-eye-open"></span> show</a><?php } ?>
    <input type="password" autocomplete="off" class="form-control" name="recap_private" id="recap_private" value="<?=$settings->recap_private?>">
    </div>

		<!-- Messaging Option -->
		<div class="form-group">
			<label for="messaging">Messaging <a class="nounderline" data-toggle="tooltip" title="Enable or disable the built in messaging system which features Mass Messaging, user-specific messaging with replies in thread format and email notifications. Default: Disabled.">?</a></label>
			<select id="messaging" class="form-control" name="messaging">
				<option value="1" <?php if($settings->messaging==1) echo 'selected="selected"'; ?> >Enabled</option>
				<option value="0" <?php if($settings->messaging==0) echo 'selected="selected"'; ?> >Disabled</option>
			</select>
		</div>

		<!-- echouser Option -->
		<div class="form-group">
			<label for="echouser">echouser Function <a class="nounderline" data-toggle="tooltip" title="What do you want to echo when you use the echouser function? You can use this to echo their name in several different formats. Need their username instead? Use echousername. If it cannot find the user, it will echo Deleted. Default: FName LName.">?</a></label>
			<select id="echouser" class="form-control" name="echouser">
				<option value="0" <?php if($settings->echouser==0) echo 'selected="selected"'; ?> >FName LName</option>
				<option value="1" <?php if($settings->echouser==1) echo 'selected="selected"'; ?> >Username</option>
				<option value="2" <?php if($settings->echouser==2) echo 'selected="selected"'; ?> >Username (FName LName)</option>
				<option value="3" <?php if($settings->echouser==3) echo 'selected="selected"'; ?> >Username (FName)</option>
			</select>
		</div>

		<!-- WYSIWYG Option -->
		<div class="form-group">
			<label for="wys">WYSIWYG Editor <a class="nounderline" data-toggle="tooltip" title="This does what it says. If you want to disable the Editor, you can change this. This is used in the messaging system. Default: Enabled.">?</a></label>
			<select id="wys" class="form-control" name="wys">
				<option value="0" <?php if($settings->wys==0) echo 'selected="selected"'; ?> >Disabled</option>
				<option value="1" <?php if($settings->wys==1) echo 'selected="selected"'; ?> >Enabled</option>
			</select>
		</div>

		<!-- Navigation Type Option -->
		<div class="form-group">
			<label for="navigation_type">Navigation Type <a class="nounderline" data-toggle="tooltip" title="As of U 4.3 navigations can be controlled from the database, switch between the original and database-driven navigaton here. Default: Non-Database Driven.">?</a></label>
			<select id="navigation_type" class="form-control" name="navigation_type">
				<option value="0" <?php if($settings->navigation_type==0) echo 'selected="selected"'; ?> >Non-Database Driven</option>
				<option value="1" <?php if($settings->navigation_type==1) echo 'selected="selected"'; ?> >Database-Driven</option>
			</select>
		</div>

		<!-- Force SSL -->
		<div class="form-group">
			<label for="force_ssl">Force HTTPS Connections <a class="nounderline" data-toggle="tooltip" title="Don't want anyone accessing your site insecurely? Enabled this. This will redirect any users from an HTTP (non-secure) connection to HTTPS. Make sure your SSL Cert is valid before doing this! Default: No.">?</a></label>
			<select id="force_ssl" class="form-control" name="force_ssl">
				<option value="1" <?php if($settings->force_ssl==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($settings->force_ssl==0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<!-- Cron Job Security -->
		<a name="cron"></a>
		<div class="form-group">
			<label for="cron_ip">Only allow cron jobs from the following IP <a class="nounderline" data-toggle="tooltip" title="Cron jobs are automated server tasks that can make your life easier.  You may want to make sure, though, that they originate from you and not someone else.  You can whitelist an ip address here.">?</a></label>
				<input type="text" class="form-control" name="cron_ip" id="cron_ip" value="<?=$settings->cron_ip?>" placeholder="<?php if($settings->cron_ip == ''){echo 'No security is IP is set';}?>">
		</div>


</div>

	<!-- right column -->
	<div class="col-xs-12 col-sm-6">
		<!-- Force Password Reset -->





		<div class="form-group">
				<label for="force_user_pr">Force Password Reset <a class="nounderline" data-toggle="tooltip" title="This will change the force_pr value in your users database for all users to 1, requiring every user including the current one to reset their password. They will not be able to leave the user settings page until this make this change. This will always be no, however when you change it to Yes and save changes, it will perform the above action, and reset back to no. This isn't a setting, but a function.">?</a></label>
				<select id="force_user_pr" class="form-control" name="force_user_pr">
						<option value="0" selected>No</option>
						<option value="1">Yes</option>
				</select>
		</div>

		<!-- Force Password Reset -->
		<div class="form-group">
				<label for="force_pr">Force Password Reset on Manual Creation <a class="nounderline" data-toggle="tooltip" title="When a user is created from the admin panel, force their password to be reset upon login, this will also send them a password reset link on manual creation no matter what password you enter on the form. If you enable this, the force_pr value in your users database for this user will be 1 when created. Default: No.">?</a></label>
				<select id="force_pr" class="form-control" name="force_pr">
						<option value="1" <?php if($settings->force_pr==1) echo 'selected="selected"'; ?> >Yes</option>
						<option value="0" <?php if($settings->force_pr==0) echo 'selected="selected"'; ?> >No</option>
				</select>
		</div>

		<!-- Site Offline -->
		<div class="form-group">
			<label for="site_offline">Site Offline <a class="nounderline" data-toggle="tooltip" title="Need to go into Maintenance Mode to do an upgrade? Enable this! This will display a 'Maintenance Mode Active' message for those in the default Administrator permission group (ID: 2) and redirect the remaining to the maintenance page. This will occur until the setting is disabled. Default: No.">?</a></label>
			<select id="site_offline" class="form-control" name="site_offline">
				<option value="1" <?php if($settings->site_offline==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($settings->site_offline==0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<!-- Track Guests -->
		<div class="form-group">
			<label for="track_guest">Track Guests <a class="nounderline" data-toggle="tooltip" title="Want details on how many visitors are visiting your site? Keep this on! Site getting laggy, stumbling and having issues? Disabled this to see if it fixes it for higher volume sites. Default: Yes.">?</a></label>
			<select id="track_guest" class="form-control" name="track_guest">
				<option value="1" <?php if($settings->track_guest==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($settings->track_guest==0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<div class="form-group">
						<label for="permission_restriction">Permission Restrictions <a class="nounderline" data-toggle="tooltip" title="Use this as a safeguard to only allow users to add/remove permission levels they have access to. You might use this in a format to give certain users access to add/remove users or make site changes, but you don't want them to give other users permissions they don't have, or take those away. Your safeguard for this (in your own case if you have certain permissions not assigned to yourself) is by restricting admin_pages.php and admin_page.php to the default Level 2 as you can do anything from these pages currently. This will still show the user the levels on admin_user.php but will have a disabled attribute. Default: Disabled.">?</a></label>
						<select id="permission_restriction" class="form-control" name="permission_restriction">
										<option value="1" <?php if($settings->permission_restriction==1) echo 'selected="selected"'; ?> >Enabled</option>
										<option value="0" <?php if($settings->permission_restriction==0) echo 'selected="selected"'; ?> >Disabled</option>
						</select>
		</div>

		<div class="form-group">
						<label for="page_page_permission_restriction">Page Permission Restrictions <a class="nounderline" data-toggle="tooltip" title="Only allow one permission level per page using this setting. This is particularly good for ensuring no overlap in permission levels. You can have a permission group hierarchy such as this: User, User Manager, Database Manager, Administrator. In this case you want to give all your User Managers access to admin_user.php and admin_users.php, and yourself of course, but many not to those who manage your database only (maybe you want to give them access to site and email settings only). In any case, it will change the checkboxes on admin_page.php to radio buttons under Add Permission Level and restrict addition from admin_permission.php to be added only if no other level has it. Default: Disabled.">?</a></label>
						<select id="page_permission_restriction" class="form-control" name="page_permission_restriction">
										<option value="1" <?php if($settings->page_permission_restriction==1) echo 'selected="selected"'; ?> >Enabled</option>
										<option value="0" <?php if($settings->page_permission_restriction==0) echo 'selected="selected"'; ?> >Disabled</option>
						</select>
		</div>

		<div class="form-group">
						<label for="page_default_private">New Pages Default To "Private" <a class="nounderline" data-toggle="tooltip" title="Does what it says. Default: Enabled.">?</a></label>
						<select id="page_default_private" class="form-control" name="page_default_private">
										<option value="1" <?php if($settings->page_default_private==1) echo 'selected="selected"'; ?> >Enabled</option>
										<option value="0" <?php if($settings->page_default_private==0) echo 'selected="selected"'; ?> >Disabled</option>
						</select>
		</div>

		<!-- Notification System -->
		<div class="form-group">
			<label for="notifications">Notification System <a class="nounderline" data-toggle="tooltip" title="Enable or disable the notification system. Default: Disabled.">?</a></label>
			<select id="notifications" class="form-control" name="notifications">
				<option value="0" <?php if($settings->notifications==0) echo 'selected="selected"'; ?> >Disabled</option>
				<option value="1" <?php if($settings->notifications==1) echo 'selected="selected"'; ?> >Enabled</option>
			</select>
		</div>

		<!-- Notification System -->
		<div class="form-group">
			<label for="force_notif">Force users to see notifications <a class="nounderline" data-toggle="tooltip" title="With this enabled, notifications will popup automatically for your users. It could be annoying, but in some situatuons, notifications are crucial.">?</a></label>
			<select id="force_notif" class="form-control" name="force_notif">
				<option value="0" <?php if($settings->force_notif==0) echo 'selected="selected"'; ?> >Disabled</option>
				<option value="1" <?php if($settings->force_notif==1) echo 'selected="selected"'; ?> >Enabled</option>
			</select>
		</div>

		<!-- Expiration for Notifications Setting -->
		<div class="form-group">
			<label for="notif_daylimit">Expiration for Notifications <a class="nounderline" data-toggle="tooltip" title="Notifications are archived from user views automatically when they load a page that has the HTML footer (by default all US files) based on the date of the notification, and the difference between that date and now. Change this to increase or decrease the amount of days. Minimum: 1, Maximum: 999, Default: 7.">?</a></label>
			<div class="input-group">
				<input type="text" class="form-control" name="notif_daylimit" id="notif_daylimit" value="<?=$settings->notif_daylimit?>">
				<span class="input-group-addon">Days</span>
			</div>
		</div>

		<!-- Custom Settings Option -->
		<div class="form-group">
			<label for="custom_settings">Custom Settings Tab <a class="nounderline" data-toggle="tooltip" title="Enabling this will add a custom settings panel on the right of this window.">?</a></label>
			<select id="custom_settings" class="form-control" name="custom_settings">
				<option value="1" <?php if($settings->custom_settings==1) echo 'selected="selected"'; ?> >Enabled</option>
				<option value="0" <?php if($settings->custom_settings==0) echo 'selected="selected"'; ?> >Disabled</option>
			</select>
		</div>

	</div>
</div>



<input type="hidden" name="csrf" value="<?=$token?>" />

<p><input class='btn btn-primary' type='submit' name="settings" value='Save Site Settings' /></p>
</form>
