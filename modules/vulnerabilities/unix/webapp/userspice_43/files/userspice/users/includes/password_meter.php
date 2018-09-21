<!--strength meter-->
<?php
$password_meterURL = 'js/zxcvbn-bootstrap-strength-meter.js';
?>
<script type="text/javascript">
// modification to disable submit button on _join.php and _forgot_password_reset.php until password strength is Strong or Very Strong
if ( result.score < 3 ) {
$('#password_strength').prop('disabled', true);
} else {
$('#password_strength').prop('disabled', false);
}
</script>
<script type="text/javascript" src="<?php echo $password_meterURL; ?>"></script>

<!-- Hook up strength bar on page load-->
<script type="text/javascript">
$(document).ready(function()
{
$("#StrengthProgressBar").zxcvbnProgressBar({ passwordInput: "#password" });
});
</script>
