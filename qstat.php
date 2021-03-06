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
<script type="text/javascript">
  function changeIt(view){document.getElementById('rta').src= view;}
</script>
<script type="text/javascript" language="javascript" src="/media/js/jquery.js"></script> 
<script type="text/javascript" language="javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script> 
   <script type="text/javascript" language="javascript" src="/media/js/jquery.dataTables.js"></script>

<script>
   $(document).ready(function() {
       //$('td.val():contains(0)').closest('tr.alt').css('background-color', '#cd0000');
       //$('td.status[value=Zero]').closest('tr').css('background-color', 'red');
       
       //       if ( $( 'table#queues tbody tr td:last-child' ).html() == 0 ) {
       //	 $( 'table#queues tbody tr td:last-child' ).css( 'background-color', 'red' );
       //       }
       $( 'table#queues tbody tr td:last-child' ).each( function () {
	   if ( ( $(this).html() != '0' ) && ( $(this).html().length < 4 ) ){
	     $(this).closest('tr').css( 'background-color', '#FFBBBB' );
	   }

	 });
       

     });


</script>

</head>
<body>
<body>
<table align=center width=95% border="1" cellpadding="0" cellspacing="0"><tbody>
<tr><td><h1>PHPQstat</h1></td></tr>
  <tr><td CLASS=\"bottom\" align=center>
<?php displayNav( $_GET['owner'] ); ?>
</td>
</tr>
    <tr>
      <td>
<br>

	<table class="list" id='queues' align=center width=95%>
        <tbody>
		<tr CLASS="header">
		<td>Queue</td>
                <td>Load</td>
                <td>Used</td>
                <td>Resv</td>
                <td>Available</td>
                <td>Total</td>
                <td>Temp. disabled</td>
                <td>Manual intervention</td>
                </tr>

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

$out = exec("./gexml -u all -R -o /tmp/$token.xml");

//printf("System Output: $out\n"); 
$qstat = simplexml_load_file("/tmp/$token.xml");

//$qstat = simplexml_load_file("/home/xadmin/phpqstat/qstat_user.xml");

foreach ($qstat->xpath('//cluster_queue_summary') as $cluster_queue_summary) {
echo "                <tr>
                <td><a href=qstat_user.php?owner=$owner&queue=$cluster_queue_summary->name>$cluster_queue_summary->name</a> (<a href=\"queueinfo.php#$cluster_queue_summary->name\">info</a>)</td>
                <td>$cluster_queue_summary->load</td>
                <td>$cluster_queue_summary->used</td>
                <td>$cluster_queue_summary->resv</td>
                <td>$cluster_queue_summary->available</td>
                <td>$cluster_queue_summary->total</td>
                <td>$cluster_queue_summary->temp_disabled</td>
                <td>$cluster_queue_summary->manual_intervention</td>
                </tr>";
}
exec("rm /tmp/$token.xml");

echo "                </tbody>
	</table>

<br>
	<table id='jobs' align=center width=95%>
        <tbody>
		<tr CLASS='header'>
		<td>Jobs status</td>
                <td>Total</td>
                <td>Slots</td>
                </tr>

";

$out2 = exec("./gexml -u all -o /tmp/$token.xml");
$jobs = simplexml_load_file("/tmp/$token.xml");
$nrun=0;
$srun=0;
$npen=0;
$spen=0;
$nzom=0;
$szom=0;
foreach ($jobs->xpath('//job_list') as $job_list) {
$jobstatus=$job_list['state'];

	if ($jobstatus == "running"){
		$nrun++;
		$srun=$srun+$job_list->slots;
	}
	elseif ($jobstatus == "pending"){
		$npen++;
		$spen=$spen+$job_list->slots;
	}
	elseif ($jobstatus == "zombie"){
		$nzom++;
		$szom=$szom+$job_list->slots;
	}
}
echo "          <tr>
                <td><a href=qstat_user.php?jobstat=r&owner=$owner>running</a></td>
                <td>$nrun</td>
                <td>$srun</td>
                </tr>
                <tr>
                <td><a href=qstat_user.php?jobstat=p&owner=$owner>pending</a></td>
                <td>$npen</td>
                <td>$spen</td>
                </tr>
                <tr>
                <td><a href=qstat_user.php?jobstat=z&owner=$owner>zombie</a></td>
                <td>$nzom</td>
                <td>$szom</td>
                </tr>
";

exec("rm /tmp/$token.xml");
?>

	  </tbody>
	</table>
<br>
	<table align=center border="1" cellpadding="0" cellspacing="0">
        <tbody>
		<tr class="header"><td align="center">Real-time Accounting : 
		<a href="#" onclick="changeIt('img/hour.png')">hour</a> - 
		<a href="#" onclick="changeIt('img/day.png')">day</a> - 
		<a href="#" onclick="changeIt('img/week.png')">week</a> - 
		<a href="#" onclick="changeIt('img/month.png')">month</a> - 
		<a href="#" onclick="changeIt('img/year.png')">year</a></td></tr>
		<tr><td>
		<img src="img/hour.png" id='rta' border='0'></td></tr>
	</tbody>
	</table>
<br>
      </td>
    </tr>
<?php
include("bottom.php");
?>
  </tbody>
</table>



</body>
</html>

