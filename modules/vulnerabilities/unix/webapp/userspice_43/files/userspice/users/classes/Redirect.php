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
class Redirect {
  public static function to($location = null, $args=''){
    global $us_url_root;
    #die("Redirecting to $location<br />\n");
    if ($location) {
      if (!preg_match('/^https?:\/\//', $location) && !file_exists($location)) {
        foreach (array($us_url_root, '../', 'users/', substr($us_url_root, 1), '../../', '/', '/users/') as $prefix) {
          if (file_exists($prefix.$location)) {
            $location = $prefix.$location;
            $location = preg_replace('~/{2,}~', '/', $location);
            break;
          }
        }
      }
      if ($args) $location .= $args; // allows 'login.php?err=Error+Message' or the like
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

}
