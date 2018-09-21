<?php require_once("install/includes/header.php"); ?>
<div class="container">
 <div class="row">
        <div class="col-xs-12">
            <ul class="nav nav-pills nav-justified thumbnail">
                <li><a href="#">
                    <h4 class="list-group-item-heading">Step 1</h4>
                    <p class="list-group-item-text"><?=$step1?></p>
                </a></li>
                <li class="active"><a href="#">
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
              <?php  if (!empty($_POST)){
                  echo "We are importing the tables...one moment please!"; } ?>
                <H2>Please fill in your information</H2>
                <form class="form" action="" method="post">
                  <label for="dbh">Region/Timezone (required)</label><br>

                    <?php
    $regions = array(
        'Africa' => DateTimeZone::AFRICA,
        'America' => DateTimeZone::AMERICA,
        'Antarctica' => DateTimeZone::ANTARCTICA,
        'Asia' => DateTimeZone::ASIA,
        'Atlantic' => DateTimeZone::ATLANTIC,
        'Europe' => DateTimeZone::EUROPE,
        'Indian' => DateTimeZone::INDIAN,
        'Pacific' => DateTimeZone::PACIFIC
    );
    $timezones = array();
    foreach ($regions as $name => $mask)
    {
        $zones = DateTimeZone::listIdentifiers($mask);
        foreach($zones as $timezone)
        {
        // Lets sample the time there right now
        $time = new DateTime(NULL, new DateTimeZone($timezone));
        // Us dumb Americans can't handle millitary time
        $ampm = $time->format('H') > 12 ? ' ('. $time->format('g:i a'). ')' : '';
        // Remove region name and add a sample time
        $timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;
      }
    }
    // View
    print '<select class="form-control" id="timezone" name="timezone" required>';

    foreach($timezones as $region => $list)
    {
      print '<optgroup label="' . $region . '">' . "\n";
      if(!empty($_POST['timezone'])){?>
      <option value="<?=$_POST['timezone']?>" elected="selected"><?=$_POST['timezone']?></option>
      <?php
      }
      foreach($list as $timezone => $name)
      {
        print '<option value="' . $timezone . '"name="' . $timezone . '">' . $name . '</option>' . "\n";
      }
      print '<optgroup>' . "\n";
    }
    print '</select>';?><br>
                  <label for="dbh">Database Host (required)</label>
                  <input required class="form-control" type="text" name="dbh"  value="<?php if (!empty($_POST['dbh'])){ print $_POST['dbh']; } ?>" required></label><br>

                  <label for="dbu">Database User (required)</label>
                  <input required class="form-control" type="text" name="dbu"  value="<?php if (!empty($_POST['dbu'])){ print $_POST['dbu']; } ?>" required></label><br>

                  <label for="dbp">Database Password (usually required)</label>
                  <input class="form-control" type="text" name="dbp"  value="<?php if (!empty($_POST['dbp'])){ print $_POST['dbp']; } ?>"></label><br>

                  <label required for="dbn">Database Name (required)</label>
                <input class="form-control" type="text" name="dbn"  value="<?php if (!empty($_POST['dbn'])){ print $_POST['dbn']; } ?>" required></label><br>

            <label required for="copyright">Copyright Message</label>
          <input class="form-control" type="text" name="copyright"  value="<?php if (!empty($_POST['copyright'])){ print $_POST['copyright']; } ?>"></label><br>


                <input class="btn btn-success" type="submit" name="test" value="Test Settings (This will take a moment)"><br><br>

<?php
//PHP Logic Goes Here
if (!empty($_POST)){
  $fh=fopen($config_file , "a+");

	fwrite($fh ,"");

	fclose($fh);
$fh=fopen($config_file , "a+");
$end = "',";

$dbh_syn="'host'         => '";
$dbh=$_POST['dbh'];

$dbu_syn="'username'     => '";
$dbu=$_POST['dbu'];

$dbp_syn="'password'     => '";
$dbp=$_POST['dbp'];

$dbn_syn="'db'           => '";
$dbn=$_POST['dbn'];
//If Testing
if (!empty($_POST['test'])) {
    $success = true;
try {
    $dsn = "mysql:host=$dbh;dbname=$dbn;charset=utf8";
    $opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
$pdo = new PDO($dsn, $dbu, $dbp, $opt) or die('could not connect');
} catch (PDOException $e) {
    $success = false;
    echo "Database connection <font color='red'><strong>unsuccessful</font></strong>! Please try again.";
}

if ($success) {
    echo "Database connection <font color='green'><strong>successful</font></strong>!<br><br>";
    $link = mysqli_connect($dbh, $dbu, $dbp, $dbn);
    if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
    }

    // Temporary variable, used to store current query
    $templine = '';
    // Read in entire file
    $lines = file($sqlfile);
    // Loop through each line
    foreach ($lines as $line)
    {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '')
        continue;

    // Add this line to the current segment
    $templine .= $line;
    // If it has a semicolon at the end, it's the end of the query
    if (substr(trim($line), -1, 1) == ';')
    {
        // Perform the query
        mysqli_query($link,$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_connect_error() . '<br /><br />');
        // Reset temp variable to empty
        $templine = '';
    }
    }
     echo "Tables imported successfully<br>";

     $copyright = $_POST['copyright'];

     mysqli_query($link,"UPDATE settings SET copyright = $copyright WHERE id = 1");
     echo "Entered your copyright message!";

    ?>
<input class="btn btn-danger" type="submit" name="submit" value="Save Settings >>">
<?php
}
}

//If Submitted
if (!empty($_POST['submit'])) {
  $timezone_syn='$timezone_string = \'';
  $tz=$_POST['timezone'];
fwrite($fh ,
  $dbh_syn . $dbh . $end . PHP_EOL .
  $dbu_syn . $dbu . $end . PHP_EOL .
  $dbp_syn . $dbp . $end . PHP_EOL .
  $dbn_syn . $dbn . $end . PHP_EOL
);
$chunk1 = file_get_contents("install/chunks/chunk1.php");
file_put_contents($config_file, $chunk1, FILE_APPEND);
fclose($fh);
$fh=fopen($config_file , "a+");
$end = "';";
fwrite($fh , $timezone_syn . $tz . $end . PHP_EOL);
fclose($fh);
$chunk2 = file_get_contents("install/chunks/chunk2.php");
file_put_contents($config_file, $chunk2, FILE_APPEND);
fclose($fh);
redirect("step3.php")
?>
<?php
}
}
?>
</form>

              </div>
              </div>
    	</div>
    </div>
<?php require_once("install/includes/header.php"); ?>
