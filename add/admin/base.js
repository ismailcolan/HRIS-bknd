function getLocationQueryParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\jQuery&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|jQuery)", "i"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function removeAnswer(btn) {

    jQuery(btn).parent().parent().parent().parent().parent().remove();

    var blocks = jQuery(".ansBlock"), blockCnt = blocks.length;
    for (i = 0; i < blockCnt; i++) {
        var ans = blocks[i];
        if (jQuery(ans).prop("id") !== "newAnswer") {
            jQuery(ans).find("input[type='text']").prop("name", "ansName" + (i + 1));
            jQuery(ans).find("input[type='checkbox']").prop("name", "ansPositions" + (i + 1) + "[]");
        }
    }
    return false;
}

function showEmployeeRecordContent() {
    jQuery("#employee_record_button").removeClass("btn-secondary");
    jQuery("#employee_record_button").addClass("btn-primary");
    jQuery("#employee_record_content").show();
    hideEmployeeOnboardingContent();
	hideEmployeeOffboardingContent()
    hideEmployeeCredentialingContent();
	hideAssignedFilesContentContent();
	hideTimeclockContent();
	hideTimeclockRequestContent();
}

function hideEmployeeRecordContent() {
    jQuery("#employee_record_button").removeClass("btn-primary");
    jQuery("#employee_record_button").addClass("btn-secondary");
    jQuery("#employee_record_content").hide();
}

function showEmployeeOnboardingContent() {
    jQuery("#employee_onboarding_button").removeClass("btn-secondary");
    jQuery("#employee_onboarding_button").addClass("btn-primary");
    jQuery("#employee_onboarding_content").show();
    hideEmployeeRecordContent();
    hideEmployeeCredentialingContent();
	hideEmployeeOffboardingContent();
	hideAssignedFilesContentContent();
	hideTimeclockContent();
	hideTimeclockRequestContent();
}

function hideEmployeeOnboardingContent() {
    jQuery("#employee_onboarding_button").removeClass("btn-primary");
    jQuery("#employee_onboarding_button").addClass("btn-" + jQuery("#employee_onboarding_button").prop("data"));
    jQuery("#employee_onboarding_content").hide();
}


function showEmployeeOffboardingContent() {
    jQuery("#employee_offboarding_button").removeClass("btn-secondary");
    jQuery("#employee_offboarding_button").addClass("btn-primary");
    jQuery("#employee_offboarding_content").show();
    hideEmployeeRecordContent();
	hideEmployeeOnboardingContent();
    hideEmployeeCredentialingContent();
	hideAssignedFilesContentContent();
	hideTimeclockContent();
	hideTimeclockRequestContent();
}

function hideEmployeeOffboardingContent() {
    jQuery("#employee_offboarding_button").removeClass("btn-primary");
    jQuery("#employee_offboarding_button").addClass("btn-" + jQuery("#employee_offboarding_button").prop("data"));
    jQuery("#employee_offboarding_content").hide();
}

function showEmployeeCredentialingContent() {
    jQuery("#employee_credentialing_button").removeClass("btn-secondary");
    jQuery("#employee_credentialing_button").addClass("btn-primary");
    jQuery("#employee_credentialing_content").show();
    hideEmployeeRecordContent();
    hideEmployeeOnboardingContent();
	hideEmployeeOffboardingContent();
	hideAssignedFilesContentContent();
	hideTimeclockContent();
	hideTimeclockRequestContent();
}

function showAssignedFilesContent() {
    jQuery("#assigned_files_button").removeClass("btn-secondary");
    jQuery("#assigned_files_button").addClass("btn-primary");
    jQuery("#assigned_files_content").show();
    hideEmployeeRecordContent();
    hideEmployeeOnboardingContent();
	hideEmployeeOffboardingContent();
	hideEmployeeCredentialingContent();
	hideTimeclockContent();
	hideTimeclockRequestContent();
}

