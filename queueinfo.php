<?php
require_once( 'navigation.php' );
?>

<html>

<head>
  <title>PHPQstat</title>
  <meta name="AUTHOR" content="Dirk Doerflinger ">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="refresh" content="30"/>
  <meta name="KEYWORDS" content="gridengine sge sun hpc supercomputing batch queue linux xml qstat qhost jordi blasco solnu">
  <link rel="stylesheet" href="phpqstat.css" type="text/css" /> 
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" type="text/css" />

<script type="text/javascript" language="javascript" src="/media/js/jquery.js"></script> 
<script type="text/javascript" language="javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script> 
   <script type="text/javascript" language="javascript" src="/media/js/jquery.dataTables.js"></script>
 
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
<br/>
    <p>For more informations see: <a target="_blank" href="http://manpages.ubuntu.com/manpages/trusty/man1/sge_intro.html">http://manpages.ubuntu.com/manpages/trusty/man1/sge_intro.1.html</a></p>
<br/>
	<table class="qstat" align=center width=95% border="1" cellpadding="0" cellspacing="0">
        <thead>
		<tr CLASS="header">
		<td>Parallel environment</td>
                <td>Settings</td>
                <td>Hosts</td>
                </tr>
        </thead>
        <tbody>
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

// array to store the queue infos
$queues = array();
// array to store the queue hosts
$queuehosts = array();
// Index of our array
$idx = 0;
// Helper string
$dummy = "";

// get a file with all information about all PEs
// Note: PEs are separated by a line like === 
$out = exec("./qqeuelistout /tmp/$token.quinfo");

// Get a list of all hosts with associated queues
$out = exec("./qqueuehosts /tmp/$token.hostlist");
foreach ( file ( "/tmp/$token.hostlist" ) as $host ) {
  $hostarr = explode ( "@", $host );
  $queuehosts[ trim( $hostarr[0] ) ][] = trim ( $hostarr[1] );
}

// First create an array of PEs from our file
foreach ( file ( "/tmp/$token.quinfo" ) as $quinfo ) {
  if ( trim( $quinfo ) == '===' ) {
    // Found a separator, increase our index for a new PE, omit the line
    $idx++;
    continue;
  }
  if ( substr( trim( $quinfo ), -1) == '\\' ) {
    // line ended with a \ which means we need the following line, too.
    $dummy .= $quinfo;
  }
  else {
    // Line ended normally, just add to the array
    $dummy .= $quinfo;
    $queues[ $idx ][] = $dummy;
    $dummy = '';
  }
}
// Iterate over the PEs and write them to a table of DLs
foreach ( $queues as $queue ) {
  echo "<tr><td class=\"pe\">";
  // Get the PE name and use it as an anchor (linked from job status page)
  $quname = trim ( str_replace ( "qname", '', $queue[0] ) );
  echo "<a name=\"$quname\">$quname</a>";
  echo "</td><td><dl>";
  // Iterate over all settings and put them in a DL
  foreach ( $queue as $line ) {
    // Get the name of the config item (e.g. pe_name) and use it in the DT
    $l_arr = explode ( " ", $line );
    echo "<dt>" . $l_arr[0] . "</dt>";
    // Remove the config item name and add a <br> if there is a \
    echo "<dd>" . str_replace ( '\\', '\\<br />', trim ( str_replace ( $l_arr[0], '', $line ) ) ) . "</dd>";
  }
  echo "</dl></td><td class=\"pe\">";
  foreach ( $queuehosts[ $quname ] as $host ) {
    echo $host . "<br />";
  }
  echo "</td></tr>";
}
// Remove the temporary file


exec("rm /tmp/$token.quinfo");


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

