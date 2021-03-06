<?php 
require_once( 'navigation.php' );
?>
<html>

<head>
  <title>PHPQstat</title>
  <meta name="AUTHOR" content="Dirk Doerflinger ">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
    <p>For more informations see: <a target="_blank" href="http://manpages.ubuntu.com/manpages/trusty/man5/sge_pe.5.html">http://manpages.ubuntu.com/manpages/trusty/man5/sge_pe.5.html</a></p>
<br/>
	<table class="qstat" align=center width=95% border="1" cellpadding="0" cellspacing="0">
        <thead>
		<tr CLASS="header">
		<td>Parallel environment</td>
                <td>Settings</td>
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

// get a file with all information about all PEs
// Note: PEs are separated by a line like === 
$out = exec("./qpelistout /tmp/$token.peinfo");

// array to store the pe infos
$pes = array();
// Index of our array
$idx = 0;
// Helper string
$dummy = "";

// First create an array of PEs from our file
foreach ( file ( "/tmp/$token.peinfo" ) as $peinfo ) {
  if ( trim( $peinfo ) == '===' ) {
    // Found a separator, increade our index for a new PE, omit the line
    $idx++;
    continue;
  }
  if ( substr( trim( $peinfo ), -1) == '\\' ) {
    // line ended with a \ which means we need the following line, too.
    $dummy .= $peinfo;
  }
  else {
    // Line ended normally, just add to the array
    $dummy .= $peinfo;
    $pes[ $idx ][] = $dummy;
    $dummy = '';
  }
}

// Iterate over the PEs and write them to a table of DLs
foreach ( $pes as $pe ) {
  echo "<tr><td class=\"pe\">";
  // Get the PE name and use it as an anchor (linked from job status page)
  $pename = trim ( str_replace ( "pe_name", '', $pe[0] ) );
  echo "<a name=\"$pename\">$pename</a>";
  echo "</td><td><dl>";
  // Iterate over all settings and put them in a DL
  foreach ( $pe as $line ) {
    // Get the name of the config item (e.g. pe_name) and use it in the DT
    $l_arr = explode ( " ", $line );
    echo "<dt>" . $l_arr[0] . "</dt>";
    // Remove the config item name and add a <br> if there is a \
    echo "<dd>" . str_replace ( '\\', '\\<br />', trim ( str_replace ( $l_arr[0], '', $line ) ) ) . "</dd>";
  }
  echo "</dl></td></tr>";
}
// Remove the temporary file
exec("rm /tmp/$token.peinfo");


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

