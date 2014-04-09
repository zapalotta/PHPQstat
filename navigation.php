<?php
require_once( "config.php" );


function displayNav( $owner = "" ) {
  global $accounting_users;
  echo "<a href='index.php'>Home</a> * 
<a href=\"qhost.php?owner=$owner\">Hosts status</a> * 
<a href=\"qstat.php?owner=$owner\">Queue status</a> * 
<a href=\"qstat_user.php?owner=$owner\">Jobs status ($owner)</a> * 
<a href=\"queueinfo.php?owner=all\">Queue informations</a> * ";
  if ( in_array( $_SERVER['AUTHENTICATE_UID'], $accounting_users ) ) {
    echo "<a href=\"qaccounting.php\">Accounting</a> * ";
  }

  echo "<a href=\"pe.php?owner=all\">PEs</a> * 
<a href=\"about.php?owner=$owner\">About PHPQstat</a>";
}

?>