function hideAssignedFilesContentContent() {
    jQuery("#assigned_files_button").removeClass("btn-primary");
    jQuery("#assigned_files_button").addClass("btn-" + jQuery("#assigned_files_button").prop("data"));
    jQuery("#assigned_files_content").hide();
}


function showTimeclockContent() {
    jQuery("#timeclock_button").removeClass("btn-secondary");
    jQuery("#timeclock_button").addClass("btn-primary");
    jQuery("#timeclock_content").show();
    hideEmployeeRecordContent();
    hideEmployeeOnboardingContent();
	hideEmployeeOffboardingContent();
	hideEmployeeCredentialingContent();
	hideAssignedFilesContentContent();
	hideTimeclockRequestContent();
}

function hideTimeclockContent() {
    jQuery("#timeclock_button").removeClass("btn-primary");
    jQuery("#timeclock_button").addClass("btn-" + jQuery("#assigned_files_button").prop("data"));
    jQuery("#timeclock_content").hide();
}

/*****REQUEST NEW****/
function showTimeclockRequestContent() {
    jQuery("#timeclock_request_button").removeClass("btn-secondary");
    jQuery("#timeclock_request_button").addClass("btn-primary");
    jQuery("#timeclock_request_content").show();
    hideEmployeeRecordContent();
    hideEmployeeOnboardingContent();
	hideEmployeeOffboardingContent();
	hideEmployeeCredentialingContent();
	hideAssignedFilesContentContent();
	hideTimeclockContent();
}

function hideTimeclockRequestContent() {
    jQuery("#timeclock_request_button").removeClass("btn-primary");
    jQuery("#timeclock_request_button").addClass("btn-" + jQuery("#assigned_files_button").prop("data"));
    jQuery("#timeclock_request_content").hide();
}
/********/

function hideEmployeeCredentialingContent() {
    jQuery("#employee_credentialing_button").removeClass("btn-primary");
    jQuery("#employee_credentialing_button").addClass("btn-" + jQuery("#employee_credentialing_button").prop("data"));
    jQuery("#employee_credentialing_content").hide();
}

function removeResourceFile(url) {
    if (confirm("Are you sure?")) {
        jQuery("#filesList").load(url);
    }
}

function removeResourceLink(url) {
    if (confirm("Are you sure?")) {
        jQuery("#linksList").load(url);
    }
}


