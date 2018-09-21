<form class="" action="admin.php?tab=5" name="css" method="post">
		<!-- Test CSS Settings -->
		<h2>Sitewide CSS</h2>

		<div class="form-group">
			<label for="us_css1">Primary Color Scheme <a class="nounderline" data-toggle="tooltip" title="Loaded 1st">?</a></label>
			<select class="form-control" name="us_css1" id="us_css1" >
				<option selected="selected"><?=$settings->us_css1?></option>
				<?php
				$css_userspice=glob('../users/css/color_schemes/*.css');
				$css_custom=glob('../usersc/css/color_schemes/*.css');
				foreach(array_merge($css_userspice,$css_custom) as $filename){
				echo "<option value=".$filename.">".$filename."";
				}
				?>
			</select>
		</div>

		<div class="form-group">
			<label for="us_css2">Secondary UserSpice CSS <a class="nounderline" data-toggle="tooltip" title="Loaded 2nd">?</a></label>
			<select class="form-control" name="us_css2" id="us_css2">
				<option selected="selected"><?=$settings->us_css2?></option>
				<?php
				$css_userspice=glob('../users/css/*.css');
				$css_custom=glob('../usersc/css/*.css');
				foreach(array_merge($css_userspice,$css_custom) as $filename){
				echo "<option value=".$filename.">".$filename."";
				}
				?>
			</select>
		</div>

		<div class="form-group">
			<label for="us_css3">Custom UserSpice CSS <a class="nounderline" data-toggle="tooltip" title="Loaded 3rd">?</a></label>
			<select class="form-control" name="us_css3" id="us_css3">
				<option selected="selected"><?=$settings->us_css3?></option>
				<?php
				$css_userspice=glob('../users/css/*.css');
				$css_custom=glob('../usersc/css/*.css');
				foreach(array_merge($css_userspice,$css_custom) as $filename){
				echo "<option value=".$filename.">".$filename."";
				}
				?>
			</select>
		</div>
	<input type="hidden" name="csrf" value="<?=$token?>" />
		<p><input class='btn btn-large btn-primary' type='submit' name="css" value='Save CSS Settings'/></p>
		</form>
