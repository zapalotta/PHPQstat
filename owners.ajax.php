<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);

function startsWith($haystack, $needle) {
  return $needle === "" || strpos($haystack, $needle) === 0;
}


require_once( "include/accounting.inc.php" );

//print_r ( $_GET );
$acct = new Accounting( );

$acct->getAllOwners();

if ( !empty( $_GET[ "term" ] ) ) {
  $username = htmlspecialchars( $_GET['term'] ) ;
  function filter(&$item) {
    global $username;

    return startsWith ( $item, $username );
  
  }

  $matches = array_filter ($acct->owners, 'filter' );
}
else {
  $matches = $acct->owners;
}

header('Content-Type: application/json; charset=utf-8', true,200);

echo json_encode( $matches );

//echo ( json_encode( array_map( function($t){ return is_string($t) ? utf8_encode($t) : $t; }, $matches ) ) );


//echo ( json_encode ( $acct->getAjaxJobs(), JSON_NUMERIC_CHECK|JSON_FORCE_OBJECT  ) );
?>