jQuery(function () {
    if (jQuery("#jobPositionSelect")) {
        jQuery("#jobPositionSelect").change(function () {
            var newVal = jQuery("#jobPositionSelect").val();
            if (newVal !== "{!--Empty--!}") {
                jQuery("#jobTitleEdit").val(jQuery("#jobPositionSelect option:selected").text());
            }
        });
    }
    /**********/
    if (jQuery("#applicationStatus")) {
        jQuery("#applicationStatus").change(function () {
            var newVal = jQuery("#applicationStatus").val();
            if (newVal === 'Hired') {
                jQuery("#hiredInfo").show();
            } else {
                jQuery("#hiredInfo").hide();
            }
            if ((newVal === 'Hired')  || (newVal === 'For Interview') || (newVal === 'For Decision') || (newVal === 'Manually Rejected') ) {
                jQuery("#ratingInfo").show();
            } else {
                jQuery("#ratingInfo").hide();
            }
        });
	 jQuery("#applicationStatus").change();
    }
	/***********/

    if (jQuery("#HiredForPID")) {
        jQuery("#HiredForPID").change(function () {
            var newVal = jQuery("#HiredForPID").val();
            if (newVal === '9999') {
                jQuery("#managerInfo").show();
            } else {
                if (jQuery("#HiredForPID").attr("data") == "manager") {
                    if (confirm("There are employees under this Manager. If you change his position or fire him/her, those employees will be transferred under the HR Manager. Do you want to proceed?")) {
                        jQuery("#managerInfo").hide();
                    } else {
                        jQuery("#HiredForPID").val(jQuery("#HiredForPID").attr("value"))
                    }
                }
            }

        });
    }

    if (jQuery("#addNewAnswer")) {
        jQuery("#addNewAnswer").click(
            function () {
                var newAns = jQuery("#newAnswer");
                var blockCnt = jQuery(".ansBlock").length;
                var ans = jQuery(newAns).clone();

                jQuery(ans).prop("id", "ansBlock");
                jQuery(ans).show();
                jQuery(ans).find("input[type='text']").prop("name", "ansName" + blockCnt);
                jQuery(ans).find("input[type='checkbox']").prop("name", "ansPositions" + blockCnt + "[]");
                jQuery(ans).find("button").parent().parent().show();
                jQuery(ans).insertBefore(newAns);

                jQuery(newAns).find("input[type='text']").val("");
                jQuery(newAns).find("input[type='checkbox']").prop("checked", false);

                jQuery(".ansBlock>button").click(
                    function () {
                        return removeAnswer(this);
                    }
                );

                return false;
            }
        );
    }

    if (jQuery(".ansBlock>button")) {
        jQuery(".ansBlock>button").click(
            function () {
                return removeAnswer(this);
            }
        );
    }

    if (jQuery("#btnSaveApplication")) {
        jQuery("#btnSaveApplication").click(
            function () {
                if (jQuery("#applicationStatus").val() === "Hired") {
                    return confirm("The applicant will be HIRED with the position, department, etc. you've specified. Are you sure?");
                }
            }
        );
    }

    if (jQuery("input[name='photoCmd']")) {
        jQuery("input[name='photoCmd']").click(
            function () {
                jQuery("#selectPhotoFile").hide();
                var item = jQuery("input[name='photoCmd']:checked").val();
                switch (item) {
                    case "download": break;
                    case "upload": jQuery("#selectPhotoFile").show();
                }
            }
        );
    }

    if (jQuery("input[name='bioCmd']")) {
        jQuery("input[name='bioCmd']").click(
            function () {
                jQuery("#selectBioFile").hide();
                var item = jQuery("input[name='bioCmd']:checked").val();
                switch (item) {
                    case "download": break; //location.href = jQuery("#downloadBio").prop("href"); break;
                    case "upload": jQuery("#selectBioFile").show();
                }
            }
        );
    }

    if (jQuery("input[name='cvCmd']")) {
        jQuery("input[name='cvCmd']").click(
            function () {
                jQuery("#selectCVFile").hide();
                var item = jQuery("input[name='cvCmd']:checked").val();
                switch (item) {
                    case "download": break; //location.href = jQuery("#downloadCV").prop("href"); break;
                    case "upload": jQuery("#selectCVFile").show();
                }
            }
        );
    }

    if (jQuery("#savePositionBtn")) {
        jQuery("#savePositionBtn").click(
            function () {
                var oldData = jQuery("[name=pName]").attr("data");
                var newData = jQuery("[name=pName]").val();
                if (oldData !== newData) {
                    return confirm("Are you sure you want to change the position name? This will affect all existing employees and applicants.");
                }
            }
        );
    }

    if (jQuery("#saveOfficeBtn")) {
        jQuery("#saveOfficeBtn").click(
            function () {
                var oldData = jQuery("[name=coName]").attr("data");
                var newData = jQuery("[name=coName]").val();
                if (oldData !== newData) {
                    return confirm("Are you sure you want to change the office name? This will affect all existing employees.");
                }
            }
        );
    }

    if (jQuery("#saveDepartmentBtn")) {
        jQuery("#saveDepartmentBtn").click(
            function () {
                var oldData = jQuery("[name=dName]").attr("data");
                var newData = jQuery("[name=dName]").val();
                if (oldData !== newData) {
                    return confirm("Are you sure you want to change the department name? This will affect all existing employees.");
                }
            }
        );
    }



    if (jQuery("#employee_record_button")) {
        jQuery("#employee_record_button").click(
            function () {
                showEmployeeRecordContent();
            }
        );
    }

    if (jQuery("#employee_onboarding_button")) {
        jQuery("#employee_onboarding_button").click(
            function () {
                showEmployeeOnboardingContent();
            }
        );
    }
	
	if (jQuery("#employee_offboarding_button")) {
        jQuery("#employee_offboarding_button").click(
            function () {
                showEmployeeOffboardingContent();
            }
        );
    }
	
	if (jQuery("#assigned_files_button")) {
        jQuery("#assigned_files_button").click(
            function () { 
                showAssignedFilesContent();
            }
        );
    }

    if (jQuery("#employee_credentialing_button")) {
        jQuery("#employee_credentialing_button").click(
            function () {
                showEmployeeCredentialingContent();
            }
        );
    }
	
	if (jQuery("#timeclock_button")) {
        jQuery("#timeclock_button").click(
            function () {
                showTimeclockContent();
            }
        );
    }
	
	if (jQuery("#timeclock_request_button")) {
        jQuery("#timeclock_request_button").click(
            function () {
                showTimeclockRequestContent();
            }
        );
    }

    if (jQuery("#onboarding-form")) {
        jQuery("#onboarding-form").submit(
            function () { 
                var positions = jQuery("#onboarding-form input[name='otPositions[]']:checked");
                if (positions.length === 0) {
                    return confirm("The task has no position assigned. Are you sure you want to continue?");
                } else {
                    return true;
                }
            }
        );
    }

    if (jQuery("#credentialing-form")) {
        jQuery("#credentialing-form").submit(
            function () {
                var positions = jQuery("#credentialing-form input:checked");
                if (positions.length === 0) {
                    return confirm("The credentialing has no position assigned. Are you sure you want to continue?");
                } else {
                    return true;
                }
            }
        );
    }

    if (jQuery("#addResourceFileCtrl")) {
        jQuery("#addResourceFileCtrl").click(
            function () {
                jQuery("#newResourceFile").val("");
                jQuery("#addResourceFileErr").html("");
                jQuery("#addResourceFileModal").modal();

                return false;
            }
        );
    }

    if (jQuery("#addResourceFileApplyBtn")) {
        jQuery("#addResourceFileApplyBtn").click(
            function () {
                var file_data = jQuery('#newResourceFile').prop('files')[0],
                    err_msg = "",
                    uploadTo = jQuery('#addResourceFileApplyBtn').attr("data-url");

                if (file_data) {
                    var fn = file_data.name,
                        ext = fn.slice(-4),
                        size = file_data.size;

                    if (ext.toLowerCase() !== ".pdf") {
                        err_msg = "Select only .pdf file!"
                    } else {
                        if (size > 20971520) {
                            err_msg = "File size exceeds 20MB!";
                        }
                    }

                } else {
                    err_msg = "Please select a file!";
                }

                if (err_msg !== "") {
                    jQuery("#addResourceFileErr").html(err_msg);
                    return false;
                }

                var form_data = new FormData();
                form_data.append('newResourceFile', file_data);
                $.ajax({
                    url: uploadTo,
                    dataType: 'json',  // what to expect back from the PHP script, if anything
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    type: 'post',
                    success: function (resp) { 
                        if (resp.result === "OK") {
                            jQuery("#filesList").html(resp.html);
                            jQuery("#addResourceFileModal").modal('hide');
                        }
                        if (resp.result === "error") {
                            jQuery("#addResourceFileErr").html(resp.html);
                        }

                        return false;
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert("Failed to upload file!");
                        console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        return false;
                    }
                });

                return false;
            }
        );
    }




    if (jQuery("#addResourceLinkCtrl")) {
        jQuery("#addResourceLinkCtrl").click(
            function () {
                jQuery("#newResourceLink").val(PROTOCOL);
                jQuery("#addResourceLinkErr").html("");
                jQuery("#addResourceLinkModal").modal();

                return false;
            }
        );
    }


    if (jQuery("#addResourceLinkApplyBtn")) {
        jQuery("#addResourceLinkApplyBtn").click(
            function () {
                var link_data = jQuery('#newResourceLink').val();
                    text_data = jQuery('#newResourceLinkText').val();
                    err_msg = "",
                    uploadTo = jQuery('#addResourceLinkApplyBtn').attr("data-url");

                if (
                  (link_data && (link_data.trim() !== ""))
                 &&   
                   (text_data && (text_data.trim() !== ""))
                ) {
                    if(
                        (link_data.slice(0, 7).toLowerCase() !== "http://") &&
                        (link_data.slice(0, 8).toLowerCase() !== "https://")
                    ) {
                        err_msg = "Link must begin with http:// or https://";
                    }
                } else {
                    err_msg = "Please input a url and title!";
                }

                if (err_msg !== "") {
                    jQuery("#addResourceLinkErr").html(err_msg);
                    return false;
                }

                var form_data = new FormData();
                form_data.append('newResourceLink', link_data);
                form_data.append('newResourceLinkText', text_data);
                $.ajax({
                    url: uploadTo,
                    dataType: 'json',  // what to expect back from the PHP script, if anything
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    type: 'post',
                    success: function (resp) {
                        if (resp.result === "OK") {
                            jQuery("#linksList").html(resp.html);
                            jQuery("#addResourceLinkModal").modal('hide');
                        }
                        if (resp.result === "error") {
                            jQuery("#addResourceLinkErr").html(resp.html);
                        }

                        return false;
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert("Failed to add link!");
                        console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        return false;
                    }
                });

                return false;
            }
        );
    }

    
    var errors = jQuery("span.err");
    if (errors) {
        var errCnt = errors.length;
        for (i = 0; i < errCnt; i++) {
            var err = errors[i],
                innerText = jQuery(err).text() || "";
            if (innerText != "") {
                jQuery('html, body').animate({
                    scrollTop: jQuery(err).parent().offset().top
                });
                return;
            }
        }
    }
});

