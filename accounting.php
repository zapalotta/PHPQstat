<?php
ini_set('display_errors',1); 
ini_set('max_execution_time', 600);
ini_set('memory_limit', '4096M');
error_reporting(E_ALL);


require_once( "include/accounting.inc.php" );

$accounting_file = "/usr/local/grid/sge/sge6.2u7/bsse/common/accounting";


//    echo date("H:i:s") . "<br>";

$acct = new Accounting();

$acct->getAllJobIDs( "2014-01-01 00:00:00" );
echo "Unique job IDs: " . sizeof( $acct->job_ids ) . "<br />";

$acct->getAllOwners();
echo "Unique owners: " . sizeof( $acct->owners ) . "<br />";

$acct->getAllGroups();
echo "Unique groups: " . sizeof( $acct->groups ) . "<br />";

$acct->getAllQueues();
echo "Unique queues: " . sizeof( $acct->queues ) . "<br />";


echo "<pre>";
$acct->getAllJobsByJobID ( '922922' );
echo "Jobs by 922922: " . sizeof( $acct->jobs ) . "<br />";


//print_r ( $acct->getAllJobsByOwner ( 'dodirk' ) );
print_r ( $acct->getRuntimes( $acct->getAllJobsByOwner ( 'arhofman' ) ) );

//print_r ( $acct->getRuntimes( $acct->getAllJobsByJobID ( '922922' ) ) );

//$acct->getRuntimes();

print_r ( $acct->owners );
print_r ( $acct->groups );
print_r ( $acct->queues );
print_r ( $acct->getNumberOfJobsByOwners() );

echo "</pre>";



?>
