<?php
require_once( 'navigation.php' );
?>

<html>

<head>
  <title>PHPQstat</title>
  <meta name="AUTHOR" content="Jordi Blasco Pallares ">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="KEYWORDS" content="gridengine sge sun hpc supercomputing batch queue linux xml qstat qhost jordi blasco solnu">
  <link rel="stylesheet" href="phpqstat.css" type="text/css" /> 
  <link rel="stylesheet" href="/media/js/jquery-ui/themes/base/jquery-ui.css" type="text/css" />
   <link rel="stylesheet" type="text/css" href="/media/js/jquery.jqplot/jquery.jqplot.css" />

<script type="text/javascript" language="javascript" src="/media/js/jquery-1.11.0.min.js"></script> 
<script type="text/javascript" language="javascript" src="/media/js/jquery-ui/ui/jquery-ui.js"></script> 

   <script type="text/javascript" language="javascript" src="/media/js/jquery.dataTables.js"></script>
   <script type="text/javascript" language="javascript" src="/media/js/Chart.js/Chart.js"></script>
   <script type="text/javascript" language="javascript" src="/media/js/jquery.jqplot/jquery.jqplot.js"></script>
   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.barRenderer.min.js"></script>
   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.highlighter.min.js"></script>
<!--   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.cursor.min.js"></script>-->
   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.pointLabels.min.js"></script>
   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.pieRenderer.min.js"></script>
   <script type="text/javascript" src="/media/js/jquery.jqplot/plugins/jqplot.donutRenderer.min.js"></script>

 <style>
   .ui-autocomplete-loading {
 background: white url('img/ui-anim_basic_16x16.gif') right center no-repeat;
 }
</style>
 
<script>
   var owners = new Array();
   var groups = new Array();
   
   $(document).ready(function() {
       $('#startbtn').attr("disabled", "disabled");
       function enableGetDataBtn() {
	 $('#startbtn').removeAttr("disabled");
	 $('#spinimage').hide();
	 getData();
       }
       setTimeout( enableGetDataBtn, 3000 );

       $.jqplot.config.enablePlugins = true;
       $.jqplot.config.catchErrors = true;
       $.jqplot.config.errorMessage = 'A Plot Error has Occurred';
       $.jqplot.config.errorBorder = '2px solid #aaaaaa';
       $.jqplot.config.errorFontFamily = 'Courier New';
       $.jqplot.config.errorFontSize = '16pt';
       $.getJSON( "owners.ajax.php", function() {
	   console.log( "owners successfully loaded" );
	 })
	 .done(function( data ) {
	     $.each( data, function( i, item ) {
		 owners.push( item );
	       });
	   })
	 .fail(function() {
	     console.log( "error getting owners" );
	   })

   
	 $.getJSON("groups.ajax.php", function() {
	     console.log ( "groups loaded successfully" );
	   }).done ( function( data ) {
	     var options = $("#groupselect");
	     $.each(data, function( i , item ) {
		 options.append($("<option />").val(item).text(item));
	       });
	     });

	 $.getJSON("queues.ajax.php", function() {
	     console.log ( "queues successfully loaded" );
	   }).done ( function( data ) {
	     var options = $("#queueselect");
	     $.each(data, function( i , item ) {
		 options.append($("<option />").val(item).text(item));
	       });
	     });
     


       $.datepicker.regional[ "de" ]
       $( "#startdate" ).datepicker({ 
	 dateFormat: "dd.mm.yy",
	 onSelect: function(dateText) {
	     var today = new Date();
  
	     var dateDiff = today - $( "#startdate" ).datepicker( "getDate" );
	     // Print an alert if startdate is more than 6 months ago
	     if ( dateDiff > 15778463000 ) {
	       $( "#warningtext" ).html( "&nbsp;<b>Attention: Selection may result in a lot of data, choose wisely!</b>" );
	       $( "#warningtext" ).css ( "color", "red" );
	     }
	     else {
	       $( "#warningtext" ).html( "" );

	     }

	   }
 
       });
       // Initially start with 9 days.
       $( "#startdate" ).datepicker( "setDate", "-9" );

       $('#username').autocomplete({
	 source: owners,
	     minLength: 1,
       });

       //       $( "#radio" ).buttonset();

       $( "#startbtn" ).click(function() {
	   getData();	   
	 });

       $('#username').keydown(function(e){
	   if (e.keyCode == 13) {
	     getData();
	   }
	 });

       $( "#statstabs" ).tabs({
	 active:0,   
	     activate: function(event, ui){
	     tabIndex = $( "#statstabs" ).tabs( "option", "active" );
	     var selectedTab = $("#statstabs ul>li a" ).eq( tabIndex ).attr( 'href' );
	     if( selectedTab != "#full-list" ) {
	       // Clear full table to save some browser memory
	       $('#accountingtable').dataTable().fnClearTable();
	     }
	   }

	 });
    } );