jQuery.fn.extend({
  setCursorPosition: function(position){
    if(this.length == 0) return this;
    return $(this).setSelection(position, position);
  },

  setSelection: function(selectionStart, selectionEnd) {
    if(this.length == 0) return this;
    input = this[0];

    if (input.createTextRange) {
      var range = input.createTextRange();
      range.collapse(true);
      range.moveEnd("character", selectionEnd);
      range.moveStart("character", selectionStart);
      range.select();
    } else if (input.setSelectionRange) {
      input.focus();
      input.setSelectionRange(selectionStart, selectionEnd);
    }

    return this;
  },

  focusEnd: function(){
    this.setCursorPosition(this.val().length);
    return this;
  },

  getCursorPosition: function() {
    var el = $(this).get(0);
    var pos = 0;
    if("selectionStart" in el) {
      pos = el.selectionStart;
    } else if("selection" in document) {
      el.focus();
      var Sel = document.selection.createRange();
      var SelLength = document.selection.createRange().text.length;
      Sel.moveStart("character", -el.value.length);
      pos = Sel.text.length - SelLength;
    }
    return pos;
  },

  insertAtCursor: function(myValue) {
    return this.each(function(i) {
      if (document.selection) {
        //For browsers like Internet Explorer
        this.focus();
        sel = document.selection.createRange();
        sel.text = myValue;
        this.focus();
      }
      else if (this.selectionStart || this.selectionStart == "0") {
        //For browsers like Firefox and Webkit based
        var startPos = this.selectionStart;
        var endPos = this.selectionEnd;
        var scrollTop = this.scrollTop;
        this.value = this.value.substring(0, startPos) + myValue +
              this.value.substring(endPos,this.value.length);
        this.focus();
        this.selectionStart = startPos + myValue.length;
        this.selectionEnd = startPos + myValue.length;
        this.scrollTop = scrollTop;
      } else {
        this.value += myValue;
        this.focus();
      }
      })
  }
});


