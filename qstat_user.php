<?php
require_once( 'navigation.php' );
?>

<html>

<head>
  <title>PHPQstat</title>
  <meta name="AUTHOR" content="Jordi Blasco Pallares ">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="KEYWORDS" content="gridengine sge sun hpc supercomputing batch queue linux xml qstat qhost jordi blasco solnu">
  <meta http-equiv="refresh" content="30"/>
  <link rel="stylesheet" href="phpqstat.css" type="text/css" />

  <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" type="text/css" />

<script type="text/javascript" language="javascript" src="/media/js/jquery.js"></script> 
<script type="text/javascript" language="javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script> 
   <script type="text/javascript" language="javascript" src="/media/js/jquery.dataTables.js"></script>
 
<script>
   $(document).ready(function() {
       $('.qstat').dataTable( {
	   "bPaginate": false,
	     "bLengthChange": false,
	     "bFilter": true,
	     "bJQueryUI": true,
	     "bAutoWidth": true
	     } );

     } );
</script>

</head>
<?php
$password_length = 20;
function make_seed() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

srand(make_seed());

$alfa = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
$token = "";
for($i = 0; $i < $password_length; $i ++) {
  $token .= $alfa[rand(0, strlen($alfa))];
}

function show_run($tokenFile,$owner) {
  echo "<h2>Running Jobs</h2>";
  echo "<table class=\"qstat\" id=\"qstatrunning\" width=\"100%\" border=\"1\">
	  <thead>
		  <tr>
		  <th>JobID</th>
		  <th>Owner</th>
		  <th>Priority</th>
		  <th>Name</th>
		  <th>State</th>
		  <th>Queue </th>
		  <th>Start Time</th>
		  <th>PE</th>
		  <th>Slots</th>
		  </tr>
          </thead>
          <tbody>
";
  $qstat = simplexml_load_file($tokenFile);
  foreach ($qstat->xpath('//job_list') as $job_list) {
  $pe=$job_list->requested_pe['name'];
  echo "          <tr>
		  <td><a href=qstat_job.php?jobid=$job_list->JB_job_number&owner=$owner>$job_list->JB_job_number</a></td>
		  <td><a href=qstat_user.php?owner=$job_list->JB_owner>$job_list->JB_owner</a></td>
		  <td>$job_list->JAT_prio</td>
		  <td>$job_list->JB_name</td>
		  <td>$job_list->state</td>
		  <td><a href=qstat_user.php?queue=$job_list->queue_name&owner=$owner>$job_list->queue_name</a></td>
		  <td>$job_list->JAT_start_time</td>
		  <td><a href=\"pe.php#$pe\">$pe</a></td>
		  <td>$job_list->slots</td>
		  </tr>";
  }
  //  exec("rm $tokenFile");
  echo "</tbody></table><br><br>";

}

function show_pend($tokenFile,$owner) {

  echo "<h2>Pending Jobs</h2>";
  echo "<table class=\"qstat\" id=\"qstatpending\" align=center width=95%xml border=\"1\" cellpadding=\"0\" cellspacing=\"0\">
	  <thead>
		  <tr>
		  <th>JobID</th>
		  <th>Owner</th>
		  <th>Priority</th>
		  <th>Name</th>
		  <th>State</th>
		  <th>Queue </th>
		  <th>Submission Time</th>
		  <th>PE</th>
		  <th>Slots</th>
		  </tr>
            </thead><tbody>";
  
  $qstat = simplexml_load_file($tokenFile);
  foreach ($qstat->xpath('//job_list') as $job_list) {
  $pe=$job_list->requested_pe['name'];
  echo "          <tr>
		  <td><a href=qstat_job.php?jobid=$job_list->JB_job_number&owner=$owner>$job_list->JB_job_number</a></td>
		  <td><a href=qstat_user.php?owner=$job_list->JB_owner>$job_list->JB_owner</a></td>
		  <td>$job_list->JAT_prio</td>
		  <td>$job_list->JB_name</td>
		  <td>$job_list->state</td>
		  <td><a href=qstat_user.php?queue=$job_list->queue_name&owner=$owner>$job_list->queue_name</a></td>
		  <td>$job_list->JB_submission_time</td>
		  <td><a href=\"pe.php#$pe\">$pe</a></td>
		  <td>$job_list->slots</td>
		  </tr>";
  }
  exec("rm $tokenFile");
  echo "</tbody></table><br>";

}

?>
<body>
<body>
<table align=center width=95% border="1" cellpadding="0" cellspacing="0"><tbody>
<tr><td><h1>PHPQstat</h1></td></tr>
  <tr><td CLASS=\"bottom\" align=center>
<?php displayNav( $_GET['owner'] ); ?>
</td>
</tr>


<?php
$owner  = $_GET['owner'];
$jobstat  = $_GET['jobstat'];
$queue  = $_GET['queue'];

echo "<tr><td><br>";

if($queue){$queueflag="-q $queue";}else{$queueflag="";}

if($jobstat){$jobstatflag="-s $jobstat";}else{$jobstatflag="";}

if($owner == "" ){$owner="all";}else{$jobstatflag="";}

switch ($jobstat) {
    case "r":
        $jobstatflag="-s r";
        $out = exec("./gexml -u $owner $jobstatflag $queueflag -o /tmp/$token.xml");   
        show_run("/tmp/$token.xml",$owner);
        break;
    case "p":
        $jobstatflag="-s p";
        $out = exec("./gexml -u $owner $jobstatflag $queueflag -o /tmp/$token.xml");
        show_pend("/tmp/$token.xml",$owner);
        break;
    default:
        $jobstatflag="-s r";
        $out = exec("./gexml -u $owner $jobstatflag $queueflag -o /tmp/$token.xml");
        show_run("/tmp/$token.xml",$owner);
        $jobstatflag="-s p";
        $out = exec("./gexml -u $owner $jobstatflag $queueflag -o /tmp/$token.xml");
        show_pend("/tmp/$token.xml",$owner);
        break;
}

?>
	  

      </td>
    </tr>
<?php
include("bottom.php");
?>
  </tbody>
</table>



</body>
</html>

