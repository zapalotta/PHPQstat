<?php
  /**
   * Class for handling accounting data loaded from a MySQL database.
   *
   * (c) 2014 Dirk Doerflinger (dirk AT doerflinger.org
   */



  /**
   * Import database helper classe
   */
require_once 'lib/meekro.php';


  /**
   * Class for handling the data
   *
   * Basic usage: An object gets initialized andd is filled with a specific set of jobs from the DB. Loading the jobs is done by different methods which do some kind of filtering.
   */
class Accounting {

  /**
   * This Skeleton holds the order of fields in the accounting file and therefore fieldnames in the database. Change with care! Names are used from the manpage of qaccounting.
   *
   * Note: group has been changed to primary_group because MySQL doesn't like a field called 'group'
   */
  var $skeleton = array (
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
			"ru_nvcsw",        // 9855:The number of times a context switch resulted due to a process voluntarily giving up the processor before its time slice was completed 
			"ru_nivcsw",       // 95359:The number of times a context switch resulted due to a higher priority process becoming runnable or because the current process exceeded its time slice.
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

  //  var $jobs = array();

  /**
   * List of IDs of Jobs currently stored in the object
   */
  var $job_ids = array();


  //  var $owners = array();

  //  var $groups = array();

  //  var $queues = array();

  /*
   * Constructor of the class, initialize DB credentials
   */

  public function __construct() {
  // FIXME: move this to an external file for ignoring by git

    DB::$host = '';
    DB::$user = '';
    DB::$password = '';
    DB::$dbName = '';
  }

  /*
   * Get the number of jobs currently stored in the object
   *
   * @return integer Number of jobs
   */
  function getNumberOfJobs() {
    return sizeof( $this->jobs );
  }

  /*
   * Get a list of all job IDs currently stored in the object
   *
   * @return string[] List of IDs
   */
  function getAllJobIDs( $startDate = "" ) {
    if ( $startDate != "" ) 
      $results = DB::query("SELECT DISTINCT job_number FROM accountingdata WHERE submission_time > '$startDate';");
    else
      $results = DB::query("SELECT DISTINCT job_number FROM accountingdata;");
    foreach ($results as $row) {
      $this->job_ids[] = $row['job_number'];
    }
  }

  /*
   * Fill the object with all jobs. 
   *
   * ATTN: 
   */
  function loadAllJobs ( $startDate = "" ) {
    if ( $startDate != "" ) 
      $results = DB::query("SELECT * FROM accountingdata WHERE submission_time > '$startDate';");
    else
      $results = DB::query("SELECT * FROM accountingdata;");
    foreach ($results as $row) {
      $this->jobs[] = $row;
    }
  }

  function getAllOwners( $startDate = "" ) {
    if ( $startDate != "" ) 
      $results = DB::query("SELECT DISTINCT owner FROM accountingdata WHERE submission_time > '$startDate';");
    else
      $results = DB::query("SELECT DISTINCT owner FROM accountingdata;");
    foreach ($results as $row) {
      $this->owners[] = $row['owner'];
    }
  }

  function getNumberOfJobsByOwners( $startDate = "" ) {
    $ret = array();
    //    if ( $startDate != "" ) 
    //      $results = DB::query("SELECT DISTINCT job_number FROM accountingdata WHERE submission_time > '$startDate';");
    //    else
    //      $results = DB::query("SELECT DISTINCT job_number FROM accountingdata;");

    $results = DB::query("SELECT owner, count(job_number) as no FROM accountingdata GROUP BY owner;");
    foreach ($results as $row) {
      $ret[] = array( $row['owner'], $row['no'] );
    }
    return $ret;
  }

  function getAllGroups() {
    $results = DB::query("SELECT DISTINCT primary_group FROM accountingdata ORDER BY primary_group ASC;");
    foreach ($results as $row) {
      $this->groups[] = $row['primary_group'];
    }
  }

  function getAllQueues() {
    $results = DB::query("SELECT DISTINCT qname FROM accountingdata ORDER BY qname ASC;");
    foreach ($results as $row) {
      if ( strlen( trim ( $row['qname'] ) ) > 0 )
	$this->queues[] = $row['qname'];
    }    
  }

  function getAllJobsByJobID ( $job_id ) {
    $results = DB::query("SELECT * FROM accountingdata WHERE job_number='$job_id';");
    foreach ($results as $row) {
      $this->jobs[] = $row;
    }
  }
  
  function getAllJobsByOwner( $owner , $startDate = "" ) {
    if ( $startDate != "" ) 
      $results = DB::query("SELECT * FROM accountingdata WHERE owner='$owner' AND submission_time > '$startDate';");
    else
      $results = DB::query("SELECT * FROM accountingdata WHERE owner='$owner';");

    //    $results = DB::query("SELECT * FROM accountingdata WHERE owner='$owner';");
    foreach ($results as $row) {
      $this->jobs[] = $row;
    }
  }

  function getAllJobsByGroup( $group , $startDate = "" ) {
    if ( $startDate != "" ) 
      $results = DB::query("SELECT * FROM accountingdata WHERE primary_group='$group' AND submission_time > '$startDate';");
    else
      $results = DB::query("SELECT * FROM accountingdata WHERE primary_group='$group';");
    foreach ($results as $row) {
      $this->jobs[] = $row;
    }
  }

  function loadAllJobsFiltered( $startDate = "", $groupname = "", $username= "" ) {
    $groupname == "" ? $group = "%" : $group = $groupname;
    $username == "" ? $owner = "%" : $owner = $username;
    if ( $startDate != "" ) 
      $results = DB::query("SELECT * FROM accountingdata WHERE primary_group LIKE '$group' AND owner LIKE '$owner' AND submission_time > '$startDate';");
    else
      $results = DB::query("SELECT * FROM accountingdata WHERE primary_group LIKE '$group' AND owner LIKE '$owner';");
    foreach ($results as $row) {
      $this->jobs[] = $row;
    }
  }

  function getAjaxStatistics( $mode,  $groupname = "", $queuename = "", $startdate = "", $lmt = "" ) {
    $groupname == "" ? $group = "%" : $group = $groupname;
    $queuename == "" ? $queue = "%" : $queue = $queuename;
    $lmt == "" ? $limit = "10" : $limit = $lmt;
    if ( $startdate == "" ) $startdate = "1990-01-01 00:00";

    switch( $mode ) {
    case "execjobs":
      $diff = date_diff( date_create( $startdate ), date_create( "now" ) );
      if ( $diff->y > 0 ) {
	// More than one year selected, show per year
	$query = "SELECT Year(`start_time`) as 'lbl',   Count(*) As cnt FROM accountingdata  where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' GROUP BY Year(`start_time`);";
      }
      else {
	// Show monthly
	$query = "SELECT concat( Year(`start_time`), '-',  LPAD( Month(`start_time`), 2, '0' ) ) as 'lbl',   Count(*) As cnt FROM accountingdata  where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' GROUP BY Year(`start_time`), Month(`start_time`);";
      }
      break;
    case "slotsjob":
      $query="select  concat(8 * round(slots / 8) + 1, '-', 8 * round(slots / 8) + 8) as `lbl`,   count(*) as `cnt` from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' group by lbl order by slots;";
      break;
    case "users":
      $query = "select owner as lbl, count(owner) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' group by owner order by cnt desc LIMIT $limit;";
      break;
    case "nodes":
      $query = "select hostname as lbl, count(hostname) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' group by hostname order by cnt desc LIMIT $limit;";
      break;
    case "mem":
      $query = "select owner as lbl, sum(mem/1024) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' group by owner order by cnt desc LIMIT $limit;";
      break;
    case "memavg":
      $query = "select owner as lbl, avg(mem/1024) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' group by owner order by cnt desc LIMIT $limit;";
      break;
    case "ios":
      $query = "select owner as lbl, sum(io) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' group by owner order by cnt desc LIMIT $limit;";
      break;
    case "pes":
      $query = "select granted_pe as lbl, count(granted_pe) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' AND granted_pe != 'NONE' group by granted_pe order by granted_pe desc LIMIT $limit;";
      break;
    case "exectime":
      $query = "select owner as lbl, sum(ru_wallclock/3600) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' group by owner order by cnt desc LIMIT $limit;";
      break;
    case "exectimeavg":
      $query = "select owner as lbl, avg(ru_wallclock/3600) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue' group by owner order by cnt desc LIMIT $limit;";
      break;
    case "group-usage":
      $query = "select primary_group as lbl, count(primary_group) as cnt from accountingdata where submission_time > '$startdate' AND qname LIKE '$queue' group by primary_group order by cnt desc";
      break;
    case "failed":
      $query = "select failed as lbl, count(*) as cnt from accountingdata where submission_time > '$startdate' AND qname LIKE '$queue' group by failed order by cnt desc";
      break;
    case "clusterstats":
      // get sum of cputime
      foreach (DB::query( "select ROUND( sum(cpu)/3600/24/365, 2 ) as cpusum from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue'" )  as $row) {
	$totalcputime = $row['cpusum'];	
      }
      // get longest running job
      foreach (DB::query( "select SEC_TO_TIME( max(ru_wallclock) ) as maxrun, job_number from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue'" )  as $row) {
	$longestrunningjob = $row['maxrun'] . " (Job ID: " . $row['job_number'] . ")";	
      }
      // get number of jobs
      foreach (DB::query( "select count(*) as cnt from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue'" )  as $row) {
	$totalnumberofjobs = $row['cnt'];	
      }
      // get average job runtime
      foreach (DB::query( "select SEC_TO_TIME( ROUND( avg(ru_wallclock) ) ) as avgrun from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue'" )  as $row) {
	$averagejobruntime = $row['avgrun'];	
      }
      // get total time in usermode
      foreach (DB::query( "select ROUND( sum(ru_time)/3600/24/365, 2 ) as ru_timesum from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue'" )  as $row) {
	$totalusermodetime = $row['ru_timesum'];	
      }
      // get total time in system mode
      foreach (DB::query( "select ROUND( sum(ru_stime)/3600/24/365, 2 ) as ru_stimesum from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue'" )  as $row) {
	$totalsystemmodetime = $row['ru_stimesum'];	
      }
      // get total ios
      foreach (DB::query( "select ROUND( sum(io) ) as iosum from accountingdata where submission_time > '$startdate' AND primary_group LIKE '$group' AND qname LIKE '$queue'" )  as $row) {
	$totalios = $row['iosum'];	
      }


      $query = "select 1;";

      break;
    }
      

    $data = array ();
    $labels = array ();

    if ( $mode == "clusterstats" ) {
      return array( 
		   "totalcputime" => $totalcputime,
		   "longestrunningjob" => $longestrunningjob,
		   "totalnumberofjobs" => $totalnumberofjobs,
		   "averagejobruntime" => $averagejobruntime,
		   "totalusermodetime" => $totalusermodetime,
		   "totalsystemmodetime" => $totalsystemmodetime,
		   "totalios" => $totalios
		    );
    }


    $results = DB::query( $query );
    foreach ($results as $row) {
      $labels[] = $row['lbl'];
      $data[] = $row['cnt'] * 1;
    }

    $errorcode = 0;
    
    if( sizeof( $data ) == 0 ) {
      $errorcode = 1;
    }

    return array( 'data' => $data, 
		  'labels' => $labels,
		  'errorcode' => $errorcode
		  );
  }


  function getAllJobIDsByOwner( $owner ) {
    foreach ( $this->jobs as $value ) {
      if ( $value['owner'] == $owner ) {
	$this->job_ids[] = $value[ 'job_number' ];
      }
    }
  }

  function parseAccountingFile() {}

  function getAjaxJobs( $jobarray = NULL ) {
    $jobs = array();
    $runtimes = array();
    if ( $jobarray ) {
      // work on defined list of jobs
      $workingset = &$jobarray;
    }
    else {
      // work on all jobs
      $workingset = &$this->jobs;
    }
    //    print_r ( $workingset );
    foreach ( $workingset as $key => $value ) {
      $jobs[] = array(
		      
		      $value[ 'qname' ],
		      $value[ 'hostname' ],
		      $value[ 'primary_group' ],
		      $value[ 'owner' ],
		      $value[ 'job_name' ],
		      $value[ 'job_number' ],
		      $value[ 'submission_time' ],
		      $value[ 'start_time' ],
		      $value[ 'end_time' ],
		      //round ( $value[ 'ru_wallclock' ]/60 ),
		      $value[ 'ru_wallclock' ],
		      $value[ 'ru_time' ],
		      $value[ 'ru_stime' ],
		      $value[ 'ru_inblock' ],
		      $value[ 'ru_outblock' ],
		      $value[ 'granted_pe' ],
		      $value[ 'slots' ],
		      $value[ 'cpu' ],
		      $value[ 'mem' ],
		      $value[ 'io' ],
		      $value[ 'maxvmem' ],



		      );
    }    
    return ( array( 'aaData' => $jobs ) );
  }



  function getRuntimes ( $jobarray = NULL, $sorted = FALSE ) {
    $runtimes = array();
    if ( $jobarray ) {
      // work on defined list of jobs
      $workingset = &$jobarray;
    }
    else {
      // work on all jobs
      $workingset = &$this->jobs;
    }
    foreach ( $workingset as $key => $value ) {
      $runtimes[ $key ] = $value[ 'ru_wallclock' ];
    }
    if ( $sorted ) 
      asort ( $runtimes );
    return $runtimes;
  }


}


?>