<?php
ini_set('max_execution_time', 1356);
ini_set('memory_limit','1024M');
?><?php require_once("install_settings.php"); ?>
<html class="no-js" lang="">
   <head>
       <meta charset="utf-8">
       <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
       <title>InstallSpice</title>
       <meta name="description" content="">
       <meta name="viewport" content="width=device-width, initial-scale=1">

       <link rel="stylesheet" href="install/css/bootstrap.min.css">


               <style>
                   body {
                       padding-top: 50px;
                       padding-bottom: 20px;
                   }
               </style>
               <link rel="stylesheet" href="install/css/bootstrap-theme.min.css">
               <link rel="stylesheet" href="install/css/main.css">

           </head>
           <body>
 <?php
 function redirect($location = null){
   if ($location) {
       if (!headers_sent()){
           header('Location: '.$location);
           exit();
         } else {
           echo '<script type="text/javascript">';
           echo 'window.location.href="'.$location.'";';
           echo '</script>';
           echo '<noscript>';
           echo '<meta http-equiv="refresh" content="0;url='.$location.'" />';
           echo '</noscript>'; exit;
         }
   }
 }
 ?>
            <div align="center">
Having Problems?  <a class="btn btn-primary" href="recovery.php">Reset and Start Over</a></div>
