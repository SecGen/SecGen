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

Special thanks to John Bovey for the password strenth feature.
*/
?>
<div class="row">
<div class="col-xs-12">
<?php
if (!$form_valid && Input::exists()){?>
      <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
<?php }
$settingsQ = $db->query("SELECT * FROM settings");
$settings = $settingsQ->first();
?>

<form class="form-signup" action="<?=$form_action;?>" method="<?=$form_method;?>" id="payment-form">

        <h2 class="form-signin-heading"> <?=lang("SIGNUP_TEXT","");?></h2>

        <div class="form-group">

                <?php if($settings->auto_assign_un==0) {?><label>Username:</label>&nbsp;&nbsp;<span id="usernameCheck" class="small"></span>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php if (!$form_valid && !empty($_POST)){ echo $username;} ?>" required autofocus><?php } ?>


                <label for="fname">First Name*</label>
                <input type="text" class="form-control" id="fname" name="fname" placeholder="First Name" value="<?php if (!$form_valid && !empty($_POST)){ echo $fname;} ?>" required autofocus>

                <label for="lname">Last Name*</label>
                <input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name" value="<?php if (!$form_valid && !empty($_POST)){ echo $lname;} ?>" required>

                <label for="email">Email Address*</label>
                <input  class="form-control" type="text" name="email" id="email" placeholder="Email Address" value="<?php if (!$form_valid && !empty($_POST)){ echo $email;} ?>" required >

<?php

                $character_range = 'Be between '.$settings->min_pw . ' and ' . $settings->max_pw;
                $character_statement = '<span id="character_range" class="gray_out_text">' . $character_range . ' characters</span>';

if ($settings->req_cap == 1){
                $num_caps = '1'; //Password must have at least 1 capital
                if($num_caps != 1){
                        $num_caps_s = 's';
                }
                $num_caps_statement = '<span id="caps" class="gray_out_text">Have at least ' . $num_caps . ' capital letter </span>';
}

if ($settings->req_num == 1){
                $num_numbers = '1'; //Password must have at least 1 number
                if($num_numbers != 1){
                        $num_numbers_s = 's';
                }

                $num_numbers_statement = '<span id="number" class="gray_out_text">Have at least ' . $num_numbers . ' number</span>';
}
                $password_match_statement = '<span id="password_match" class="gray_out_text">Be typed correctly twice</span>';


                //2.) Apply default class to gray out green check icon
                echo '
                        <style>
                                .gray_out_icon{
                                        -webkit-filter: grayscale(100%); /* Safari 6.0 - 9.0 */
                                        filter: grayscale(100%);
                                }
                                .gray_out_text{
                                        opacity: .5;
                                }
                        </style>
                ';

                //3.) Javascript to check to see if user has met conditions on keyup (NOTE: It seems like we shouldn't have to include jquery here because it's already included by UserSpice, but the code doesn't work without it.)
                echo '
                        <script type="text/javascript">
                        $(document).ready(function(){

                                $( "#password" ).keyup(function() {
                                        var pswd = $("#password").val();

                                        //validate the length
                                        if ( pswd.length >= ' . $settings->min_pw . ' && pswd.length <= ' . $settings->max_pw . ' ) {
                                                $("#character_range_icon").removeClass("gray_out_icon");
                                                $("#character_range").removeClass("gray_out_text");
                                        } else {
                                                $("#character_range_icon").addClass("gray_out_icon");
                                                $("#character_range").addClass("gray_out_text");
                                        }

                                        //validate capital letter
                                        if ( pswd.match(/[A-Z]/) ) {
                                                $("#num_caps_icon").removeClass("gray_out_icon");
                                                $("#caps").removeClass("gray_out_text");
                                        } else {
                                                $("#num_caps_icon").addClass("gray_out_icon");
                                                $("#caps").addClass("gray_out_text");
                                        }

                                        //validate number
                                        if ( pswd.match(/\d/) ) {
                                                $("#num_numbers_icon").removeClass("gray_out_icon");
                                                $("#number").removeClass("gray_out_text");
                                        } else {
                                                $("#num_numbers_icon").addClass("gray_out_icon");
                                                $("#number").addClass("gray_out_text");
                                        }
                                });

                                $( "#confirm" ).keyup(function() {
                                        var pswd = $("#password").val();
                                        var confirm_pswd = $("#confirm").val();

                                        //validate password_match
                                        if (pswd == confirm_pswd) {
                                                $("#password_match_icon").removeClass("gray_out_icon");
                                                $("#password_match").removeClass("gray_out_text");
                                        } else {
                                                $("#password_match_icon").addClass("gray_out_icon");
                                                $("#password_match").addClass("gray_out_text");
                                        }

                                });
                        });
                        </script>
                ';

?>

                <div style="display: inline-block">
                        <label for="password">Choose a Password* (Between <?=$settings->min_pw?> and <?=$settings->max_pw?> characters)</label>
                        <input  class="form-control" type="password" name="password" id="password" placeholder="Password" required autocomplete="off" aria-describedby="passwordhelp">

                        <label for="confirm">Confirm Password*</label>
                        <input  type="password" id="confirm" name="confirm" class="form-control" placeholder="Confirm Password" required autocomplete="off" >
                </div>
                <div style="display: inline-block; padding-left: 20px">
                        <strong>Passwords Should...</strong><br>
                        <span id="character_range_icon" class="glyphicon glyphicon-ok gray_out_icon" style="color: green"></span>&nbsp;&nbsp;<?php echo $character_statement;?>
                        <br>
<?php
if ($settings->req_cap == 1){ ?>
                        <span id="num_caps_icon" class="glyphicon glyphicon-ok gray_out_icon" style="color: green"></span>&nbsp;&nbsp;<?php echo $num_caps_statement;?>
                        <br>
<?php }

if ($settings->req_num == 1){ ?>
                        <span id="num_numbers_icon" class="glyphicon glyphicon-ok gray_out_icon" style="color: green"></span>&nbsp;&nbsp;<?php echo $num_numbers_statement;?>
                        <br>
<?php } ?>
                        <span id="password_match_icon" class="glyphicon glyphicon-ok gray_out_icon" style="color: green"></span>&nbsp;&nbsp;<?php echo $password_match_statement;?>
                        <br><br>
                        <a class="nounderline" id="password_view_control"><span class="glyphicon glyphicon-eye-open"></span> Show Passwords</a>
                </div>
                <br><br>

                <?php include('../usersc/scripts/additional_join_form_fields.php'); ?>

                <label for="confirm">Registration User Terms and Conditions</label>
                <textarea id="agreement" name="agreement" rows="5" class="form-control" disabled ><?php require $abs_us_root.$us_url_root.'usersc/includes/user_agreement.php'; ?></textarea>

                <label><input type="checkbox" id="agreement_checkbox" name="agreement_checkbox"> Check box to agree to terms</label>
        </div>

        <?php if($settings->recaptcha == 1|| $settings->recaptcha == 2){ ?>
        <div class="g-recaptcha" data-sitekey="<?=$settings->recap_public; ?>" data-bind="next_button" data-callback="submitForm"></div>
        <?php } ?>
        <input type="hidden" value="<?=Token::generate();?>" name="csrf">
        <button class="submit btn btn-primary " type="submit" id="next_button"><i class="fa fa-plus-square"></i> Register</button>
</form><br />
</div>
</div>