/**
 * React on user input, decide by view
 */
var plot;

function getData() {
  var today = new Date();
  var dateDiff = today - $( "#startdate" ).datepicker( "getDate" );
  // Print an alert if startdate is more than 6 months ago
  if ( dateDiff > 15778463000 ) {
    $( "#warningtext" ).html( "&nbsp;<b>Attention: Selection may result in a lot of data, choose wisely!</b>" );
    $( "#warningtext" ).css ( "color", "red" );
  }
  else {
    $( "#warningtext" ).html( "" );
  }
  
  tabIndex = $( "#statstabs" ).tabs( "option", "active" );
  var selectedTab = $("#statstabs ul>li a" ).eq( tabIndex ).attr( 'href' );
  switch ( selectedTab ) {
  case "#full-list":
    showFullList();
    break;
  case "#clusterstats":
    showClusterStats();
    break;
  case "#exec-jobs":
    showGraphTopTen( "execjobs" );
    break;
  case "#slots-job":
    showGraphTopTen( "slotsjob" );
    break;
  case "#top-ten-nodes":
    showGraphTopTen( "nodes" );
    break;
  case "#top-ten-users":
    showGraphTopTen( "users" );
    break;
  case "#top-ten-mem":
    showGraphTopTen( "mem" );
    break;
  case "#top-ten-mem-avg":
    showGraphTopTen( "memavg" );
    break;
  case "#top-ten-pes":
    showGraphTopTen( "pes" );
    break;
  case "#top-ten-ios":
    showGraphTopTen( "ios" );
    break;
  case "#top-ten-exectime":
    showGraphTopTen( "exectime" );
    break;
  case "#top-ten-exectime-avg":
    showGraphTopTen( "exectimeavg" );
    break;
  case "#group-usage":
    showGraphTopTen( "group-usage" );
    break;
  case "#failed":
    showGraphTopTen( "failed" );
    break;
  default: 
    break;
  }
}


function showClusterStats() {
  var today = new Date();
  var dateDiff = new Date() - $( "#startdate" ).datepicker( "getDate" );
  // Print an alert if startdate is more than 6 months ago
  if ( dateDiff > 15778463000 ) {
    $( "#warningtext" ).html( "&nbsp;<b>Attention: Selection may result in a lot of data, choose wisely!</b>" );
    $( "#warningtext" ).css ( "color", "red" );
  }
  else {
    $( "#warningtext" ).html( "" );
  }
  var startDate = $( "#startdate" ).datepicker({ dateFormat: 'yy-mm-dd', regional: "us" }).val();
  var username = $("#username").val();
  var groupname = $('#groupselect').val();
  var queuename = $('#queueselect').val();
  var limit = $('#limitselect').val();
  $('#spinimage').show();
  $.getJSON( "statistics.ajax.php", {
    startdate: startDate,
	username: username,
	groupname: groupname,
	limit: limit,
	queuename: queuename,
	mode: 'clusterstats'
	}).done( function ( result ) {
	    if( result.errorcode == 99 ) {
	      $( "#warningtext" ).html( "You are not authenticated for this section ... leave!" );
	      $( "#warningtext" ).css ( "color", "red" );
	      plot.destroy();
	      return 0;
	    }
	    if( result.errorcode == 1 ) {
	      $( "#warningtext" ).html( "Sorry, no data to be displayed!" );
	      $( "#warningtext" ).css ( "color", "red" );
	      plot.destroy();
	      $('#spinimage').hide();
	      return 0;
	    }
	    $("#totalcputime").html( result.totalcputime );
	    $("#totalnumberofjobs").html( result.totalnumberofjobs );
	    $("#longestrunningjob").html( result.longestrunningjob );
	    $("#averagejobruntime").html( result.averagejobruntime );
	    $("#totalusermodetime").html( result.totalusermodetime );
	    $("#totalsystemmodetime").html( result.totalsystemmodetime );
	    $("#totalios").html( result.totalios );
	    $('#spinimage').hide();
	  });
	  
}


