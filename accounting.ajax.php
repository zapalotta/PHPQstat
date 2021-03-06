<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);


require_once( "include/accounting.inc.php" );

$accounting_file = "/usr/local/grid/sge/sge6.2u7/bsse/common/accounting";

$startdate = date( "Y-m-d h:i:s", strtotime( htmlspecialchars( $_GET['startdate'] ) ) );
$username = trim( htmlspecialchars( $_GET['username'] ) ) ;
$groupname = trim( htmlspecialchars( $_GET['groupname'] ) );

$acct = new Accounting( );

$acct->loadAllJobsFiltered( $startdate, $groupname, $username );

header('Content-Type: application/json; charset=utf-8', true,200);

echo ( json_encode( array_map( function($t){ return is_string($t) ? utf8_encode($t) : $t; }, $acct->getAjaxJobs() ) ) );

?>
