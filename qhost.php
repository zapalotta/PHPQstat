<?php require_once( 'navigation.php' ); ?>

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
	     "bAutoWidth": true,
	     "aoColumnDefs": [
			      {
				  "aTargets": [ 3 ],
				    "fnCreatedCell": function(nTd, sData, oData, iRow, iCol)
				      {
					if ( oData[3] == '-' ) {
					  $(nTd).parent().addClass( 'down' );
					}
				      },
			      },
			      ],
	     } );

     } );
</script>

</head>
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


	<table class="qstat" align=center width=95% border="1" cellpadding="0" cellspacing="0">
        <thead>
		<tr CLASS="header">
		<th>Hostname</th>
                <th>Architecture</th>
                <th>NCPU</th>
                <th>Load avg</th>
                <th>mem_total</th>
                <th>mem_used</th>
                <th>swap_total</th>
                <th>swap_used</th>
                <th>Queues</th>
                </tr>
        </thead>
        <tbody>
<?php
$password_length = 20;

function make_seed() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

function rstrstr($haystack,$needle)
{
  return substr($haystack, 0,strpos($haystack, $needle));
}
srand(make_seed());

$alfa = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
$token = "";
for($i = 0; $i < $password_length; $i ++) {
  $token .= $alfa[rand(0, strlen($alfa))];
}

// array to store status of all queue instances (queue@host)
$queueinstances = array();

$out = exec("./qquinst /tmp/$token.queueinstances");

foreach ( file ( "/tmp/$token.queueinstances" ) as $qi ) {
  //  $hostarr = explode ( "@", $host );
  // Bug in qhost: sometimes the fqdn gets truncated...
  //  $queuehosts[ rstrstr( trim( $hostarr[1] ), '.ethz' ) . '.ethz.ch' ][] = trim ( $hostarr[0] );

  if ( strstr($qi, '@') ) {
    $d = explode ( " ", $qi ); 
    $queueinstances[ $d[0] ] = array ( "name"      => trim ( $d[0] ),
				       "qtype"     => trim ( $d[1] ),
				       "rut"       => trim ( $d[2] ),
				       "lavg"      => trim ( $d[3] ),
				       "arch"      => trim ( $d[4] ),
				       "states"    => trim ( $d[5] )
				       );
  }
    
}
//echo "<pre>";
//print_r ( $queueinstances );
//echo "</pre>";

// array to store the queue hosts
$queuehosts = array();

// Get a list of all hosts with associated queues
$out = exec("./qqueuehosts /tmp/$token.hostlist");
foreach ( file ( "/tmp/$token.hostlist" ) as $host ) {
  $hostarr = explode ( "@", $host );
  // Bug in qhost: sometimes the fqdn gets truncated...
  $queuehosts[ rstrstr( trim( $hostarr[1] ), '.ethz' ) . '.ethz.ch' ][] = trim ( $hostarr[0] );
}


$out = exec("./qhostout /tmp/$token.xml");

//printf("System Output: $out\n"); 
$qhost = simplexml_load_file("/tmp/$token.xml");
$i=0;
foreach ($qhost->host as $host) {
	echo "<tr>";
	$hostname=$host['name'];
	echo "          <td>$hostname</td>";
	foreach ($qhost->host[$i] as $hostvalue) {
		echo "          <td>$hostvalue</td>";
	}
	echo "<td>";
	if ( count ( $queuehosts[ trim ( $hostname ) ] ) != 0 ) {
	  foreach ( $queuehosts[ trim ( $hostname ) ] as $queue ) {
	    $states = trim ( $queueinstances[ "$queue@" . trim ( $hostname ) ][ 'states' ] );
	    if ( $states != '' ) {
	      echo $queue . " [$states], ";
	    }
	    else {
	      echo $queue . ", ";
	    }
	  }
	}
	else {
	  echo "&nbsp;";
	}
	echo "</td>";
	echo "</tr>";
	$i++;
}


exec("rm /tmp/$token.xml");
?>

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

