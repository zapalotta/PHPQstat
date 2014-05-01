<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
require_once( 'config.php' );


require_once( "include/accounting.inc.php" );

//$accounting_file = "/usr/local/grid/sge/sge6.2u7/bsse/common/accounting";

$startdate = date( "Y-m-d h:i:s", strtotime( htmlspecialchars( $_GET['startdate'] ) ) );
$username = trim( htmlspecialchars( $_GET['username'] ) ) ;
$groupname = trim( htmlspecialchars( $_GET['groupname'] ) );
$queuename = trim( htmlspecialchars( $_GET['queuename'] ) );
$limit = trim( htmlspecialchars( $_GET['limit'] ) );
$mode = trim( htmlspecialchars( $_GET['mode'] ) );

$acct = new Accounting( );


header('Content-Type: application/json; charset=utf-8', true,200);
if ( !in_array ( $_SERVER['AUTHENTICATE_UID'], $accounting_users ) ) {
  // Not authenticated, use errorcode 99
  echo ( json_encode( array_map( function($t){ return is_string($t) ? utf8_encode($t) : $t; }, array( "errormsg" => "ERROR: not authenticated", "errorcode" => 99 ) ) ) );
}
else {
  echo ( json_encode( array_map( function($t){ return is_string($t) ? utf8_encode($t) : $t; }, $acct->getAjaxStatistics( $mode, $groupname, $queuename, $startdate, $limit ) ) ) );
}




//echo ( json_encode( array_map( function($t){ return is_string($t) ? utf8_encode($t) : $t; }, $ret ) ) );

?>