$( document ).ready(function() {
	initAccordeon(); 
    jQuery('#alertModal').on('hidden.bs.modal', function () {
    jQuery(focused_element).focus();
	});
});



var focused_element=null;
function myalert(txt){ 
	focused_element = $(":focus")
	$('#alert_text').html(txt);
	$('#alertModal').modal('show');
}

function initAccordeon(){
	var acc = document.getElementsByClassName("accordion");
	var i;
	for (i = 0; i < acc.length; i++) {
	  acc[i].addEventListener("click", function() {
		this.classList.toggle("activeaccordion");
		var panel = this.nextElementSibling;
		if (panel.style.maxHeight){
		  panel.style.maxHeight = null;
		} else {
		  panel.style.maxHeight = panel.scrollHeight + "px";
		} 
	  });
	}
}
var CK;
var focused_element=null;
function myalert(head,txt){ 
	focused_element = jQuery(":focus")
	jQuery('#alert_text').html(txt);
	jQuery('#msg_title').html(head);
	jQuery('#alertModal').modal('show');
}

function set_visible(that,id){
	ckb = jQuery(that).is(':checked');
	if (ckb) CK='visible'; else CK ='invisible';
	jQuery.ajax({
	  url: "/admin/file_request?id="+id+"&state="+CK+'&action=set_file',
	  beforeSend: function( xhr ) {
		xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
	  }
	})
	  .done(function( data ) {
		var d = jQuery.parseJSON(data);
		if (d.result=='OK' ) {
			myalert('Message','File was set as '+CK+'.');
			
		}
		else {
			var myID='#cb'+d.id;
			myalert('Problem in setting file visibility.');
			var st;
			if (d.state == 'invisible') st=true; else st=false;
			jQuery(myID).attr('Message','checked',st);
		}
		
	  });
	
}

