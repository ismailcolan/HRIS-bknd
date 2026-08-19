<?php
class MyEmployeesView extends ApplicationView{
	
	public static function Init(){
		echo '
		<div class="content bg-white"><div class="row">
		<h3></h3>
			<ul class="nav nav-tabs text-default">
				<li class="active" >
				<a  id="unsigned_rep_link" onclick="resizeIframes()" class="no-bottom-border" data-toggle="tab" href="#unsigned-reports">Unsigned reports</a></li>
				<li><a  onclick="resizeIframes()" class="no-bottom-border" data-toggle="tab" href="#days-off-requests">Days off requests</a></li>
				<li><a onclick="resizeIframes()" class="no-bottom-border" data-toggle="tab" href="#my-employees">My Employees</a></li>
				<li><a onclick="resizeIframes()" class="no-bottom-border" data-toggle="tab" href="#upcomming-days-off">Upcoming Days Off</a></li>
				<li><a onclick="resizeIframes()" class="no-bottom-border" data-toggle="tab" href="#employee-of-the-month">Employee of the month</a></li>
			</ul>
			<div class="tab-content">
				<div id="unsigned-reports" class="tab-pane fade in tab-pane-bordered  active" >
				<iframe d="unsigned-reports-ifrm" onload="res(this)" name="unsigned_rep_iframe" class="iframe_page" scrolling="no" src="/managertcureports"></iframe>
				</iframe>
				</div>

				<div id="days-off-requests" class="tab-pane tab-pane-bordered fade">
				<iframe  id="days-off-requests-ifrm" onload="res(this)" class="iframe_page" scrolling="no" src="?cmd=days-off-request">
				</iframe>
				</div>
				
				<div id="my-employees" class="tab-pane tab-pane-bordered fade">
				<iframe id="my-employees-ifrm" onload="res(this)" class="iframe_page" scrolling="no" src="?cmd=my-employees-list">
				</iframe>
				</div>
				
				<div id="upcomming-days-off" class="tab-pane tab-pane-bordered fade">
				<iframe id="my-employees-ifrm" onload="res(this)" class="iframe_page" scrolling="no" src="?cmd=upcomming-days-off">
				</iframe>
				</div>
				
				<div id="employee-of-the-month" class="tab-pane tab-pane-bordered fade">
				<iframe id="employee-of-the-month-ifrm" onload="res(this)" class="iframe_page"  src="?cmd=test">
				</iframe>
				</div>
			</div>

    </div>
	<script>
	
	function resizeIframes(){
		setTimeout(doResizeIframes, 350);
	}
	function doResizeIframes(){
       var iFrames = jQuery(".iframe_page");
      	function iResize() { 
    		for (var i = 0, j = iFrames.length; i < j; i++) { 
    		  iFrames[i].style.height = iFrames[i].contentWindow.document.body.offsetHeight +30+ "px";
			}
		}
		iResize();
    }

	function res(that){
			that.style.height = that.contentWindow.document.body.offsetHeight + 30+"px";
	}
	
	</script>';
	}
	
	public static function ShowDaysOffRequest($data){
		include __DIR__ ."/../webapp-html/header-only.html";
		echo '<body style="background:#FFF;min-height:500px">';
		
		echo '<div style="width:100%;text-align:center">';
		msg::get();
		echo '
		<div style="padding:15px;color:#337ab7;font-size:22px;font-weight:bold">Requests for days off</div>
		<table class="white_table" style="border:1px solid #CCC;width:100%;margin:0 auto;">
		<thead>
		<tr><td>Employee </td>
		<td>Sent</td><td>From</td><td>To</td><td>Days</td>
		<td>Type</td><td></td></tr>
		</thead>
		<tbody>';
		if (empty($data)) echo '<tr><td colspan="7" style="text-align:center">No pending requests found</td></tr>';
		else {
			foreach ($data as $k=>$d){
				$date_submitted = val::convDate(MYSQL_DATE,USER_DATE,$d['vrDateEntered']);
				$start_date=val::convDate(MYSQL_DATE,USER_DATE,$d['vrDateStart']);
				$end_date=val::convDate(MYSQL_DATE,USER_DATE,$d['vrDateEnd']);
				$days=val::mynumber_format($d['vrDays'],1);
				echo "<tr><td style=\"text-align:center\">{$d['afFirstName']} {$d['afLastName']}</td>
			<td>{$date_submitted}</td><td>{$start_date}</td><td>{$end_date}</td>
			<td>{$days}</td><td>{$d['vrType']}, {$d['vrPaydUnpaid']}</td>
			<td>
			<a  href=\"?cmd=approve&id={$d['vrID']}\" onclick=\"return confirm('Are you sure to approve that request?')\">Approve</a>
			| <a href=\"javascript:void(0)\" onclick=\"askForReason({$d['vrID']})\">Decline</a>
			</td>
			</tr>";
			}
		}
		
		echo '</tbody>
			</table>
			</div>';
		
		echo '
		<div class="modal fade" id="reasonScreen" role="dialog" style="z-index:9999999">
		<div class="modal-dialog" style="width:80vw;height:70vh;">
			<div class="modal-content" style="text-align:center">
				<div class="modal-header">
				
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="msg_title">REASON FOR BEING DECLINED:</h4>
				</div>
				<br>
			   <div id="reasonDiv" style="text-align:center">
			   <input type="text" maxlength="60" style="border:1px solid gray;width:90%;margin:0 auto;" id="reason">
			   <br>Max 60 characters
				</div>
				<br>
				<div class="modal-footer" style="text-align:center">
				<button onclick="doSend()" type="button" id="reason_button" class="btn btn-primary" data-dismiss="modal">Decline</button>
				</div>
			</div>
		</div>
	</div>

		
		<script>
		var id;
		function askForReason(myid){
			id=myid;
			var txt = jQuery("#reason").val("");
			jQuery("#reasonScreen").modal("show");
			jQuery("#reason").focus();
		}
		
		function doSend(){
			var txt = jQuery("#reason").val();
			txt=txt.trim();
			if (txt == "" ) alert("You can not decline request without filled reason");
			else {
				window.location="?cmd=decline&id="+id+"&reason="+txt;
			}
		}
		</script>';
		
			
			
		echo '</body>
			</html>';
			
			
		echo '</body></html>';
	}
}
