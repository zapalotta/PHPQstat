<?php
  /**
   * Import accounting file into database
   *
   * There may be some lines which are dropped due to being malformatted, but this is not science, we don't need to be this accurate ...
   */

  // Import neat mysql library


$basepath = realpath(dirname( dirname(__FILE__)) );

require_once $basepath.'/lib/meekro.php';

// IMPORTANT: the structure of this array reflects the order of the accounting file. No checks done!
$skeleton = array (
		       "qname",           // sc02.q
		       "hostname",        // bs-dbl02.ethz.ch
		       "primary_group",           // bsse-itsc ATTN: changed for database!
		       "owner",           // dodirk
		       "job_name",        // Mandelbrot
		       "job_number",      // 922872
		       "account",         // sge
		       "priority",        // 0:
		       "submission_time", // 1395133221:
		       "start_time",      // 1395133222:
		       "end_time",        // 1395133353:
		       "failed",          // 0
		       "exit_status",     // 0:
		       "ru_wallclock",    // 131:
		       "ru_time",         // 2608.059514:Total amount of time spent executing in usermode
		       "ru_stime",        // 215.163290:Total amount of time spent executing in kernel mode
		       "ru_maxrss",       // 5808.000000:Maximum resident set size (kb)
		       "ru_ixrss",        // 0:This field is currently unused on Linux.
		       "ru_ismrss",       // 0:
		       "ru_idrss",        // 0:This field is currently unused on Linux.
		       "ru_isrss",        // 0:This field is currently unused on Linux.
		       "ru_minflt",       // 84017:Number of page faults without any IO activity
		       "ru_majflt",       // 408:Number of page faults with IO activity
		       "ru_nswap",        // 0:This field is currently unused on Linux.
		       "ru_inblock",      // 6568.000000:The number of times the filesystem had to perform input.
		       "ru_outblock",     // 58760:The number of times the filesystem had to perform output.
		       "ru_msgsnd",       // 0:This field is currently unused on Linux.
		       "ru_msgrcv",       // 0:This field is currently unused on Linux.
		       "ru_nsignals",     // 0:This field is currently unused on Linux.
		       "ru_nvcsw",        // 9855:The number of times a context switch resulted due to a process vol\untarily giving up the processor before its time slice was completed
		       "ru_nivcsw",       // 95359:The number of times a context switch resulted due to a higher pri\ority process becoming runnable or because the current process exceeded its time slice.
		       "project",         // NONE:
		       "department",      // defaultdepartment:
		       "granted_pe",      // openmpi624:
		       "slots",           // 624:
		       "task_number",     // 0:
		       "cpu",             // 2823.222804:
		       "mem",             // 424.117438:
		       "io",              // 0.755758:
		       "category",        // -U bsse-itsc, -q sc02.q,
		       "iow",             // 0.000000:
		       "pe_taskid",       // 1.bs-dbl02:
		       "maxvmem",         // 7858765824.000000:
		       "arid",            // 0:
		       "ar_sub_time"      // 0
		       );

// Connect to database

// FIXME: external config!
DB::$host = '';
DB::$user = '';
DB::$password = '';
DB::$dbName = '';

// Empty table before importing
DB::query( 'TRUNCATE TABLE accountingdata' );

// Small helper counter for debugging
$ctr = 0;
// Read from stdin (wait for file), this is way faster and RAM saving than opening the file in php
while(!feof(STDIN)){
  $line = fgets(STDIN);
  // Ignore comments (header!)
  if ( substr( $line, 0, 1 ) == '#' ) 
    continue;
  // Ignore empty lines
  if ( strlen( trim( $line ) ) > 0  ) {
    // Create array from 
    $import = @array_combine( $skeleton, explode( ':', $line ) );
    // Fix timestamps
    $import['submission_time'] = date( "Y-m-d H:i:s", $import['submission_time'] );
    $import['start_time'] = date( "Y-m-d H:i:s", $import['start_time'] );
    $import['end_time'] = date( "Y-m-d H:i:s", $import['end_time'] );
    // Import array into DB
    @DB::insert('accountingdata', $import );
    $ctr++;
  }
    
}

echo "Imported $ctr lines!\n";

?>