function showGraphTopTen( mode ) {
  var startDate = $( "#startdate" ).datepicker({ dateFormat: 'yy-mm-dd', regional: "us" }).val();
  var username = $("#username").val();
  var groupname = $('#groupselect').val();
  var queuename = $('#queueselect').val();
  var limit = $('#limitselect').val();
  switch ( mode ) {
  case "users":
    div = 'top-ten-users-chart';
    break;
  case "execjobs":
    div = 'exec-jobs-chart';
    break;
  case "slotsjob":
    div = 'slots-job-chart';
    break;
  case "nodes":
    div = 'top-ten-nodes-chart';
    break;
  case "mem":
    div = 'top-ten-mem-chart';
    break;
  case "memavg":
    div = 'top-ten-mem-avg-chart';
    break;
  case "pes":
    div = 'top-ten-pes-chart';
    break;
  case "ios":
    div = 'top-ten-ios-chart';
    break;
  case "exectime":
    div = 'top-ten-exectime-chart';
    break;
  case "exectimeavg":
    div = 'top-ten-exectime-avg-chart';
    break;
  case "failed":
    div = 'failed-chart';
    break;
  case "group-usage":
    div = 'group-usage-chart';
    break;
  }
  $('#spinimage').show();  
  $.getJSON( "statistics.ajax.php", {
    startdate: startDate,
	username: username,
	groupname: groupname,
	limit: limit,
	queuename: queuename,
	mode: mode
	}).done( function ( result ) {
	    if( result.errorcode == 99 ) {
	      $( "#warningtext" ).html( "You are not authenticated for this section ... leave!" );
	      $( "#warningtext" ).css ( "color", "red" );
	      plot.destroy();
	      return 0;
	    }
	    if( result.errorcode == 1 ) {
	      $( "#warningtext" ).html( "Sorry, no data to be displayed!" );
	      $( "#warningtext" ).css ( "color", "red" );
	      $('#spinimage').hide();
	      plot.destroy();
	      return 0;
	    }

      plot = $.jqplot( div,  [result.data], {
	width: 900,
	seriesDefaults:{
	  renderer:$.jqplot.BarRenderer,
	  },
	    axes: {
            // Use a category axis on the x axis and use our custom ticks.
	  xaxis: {
	    renderer: $.jqplot.CategoryAxisRenderer,
                ticks: result.labels,
		tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
		tickOptions: {
	      angle: -30,
		  fontSize: '8pt'
		  },
		},
	      // Pad the y axis just a little so bars can get close to, but
	      // not touch, the grid boundaries.  1.2 is the default padding.
	      yaxis: {
	    pad: 1.05,
		min: 0
		},
	      },
	highlighter: {
	  show: true,
	      sizeAdjust: 7.5
	      },
	    cursor: {
	  show: false
	      }

	} );
      // Refresh, otherwise we will get stacked graphs...
      plot.replot();
      $('#spinimage').hide();
    });
}




