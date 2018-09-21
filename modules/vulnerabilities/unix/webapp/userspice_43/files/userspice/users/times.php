<?php  //require_once("includes/userspice/us_header.php"); ?>
<!-- stuff can go here -->

<?php
//if (!securePage($_SERVER['PHP_SELF'])){die();}

	$london_tz    = new DateTimeZone("Europe/London");
    $alaska_tz = new DateTimeZone("America/Anchorage");
    $an_tz     = new DateTimeZone("America/Toronto");

    $london_time    = new DateTime("now", $london_tz);
    $alaska_time = new DateTime("now", $alaska_tz);
    $an_time     = new DateTime("now", $an_tz);

    
   
  
	
	?>
	<div class="row text-center">
	
		<div class="col-xs-4">
			<div><p>Anchorage</p><strong class="text-success"><?php echo $alaska_time->format("H:i");	?></strong></div>
		</div>		

		<div class="col-xs-4">
			<div><p>Ottawa</p> <strong class="text-success"><?php	 echo $an_time->format("H:i");	?></strong></div>
		</div>	

		<div class="col-xs-4">
			<div><p>London</p> <strong class="text-success"><?php	  echo $london_time->format("H:i");	?></strong></div>
		</div>
				
	</div>
				
	