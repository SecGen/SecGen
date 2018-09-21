<?php require_once("install/includes/header.php"); ?>
<div class="container">
 <div class="row">
        <div class="col-xs-12">
            <ul class="nav nav-pills nav-justified thumbnail">
                <li><a href="#">
                    <h4 class="list-group-item-heading">Step 1</h4>
                    <p class="list-group-item-text"><?=$step1?></p>
                </a></li>
                <li><a href="#">
                    <h4 class="list-group-item-heading">Step 2</h4>
                    <p class="list-group-item-text"><?=$step2?></p>
                </a></li>
                <li><a href="#">
                    <h4 class="list-group-item-heading">Step 3</h4>
                    <p class="list-group-item-text"><?=$step3?></p>
                </a></li>
              </ul>
          </div>
          <div class="row">
              <div class="col-xs-3"></div>
              <div class="col-xs-6">
                <H2>Having Problems?</H2>
                <p>
                It's easy to start over!
                </p><br><br>
                <form class="form" action="" method="post">

                <input class="btn btn-danger" type="submit" name="submit" value="Reset and Start Over!">
                </form>
<?php
//If Submitted
if (!empty($_POST['submit'])) {
  $chunk = file_get_contents("install/chunks/restore.php");

  $fh=fopen($config_file , "w+");

fwrite($fh , $chunk);

fclose($fh);
redirect("index.php");
?>
<?php
}

?>


              </div>
              </div>
    	</div>
    </div>
<?php require_once("install/includes/header.php"); ?>
