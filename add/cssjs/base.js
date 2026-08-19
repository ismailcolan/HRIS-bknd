if (!window.console) console = {
    log: function () {
    }
};
/* fix for console.log for ie8 */

function queryStringValue(key) {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars[key];
}


function checkAnswers() {
    var questions = jQuery("div.card-question");

    var qCnt = questions.length;
    for (i = 0; i < qCnt; i++) {
        var q = questions[i];
        answers = jQuery(q).find("input[type='radio']:checked");
        errDiv = jQuery(q).find("div.err")[0];
        if (answers.length === 0) {
            jQuery(errDiv).text("Please choose the answer.");
            return;
        } else {
            jQuery(errDiv).text("");
        }
    }

    var options = jQuery("input[type='radio']:checked");
    var jID = queryStringValue("jID");
    if (jID === undefined) jID = -1;
    var oCnt = options.length,
        url = window.location.href.split('?')[0] + "?jID=" + jID + "&pID=" + queryStringValue("pID");
    for (var i = 0; i < oCnt; i++) {
        var opt = options[i];
        url += "&" + $(opt).prop("name") + "=" + $(opt).prop("value");
    }
    url += "&questionnaire=1";
    location.href = url;
}


function showVal(newVal) {
    newVal = Math.round(newVal);
    jQuery("#desiredSalary").html("$ " + newVal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
}

function scrollToFirstError(checkEmpty) {
    var errors = jQuery(".err"), errCnt = errors.length, minTop = 999999;
    for (i = 0; i < errCnt; i++) {
        var err = errors[i];
        if (!checkEmpty || (jQuery(err).innerText && (jQuery(err).innerText.trim() !== ""))) {
            var top = jQuery(err).parent().offset().top;
            if(top < minTop) minTop = top;
        }
    }

    jQuery('html, body').animate({
        scrollTop: (minTop === 999999 ? 0 : minTop)
    });
}


function editCredentialingExpDate(ID, oldDate) { 
    var newDateCandidate = prompt("Please enter expiration date in format m/d/yyyy Example: "+USER_DATE_EXAMPLE, oldDate);
    
	if (isValidDate(newDateCandidate)){
		var newDate = newDateCandidate;
	
		if ((newDate != null) && (newDate != oldDate)) {
			var validDate = isValidDate(newDate);
			if(validDate === false) {
				myalert('Alert','"'+newDate+'" is not a valid date. Please enter date in "m/d/yyyy" format.');
			} else {
				if(validDate < new Date().getTime()) {
					myalert("Alert",'Expiration/renewal can not be in the past.');
					return false;
				}

				jQuery('input[name="ecID"]').val(ID);
				jQuery('input[name="newRenewalDate"]').val(newDate);
				
				if (oldDate=='') {jQuery("#emptyecFileName").val('yes');}
				jQuery("#editCredentialRenewalDate").submit();
			}
		}
	}
    return false;
}

function uploadCredentialingFile(ID, withExpDate,ExpDate,ComplDate) {
    jQuery('input[name="ecID"]').val(ID);

	if(withExpDate) {
        jQuery('#credentialingExpDate').show();
    } else {
        jQuery('#credentialingExpDate').hide();
    }
	jQuery('input[name="credentialingExpDate"]').next('.input-hint').html(
		'Example: '+USER_DATE_EXAMPLE+'<span style="color:red;margin-left:20px">No later than: '+ExpDate+'</span>'
	);
	jQuery('#exp-date-field').val(ExpDate);

	if (ComplDate != '0') { 
		jQuery('#complBlock').show();
		jQuery('input[name="credentialingComplDate"]').next('.input-hint').html(
		'Example: '+USER_DATE_EXAMPLE+'<span style="color:red;margin-left:20px">No earlier than : '+ComplDate+'</span>'
		)
	}
	jQuery('#compl-date-field').val(ComplDate);

    jQuery('#credentialingUploadModal').modal('show');
    return false;
}

function validateCredentialingUpload(maxFileSize) {
    var file = jQuery('input[name="credentialingFile"]').val();
    if(!file) {
        alert("Please select a file to upload.");
        return false;
    }
    var acceptedExt = ["pdf", "doc", "docx", "xls", "xlsx", "jpg", "png"],
        hasExt = acceptedExt.indexOf(file.split('.').pop().toLowerCase());
    if(hasExt === -1) {
        alert("The file format you've uploaded isn't allowed. Please upload only :.pdf, .doc, .docx, .xls, .xlsx, .jpg, .png.");
        return false;
    }

    var file = jQuery('input[name="credentialingFile"]')[0],
        fileSize = file.files[0].size;
    if(fileSize > maxFileSize) {
        alert("Sorry, your file is too large. Maximum size allowed is "+Math.round(maxFileSize / 1048576)+"Mb.");
        return false;
    }

    var expdatectrl = jQuery('#credentialingExpDate:visible');
	
    if(expdatectrl.length > 0) {

        var expdate = jQuery('input[name="credentialingExpDate"]').val(),
            isValid = isValidDate(expdate),
            maxDate = jQuery('#exp-date-field').val(),
			maxDateValid= isValidDate(maxDate);
			
        if(isValid === false) {
            myalert('Message','"'+expdate+'" is not a valid date. Please enter date in "m/d/yyyy" format.');
            return false;
        }

        if(isValid < new Date().getTime()) {
            myalert('Message','Expiration/renewal can not be in the past.');
            return false;
        }
		
		if(maxDate !='0' && isValid > maxDateValid) {
            myalert('Message','Expiration/renewal can not be later than '+maxDate+'.');
            return false;
        }
    }

	var complDate = jQuery('input[name="credentialingComplDate"]').val();
	if (complDate != ''){
		 isValid = isValidDate(complDate),
		 minDate = jQuery('#compl-date-field').val(),
		 minDateValid= isValidDate(minDate);
		
		if(isValid > new Date().getTime()) {
            myalert('Message','Date completed/created can not be in the future.');
            return false;
        }
		
		if(minDate != '0' && isValid < minDateValid) {
            myalert('Message','Document too old, please complete and upload a new one.');
            return false;
        }
		
		
	}

    return true;
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

function showResourceItems(url) {

    // return false;

    // 
    // , function() {
        jQuery("#modalResourceItems").modal('show');
        jQuery("#modalResourceItemsContent").load(url);
    // });
    return false;
}

jQuery(
    function () {
        var skipCheck = false;

        jQuery("#dataForm").submit(
            function (e) {
                console.log("Submitting...");

                if (skipCheck) {
                    return true;
                }
                e.preventDefault();
                var formData = new FormData(this);
                var url = (jQuery(this).attr("action") || location.href) + "&mode=checkdata";
                jQuery.ajax({
                    method: 'post',
                    processData: false,
                    contentType: false,
                    cache: false,
                    data: formData,
                    enctype: 'multipart/form-data',
                    url: url,
                    success: function (data) {
                        resp = jQuery.parseJSON(data); 
                        if (resp.result === "ok") {
                            skipCheck = true;
                            jQuery('<input>').prop({
                                type: 'hidden',
                                name: 'save-btn',
                                value: 'Save'
                            }).appendTo("#dataForm");
                            jQuery("#dataForm").submit();
                            console.log("Submitted!");
                        } else {
                            jQuery(".err").remove();
                            jQuery(".error").removeClass("error");
                            jQuery.each(resp.errors, function (key, value) {
                                if(key == "not_logged") {
                                    jQuery(this).attr("action", url + '&skip-check=true');
                                    skipCheck = true;
                                    jQuery("#form-data").hide();
                                    jQuery("#confirm-panel").show();
                                } else {
                                    var input = jQuery("[name=" + key + "]");
                                    if (input) {
                                        jQuery(input).addClass("error");
                                        jQuery('<span class="err">' + resp.errors[key][0] + '</span>').insertAfter(input);
                                    }
                                }
                            });
                            scrollToFirstError(false);
                        }
                    }
                });

                return false;
            }
        );

        jQuery("#activePositions").change(
            function () {
                var val = $("#activePositions").val();
                val === "{!--Empty--!}" ? jQuery("#btnApplyPosition").prop("disabled", true) : jQuery("#btnApplyPosition").prop("disabled", false);
            }
        );

        scrollToFirstError(true);
    }
);

jQuery( document ).ready(function() {
	initAccordeon(); 
    jQuery('#alertModal').on('hidden.bs.modal', function () {
    jQuery(focused_element).focus();
	});
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

var focused_element=null;
function myalert(head,txt){ 
	focused_element = $(":focus")
	$('#alert_text').html(txt);
	jQuery('#msg_title').html(head);
	$('#alertModal').css('z-index',999999999)
	$('#alertModal').modal('show');
}

function go_sign(that,emp,doc){ 
    $('#sign_doc_button').data('container', $(that).parent('td'));
	$('#sign_doc_button').data('emp', emp);
	$('#sign_doc_button').data('doc', doc);
	
		  
	var url=$(that).data('url_doc');
	$('#screenIframe').attr('src',url);
	$('#alertScreen').css('z-index',999999999);
	$('#alertScreen').modal('show');
}

function sign_it(that,emp,doc){
	var emp=$('#sign_doc_button').data('emp');
	var doc=$('#sign_doc_button').data('doc');
	var url="?action=sign_doc&emp="+emp+"&doc="+doc;
	jQuery.ajax({
				url: url,
				success: function (result) {
				  resp = jQuery.parseJSON(result); 
				  if (resp.result === "ok")  {
					$($('#sign_doc_button').data('container')).html(resp.date);
					$('#alertScreen').modal('hide');
					myalert('Info','The document was signed by you at '+resp.date+'.');
				  }
				  else myalert('Alert',resp.message);
				},
				async: false
			});
	
	
}


var acc = document.getElementsByClassName("accordion");
function initAccordeon(){ 
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

