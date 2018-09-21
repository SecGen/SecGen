Edit these two files to create your own custom settings panel here<br>
usersc/includes/admin_panel_custom_settings.php (your form)<br>
usersc/includes/admin_panel_custom_settings_post.php (your form processing)<br>
<strong>You can disable this feature in the Site Settings tab to the left</strong>
<form class="" action="admin.php?tab=7" method="post" name="custom_settings">
    <!-- this hook below is important -->
    <input type="hidden" name="custom_settings_hook" value="1">
    <input type="hidden" name="csrf" value="<?=$token?>" />
    <!-- <input type="submit" name="submit" value="submit"> -->
</form>
