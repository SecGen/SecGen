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
<div class="row">
<div class="col-xs-12">
	<h2>Verify Your Email</h2>
	<ol>
		<li>Enter your email address and click Resend</li>
		<li>Check your email and click the link that is sent to you</li>
		<li>Done</li>
	</ol>
	<form class="" action="verify_resend.php" method="post">
	<?php if(!$errors=='') {?><div class="alert alert-danger"><?=display_errors($errors);?></div><?php } ?>
	<div class="form-group">
	<label for="email">Enter Your Email</label>
	<input class="form-control" type="text" id="email" name="email" placeholder="Email">
	</div>
	<input type="hidden" name="csrf" value="<?=Token::generate();?>">
	<input type="submit" value="Resend" class="btn btn-primary">
</form><br />
</div>
</div>