function insertAtCaret(myValue) {
    CKEDITOR.instances.mytext.insertText(myValue);
}

function insertHTMLAtCaret(myValue) {
    CKEDITOR.instances.mytext.insertHtml(myValue);
}

function insertAtCursor(myField, myValue) { 
    //IE support
    if (document.selection) {
        myField.focus();
        sel = document.selection.createRange();
        sel.text = myValue;
    }
    //MOZILLA and others
    else if (myField.selectionStart || myField.selectionStart == '0') {
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        myField.value = myField.value.substring(0, startPos)
            + myValue
            + myField.value.substring(endPos, myField.value.length);
    } else {
        myField.value += myValue;
    }
}

String.prototype.fulltrim = function(){return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');};

function go_unsign(emp,doc){ 
   if (confirm('Are you sure to mark this document as unsigned?')){	    
	    var url='employee-details?&cmd=undo-sign-doc&id='+emp+'&doc='+doc;
		jQuery.ajax({
				url: url,
				success: function (result) {
				  resp = jQuery.parseJSON(result); 
				  if (resp.result === "ok")  {
					myalert('Info','The document was marked as unsigned.');
					jQuery(resp.cont).html('Not signed');
				  }
				  else myalert('Alert',resp.message);
				},
				//async: false
			});
   }
}

var _that=null;
function change_doc_state(that,doc){ 
   _that=that;
  
   state=jQuery(that).data('state');
   //alert(state);
   if (state=='active')
	   var confirmstr="The document will be required for employees again. Are you sure you want to proceed?";
   else 
	   var confirmstr="The signed documents will remain in the system but new employee won't sign it. Are you sure you want to proceed?";
   if (confirm(confirmstr)){	    
	    var url='onboarding?&cmd=change-doc-state&docid='+doc+'&state='+state;
		jQuery.ajax({
				url: url,
				success: function (result) {//alert(result);
				  resp = jQuery.parseJSON(result); 
				  if (resp.result === "ok")  {
					myalert('Info','The document was set '+resp.state+'.');
					jQuery(_that).data('state', resp.new_state);
					jQuery(_that).text(resp.text);
					jQuery(_that).parent('td').find('.state_txt').text(resp.state);
					
					if (resp.state == 'active'){
					   jQuery('.upl_select').append("<option value='"+resp.doc+"'>To replace:'"+resp.title+"'</option>");
					}
					else {
						jQuery('.upl_select option[value="'+resp.doc+'"]').remove();
					}
					
					
				  }
				  else myalert('Alert',resp.message);
				},
				
			});
   }
}

function isValidDate(dateString) { 

    var parts = dateString.split('/');
	
    if(parts.length !== 3) {
        myalert('Alert','Please enter date in format m/d/yyyy. Per example: '+ USER_DATE_EXAMPLE+'.');return false;
    }
	
	var user_date_parts = USER_DATE.split('/');
	var len;
	
	if (user_date_parts[2] == 'Y') len=4; else len=2;
	if (parts[2].length != len) {myalert('Message',dateString +' is invalid date.Please use exactly '+len+' digit in year.');return false;}
  
	var l=parts[0].length;
	if ( l<1 || l>2 ) {myalert('Message',dateString +' is invalid date. Please use 1 or 2  digits for month.');return false;}
    var l=parts[1].length;
	if ( l<1 || l>2 ) {myalert('Message',dateString +' is invalid date. Please use 1 or 2  digits for day.');return false;}
   
	
	var m = parseInt(parts[0])-1,
        d = parseInt(parts[1]),
        y = parseInt(parts[2]);
        
		
	if ( m < 0 || m > 12 ) {myalert('Message',dateString +' is invalid date. Please enter correct month.');return false;}
	if ( d < 0 || d > 31 ) {myalert('Message',dateString +' is invalid date. Please enter correct day.');return false;}
	
	var date = new Date(y, m, d);
	
    if(date === "Invalid Date") {myalert('Message',dateString +' is invalid date.');return false;}

    if( (date.getFullYear() !== y) || (date.getMonth() !== m) || (date.getDate() !== d) ) return false;

    return date.getTime() - date.getTimezoneOffset();
}

function isValidDateAdmin(dateString,year) { 

    var parts = dateString.split('/');
	
    if(parts.length !== 3) {
        myalert('Alert','Please enter date in format m/d/yyyy. Per example: '+ USER_DATE_EXAMPLE+'.');return false;
    }
	
	var user_date_parts = USER_DATE.split('/');
	var len;
	
	if (user_date_parts[2] == 'Y') len=4; else len=2;
	if (parts[2].length != len) {myalert('Message',dateString +' is invalid date.Please use exactly '+len+' digit in year.');return false;}
  
	var l=parts[0].length;
	if ( l<1 || l>2 ) {myalert('Message',dateString +' is invalid date. Please use 1 or 2  digits for month.');return false;}
    var l=parts[1].length;
	if ( l<1 || l>2 ) {myalert('Message',dateString +' is invalid date. Please use 1 or 2  digits for day.');return false;}
   
	
	var m = parseInt(parts[0])-1,
        d = parseInt(parts[1]),
        y = parseInt(parts[2]);
		
    if (isNaN(m) || isNaN(d) || isNaN(y))  {myalert('Message',dateString +' is invalid date. Please use a digits only.');return false;}	
	if ( y != year ) {myalert('Message',dateString +' is invalid date. Please enter correct date in year '+year+'.');return false;}	
	if ( m < 0 || m > 11 ) {myalert('Message',dateString +' is invalid date. Please enter correct month.');return false;}
	if ( d < 0 || d > 31 ) {myalert('Message',dateString +' is invalid date. Please enter correct day.');return false;}
	if (d > daysInMonth(m, y)) {myalert('Message',dateString +' is non existing date. Please enter correct date.');return false;}
	
	var date = new Date(y, m, d);
	
    if(date === "Invalid Date") {myalert('Message',dateString +' is invalid date.');return false;}

    if( (date.getFullYear() !== y) || (date.getMonth() !== m) || (date.getDate() !== d) ) return false;

    return date.getTime() - date.getTimezoneOffset();
}

var daysInMonth = function (m, y) {
    switch (m) {
        case 1 :
            return (y % 4 == 0 && y % 100) || y % 400 == 0 ? 29 : 28;
        case 8 : case 3 : case 5 : case 10 :
            return 30;
        default :
            return 31
    }
};
