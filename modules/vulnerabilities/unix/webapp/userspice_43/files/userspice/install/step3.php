<?php
require_once("install/includes/header.php");
?>
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

                </a></li>
              </ul>
          </div>
          <div class="row">
              <div class="col-xs-3"></div>
              <div class="col-xs-6">
                <H2>Final Cleanup</H2>
                <p>
                  Congratulations! You can now cleanup the install files and begin using your software. If you have any problems, you can edit the init.php directly or reinstall the app.
                </p><br><br>
                  <a class="btn btn-danger" href="cleanup.php">Cleanup Install Files</a>

              </div>
              </div>
    	</div>
    </div>
<?php require_once("install/includes/header.php"); ?>
