<?php
die("You need to customize this page before using it");
//comment out the line above when you're ready to try

//This is a very basic tutorial of how to subscribe to mqtt messages using your php server.
//Note: I don't think php is the best way to do this, I'm a big fan of node-red for this sort of thing.
//But, here's how you do it.
//Note2: This page does not have a GUI. It's made to be run in the background on your server.
//Navigate to your userspice/users folder and simply type
//php subscribe.php
//and just leave this file running. It will enter all messages into the db.

//Paste your own globals config array from users/init.php here...
$GLOBALS['config'] = array(
	'mysql'      => array('host'         => 'localhost',
'username'     => 'root',
'password'     => '',
'db'           => '43',
),
'remember'        => array(
  'cookie_name'   => 'pmqesoxiw318374csb',
  'cookie_expiry' => 604800  //One week, feel free to make it longer
),
'session' => array(
  'session_name' => 'user',
  'token_name' => 'token',
)
);

require_once 'classes/Config.php';
require_once 'classes/DB.php';
require_once 'classes/Input.php';
require_once 'classes/Validate.php';
require_once 'classes/phpMQTT.php';
$db = DB::getInstance();

//put in your mqtt server credentials
$server = "192.168.95.222";     // change if necessary
$port = 1883;                     // change if necessary
$username = "";                   // set your username
$password = "";                   // set your password
$client_id = "phpMQTT-subscriber"; // make sure this is unique for connecting to sever - you could use uniqid()


$mqtt = new phpMQTT($server, $port, $client_id);
if(!$mqtt->connect(true, NULL, $username, $password)) {
	exit(1);
}
$topics['test'] = array("qos" => 0, "function" => "procmsg");
$mqtt->subscribe($topics, 0);
while($mqtt->proc()){

}
$mqtt->close();
function procmsg($topic, $msg){
  global $db;
    $topic = filter_var($topic, FILTER_SANITIZE_STRING);
    $msg = filter_var($msg ,FILTER_SANITIZE_STRING);
    echo "Topic: {$topic}\n\n";
		echo "\t$msg\n\n";
//right now it is just inserting this data into a table. Feel free to do it differently or to
//do other data validation
    $db->insert('mqttmsg',['msg'=>$msg,'topic'=>$topic]);

}
