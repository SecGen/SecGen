<?php
// Token errors are generally caused by 1 of 2 things.
// 1. Someone trying to perform a man-in-the-middle attack on a form on the site.
// 2. Something accidentally causing the page to partially reload
//
// You can decide what you want for that error message here.
 ?>
<style>
body {
    background-color: white;
}
</style>

<br><br>

<p align="center">There was an error with your form. Please go back and try again. Please note that submitting the form by refreshing the page will cause an error.</p>
<p align="center">If this continues to happen, please contact the administrator.</p>
<p align="center"><a href="javascript:history.back(-1)">Go Back</a></p>

<?php die(); ?>