function showFullList() {
  var startDate = $( "#startdate" ).datepicker({ dateFormat: 'yy-mm-dd', regional: "us" }).val();
  var username = $("#username").val();
  var groupname = $('#groupselect').val();
  $('#accountingtable').dataTable( {
      "sPaginationType": "two_button",
	"sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
	"iDisplayLength": 50,
	"bJQueryUI": true,
	"bDestroy": true,
	"bProcessing" : true,
	"sAjaxSource": "accounting.ajax.php",
	"fnServerParams": function ( aoData ) {
	aoData.push( { "name": "startdate", "value": startDate + " 00:00:00" } );
	aoData.push( { "name": "username", "value": username } );
	aoData.push( { "name": "groupname", "value": groupname } );
      }
	} );
  
}
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
<div id="spinner">
<img src="/media/images/spinner.gif" id="spinimage" />
</div>
<br/>
Startdate: <input type="text" id="startdate" />&nbsp;

Group:  <select name="groupselect" id="groupselect" size="1">
      <option></option>
    </select>

User: <input type="text" id="username" />

Queue:  <select name="queueselect" id="queueselect" size="1">
      <option></option>
    </select>

Limit:  <select name="limitselect" id="limitselect" size="1">
  <option selected="selected">10</option>
  <option>20</option>
  <option>30</option>
  <option>40</option>
  <option>50</option>
    </select>

&nbsp;
   
<input type="submit" id="startbtn" value="Get data" disabled="disabled" /><span id="warningtext"></span>
  </td>
  </tr>
    <tr id="statsview">
  <td>
<div id="statstabs">
  <ul>
    <li><a href="#clusterstats">Cluster stats</a></li>
    <li><a href="#exec-jobs">Executed Jobs</a></li>
    <li><a href="#slots-job">Slots per Job</a></li>
    <li><a href="#top-ten-nodes">Top-Ten Nodes</a></li>
    <li><a href="#top-ten-users">Top-Ten Users</a></li>
    <li><a href="#top-ten-mem">Top-Ten Memory</a></li>
    <li><a href="#top-ten-mem-avg">Avg Top-Ten Memory</a></li>
    <li><a href="#top-ten-pes">Top-Ten PEs</a></li>
    <li><a href="#top-ten-ios">Top-Ten io</a></li>
    <li><a href="#top-ten-exectime">Top-Ten Exectime</a></li>
    <li><a href="#top-ten-exectime-avg">Avg Top-Ten Exectime</a></li>
    <li><a href="#group-usage">Group Usage</a></li>
    <li><a href="#failed">Failed</a></li>
    <li><a href="#full-list">Full list</a></li>
  </ul>
  <div id="clusterstats">
  <dl>
    <dt>Total number of jobs</dt>
  <dd id="totalnumberofjobs"></dd>
  <dt>Total CPU time (years)</dt>
  <dd id="totalcputime"></dd>
  <dt>Longest running job (hours)</dt>
  <dd id="longestrunningjob"></dd>
  <dt>Average job runtime (hours)</dt>
  <dd id="averagejobruntime"></dd>
  <dt>Total time running in usermode (years)</dt>
  <dd id="totalusermodetime"></dd>
  <dt>Total time running in system mode (years)</dt>
  <dd id="totalsystemmodetime"></dd>
  <dt>Total data transfers (including cache!) in GB</dt>
  <dd id="totalios"></dd>
  </dl>
  </div>
  <div id="exec-jobs">
  <h4>Number of jobs executed per month (if startdate is only up to one year away) or per year</h4>
  <div class="charts" id="exec-jobs-chart"></div>
  </div>
  <div id="slots-job">
  <h4>Number of Slots (Cores) per job, grouped by core ranges</h4>
  <div class="charts" id="slots-job-chart"></div>
  </div>
  <div id="top-ten-nodes">
  <h4>Most used nodes (grid hosts)</h4>
  <div class="charts" id="top-ten-nodes-chart"></div>
  </div>
  <div id="top-ten-users">
  <h4>Users with the highest number of jobs</h4>
  <div class="charts" id="top-ten-users-chart"></div>
  </div>
  <div id="top-ten-mem">
  <h4>Integral of used memory in Terabyte seconds</h4>
  <div class="charts" id="top-ten-mem-chart"></div>
  </div>
  <div id="top-ten-mem-avg">
  <h4>Average integral of used memory in Terabyte seconds</h4>
  <div class="charts" id="top-ten-mem-avg-chart"></div>
  </div>
  <div id="top-ten-ios">
  <h4>Sum of data in GB transferred (includes cache!)</h4>
  <div class="charts" id="top-ten-ios-chart"></div>
  </div>
  <div id="top-ten-pes">
  <h4>Most used PEs</h4>
  <div class="charts" id="top-ten-pes-chart"></div>
  </div>
  <div id="top-ten-exectime">
  <h4>Sum of runtime of all jobs in seconds</h4>
  <div class="charts" id="top-ten-exectime-chart"></div>
  </div>
  <div id="top-ten-exectime-avg">
  <h4>Average runtime of all jobs in seconds</h4>
  <div class="charts" id="top-ten-exectime-avg-chart"></div>
  </div>
  <div id="group-usage">
  <h4>Number of jobs per group</h4>
  <div class="charts" id="group-usage-chart"></div>
  </div>
  <div id="failed">
  <h4>Number of jobs that completed successfully or failed, sorted by exitcodes</h4>
  <div class="charts" id="failed-chart"></div>
  <pre>
  +-----+-----------------------------+----+----------------------------------------+
  |Code | Description                 | OK | Explanation                            |
  +-----+-----------------------------+----+----------------------------------------+
  |0    | no failure                  | Y  | ran and exited normally                |
  +-----+-----------------------------+----+----------------------------------------+
  |1    | assumedly before job        | N  | failed early in execd                  |
  +-----+-----------------------------+----+----------------------------------------+
  |3    | before writing config       | N  | failed before execd set up local spool |
  +-----+-----------------------------+----+----------------------------------------+
  |4    | before writing PID          | N  | shepherd failed to record its pid      |
  +-----+-----------------------------+----+----------------------------------------+
  |6    | setting processor set       | N  | failed setting up processor set        |
  +-----+-----------------------------+----+----------------------------------------+
  |7    | before prolog               | N  | failed before prolog                   |
  +-----+-----------------------------+----+----------------------------------------+
  |8    | in prolog                   | N  | failed in prolog                       |
  +-----+-----------------------------+----+----------------------------------------+
  |9    | before pestart              | N  | failed before starting PE              |
  +-----+-----------------------------+----+----------------------------------------+
  |10   | in pestart                  | N  | failed in PE starter                   |
  +-----+-----------------------------+----+----------------------------------------+
  |11   | before job                  | N  | failed in shepherd before starting job |
  +-----+-----------------------------+----+----------------------------------------+
  |12   | before pestop               | Y  | ran, but failed before calling PE stop |
  |     |                             |    | procedure                              |
  +-----+-----------------------------+----+----------------------------------------+
  |13   | in pestop                   | Y  | ran, but PE stop procedure failed      |
  +-----+-----------------------------+----+----------------------------------------+
  |14   | before epilog               | Y  | ran, but failed before calling epilog  |
  |     |                             |    | script                                 |
  +-----+-----------------------------+----+----------------------------------------+
  |15   | in epilog                   | Y  | ran, but failed in epilog script       |
  +-----+-----------------------------+----+----------------------------------------+
  |16   | releasing processor set     | Y  | ran, but processor set could not be    |
  |     |                             |    | released                               |
  +-----+-----------------------------+----+----------------------------------------+
  |17   | through signal              | Y  | job killed by signal (possibly qdel)   |
  +-----+-----------------------------+----+----------------------------------------+
  |18   | shepherd returned error     | N  | shepherd died                          |
  +-----+-----------------------------+----+----------------------------------------+
  |19   | before writing exit_status  | N  | shepherd didn nott write reports       |
  |     |                             |    | correctly                              |
  +-----+-----------------------------+----+----------------------------------------+
  |20   | found unexpected error file | ?  | shepherd encountered a problem         |
  +-----+-----------------------------+----+----------------------------------------+
  |21   | in recognizing job          | N  | qmaster asked about an unknown job     |
  |     |                             |    | (not in accounting?)                   |
  +-----+-----------------------------+----+----------------------------------------+
  |24   | migrating (checkpointing    | Y  | ran, will be migrated                  |
  |     | jobs)                       |    |                                        |
  +-----+-----------------------------+----+----------------------------------------+
  |25   | rescheduling                | Y  | ran, will be rescheduled               |
  +-----+-----------------------------+----+----------------------------------------+
  |26   | opening output file         | N  | failed opening stderr/stdout file      |
  +-----+-----------------------------+----+----------------------------------------+
  |27   | searching requested shell   | N  | failed finding specified shell         |
  +-----+-----------------------------+----+----------------------------------------+
  |28   | changing to working         | N  | failed changing to start directory     |
  |     | directory                   |    |                                        |
  +-----+-----------------------------+----+----------------------------------------+
  |29   | AFS setup                   | N  | failed setting up AFS security         |
  +-----+-----------------------------+----+----------------------------------------+
  |30   | application error returned  | Y  | ran and exited 100 - maybe re-         |
  |     |                             |    | scheduled                              |
  +-----+-----------------------------+----+----------------------------------------+
  |31   | accessing sgepasswd file    | N  | failed because sgepasswd not readable  |
  |     |                             |    | (MS Windows)                           |
  +-----+-----------------------------+----+----------------------------------------+
  |32   | entry is missing in         | N  | failed because user not in sgepasswd   |
  |     | password file               |    | (MS Windows)                           |
  +-----+-----------------------------+----+----------------------------------------+
  |33   | wrong password              | N  | failed because of wrong password       |
  |     |                             |    | against sgepasswd (MS Windows)         |
  +-----+-----------------------------+----+----------------------------------------+
  |34   | communicating with Grid     | N  | failed because of failure of helper    |
  |     | Engine Helper Service       |    | service (MS Windows)                   |
  +-----+-----------------------------+----+----------------------------------------+
  |35   | before job in Grid Engine   | N  | failed because of failure running      |
  |     | Helper Service              |    | helper service (MS Windows)            |
  +-----+-----------------------------+----+----------------------------------------+
  |36   | checking configured daemons | N  | failed because of configured remote    |
  |     |                             |    | startup daemon                         |
  +-----+-----------------------------+----+----------------------------------------+
  |37   | qmaster enforced h_rt,      | Y  | ran, but killed due to exceeding run   |
  |     | h_cpu, or h_vmem limit      |    | time limit                             |
  +-----+-----------------------------+----+----------------------------------------+
  |38   | adding supplementary group  | N  | failed adding supplementary gid to job |
  +-----+-----------------------------+----+----------------------------------------+
  |100  | assumedly after job         | Y  | ran, but killed by a signal (perhaps   |
  |     |                             |    | due to exceeding resources), task      |
  |     |                             |    | died, shepherd died (e.g. node crash), |
  |     |                             |    | etc.                                   |
  +-----+-----------------------------+----+----------------------------------------|
  | See sge_shepherd(8) for the effect of non zero return codes from the various    |
  | methods (prolog etc.) executed by the sheperd.                                  |
  +---------------------------------------------------------------------------------+
  
</pre>

  </div>
  <div id="full-list">
  <table id="accountingtable" align=center width=95% border="1" cellpadding="0" cellspacing="0">
  <thead>
    <tr CLASS="header">
  <th>Queue</th>
  <th>Hostname</th>
  <th>Group</th>
  <th>Owner</th>
  <th>Jobname</th>
  <th>Job number</th>
  <th>Subm. Time</th>
  <th>Start</th>
  <th>End</th>
  <th>Wallclk</th>
  <th>utime</th>
  <th>stime</th>
  <th>inblk</th>
  <th>outblk</th>
  <th>Grtd PE</th>
  <th>Slots</th>
  <th>CPU</th>
  <th>Mem</th>
  <th>io</th>
  <th>maxvmem</th>
    </tr>


    </thead>
  <tbody>
  </tbody>
  </table>

  </div>
</div>

</td>
  </tr>  <!-- End of TR stats -->


</table>

</body>
</html>

