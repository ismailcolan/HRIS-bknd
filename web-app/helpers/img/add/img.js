//Function needed for required validation
function requireImg(el, name, id){
    var uploadedImg = el.parentNode.getElementsByTagName("img")[0].src;
    result = uploadedImg.length>0 ? true : false;
    return result;
}


function prepareImageBeforeCorp(ctrl, num, crop_type) {
    //var fileName = ctrl.getAttribute("value");
    var fileName = ctrl.value;
    if(fileName !== '') {	
        var ctrl_name = ctrl.getAttribute('id').replace("inut-file-","");
        var crop_url = ctrl.getAttribute('data-process-url');
        str = "&settings="+ctrl.getAttribute('data-settings');
        fileUpload(ctrl, crop_url+'?cmd=prepare&cname=' + ctrl_name + '&crtl=' + ctrl_name + str + '&num=' + num, ctrl_name, crop_type);					

        ctrl.value="";
        if(ctrl.hasAttribute("class")){
            if(ctrl.getAttribute("class") === "error"){     ctrl.removeAttribute("class");
            } else {                                        ctrl.setAttribute("class",ctrl.getAttribute("class").replace("error","")); }
        }
        ctrl.nextSibling.innerHTML = "";
        var thumbImg = document.getElementById("gallery-img-"+ctrl.name);
        var imgName = document.getElementById("image-name-"+ctrl.name);
        thumbImg.style.display = "none";
        if(thumbImg.hasAttribute("src")){
            thumbImg.setAttribute("data-old",thumbImg.getAttribute("src"));
            imgName.setAttribute("data-old",imgName.getAttribute("value"));
        } else {
            thumbImg.setAttribute("data-old","false");
        }
    }
}

function cancelCrop(ctrl, crop_type){
    if(crop_type === "inline"){
        document.getElementById("inline-crop-div-"+ctrl).innerHTML = "";
    } else if(crop_type === "fancybox"){
        $.fancybox.close();
    }
    var thumbImg = document.getElementById("gallery-img-"+ctrl);
    var imgName = document.getElementById("image-name-"+ctrl);
    if(thumbImg.hasAttribute("data-old")){
        if(thumbImg.getAttribute("data-old")==="false"){
            thumbImg.style.display = "none";
            thumbImg.removeAttribute("src");
            imgName.removeAttribute("value");
        } else {
            thumbImg.setAttribute("src",thumbImg.getAttribute("data-old"));
            imgName.setAttribute("value",imgName.getAttribute("data-old"));
        }
    }
}

function showUploadedImage(ctrl, fileName, url, maxHeight){
    croperror = null;
    document.getElementById("image-name-"+ctrl).setAttribute("value",fileName);
    var img = document.getElementById("gallery-img-"+ctrl);
    if(img !== null){
        img.setAttribute("src",url+"?r="+Math.random());
        img.style.maxHeight = maxHeight + "px";
        img.style.display = "inline";
    }
}

function showUploadedImagePop(lnk, crop_type){
    var img=lnk.getElementsByTagName("img")[0];
    var url = img.getAttribute("src");
    if(crop_type!=="fancybox"){
        var l = (screen.width/2)-(+img.naturalWidth/2);
        var t = (screen.height/2)-(+img.naturalHeight/2)-100;
        var win = window.open(url, "preview", "width="+img.naturalWidth+", height="+img.naturalHeight+", location=no, left="+l+", top="+t);
        win.onblur = function() { this.close(); };
    } else {
        $.fancybox({'href':url});
    }
}

function fileUpload(ctrl, action_url, ctrl_name, crop_type) {
    var iframe_id = 'upload-iframe-' + ctrl_name;		
    var div_id = 'iframe-messages-' + ctrl_name;
    var image_id = 'image-name-' +  ctrl_name;
    var crop_url = ctrl.getAttribute('data-process-url');
    var str = "&settings="+ctrl.getAttribute('data-settings');
    var form = ctrl.form;

    // Create the iframe...
    var iframe = document.createElement("iframe");
    iframe.setAttribute("id", iframe_id);
    iframe.setAttribute("name", iframe_id);
    iframe.setAttribute("style", "display:none;");

    // Add to document...
    form.parentNode.appendChild(iframe);
    window.frames[iframe_id].name = iframe_id;
    iframeId = document.getElementById(iframe_id);

    // Add event...
    var eventHandler = function () {
        if (iframeId.detachEvent) { iframeId.detachEvent("onload", eventHandler);
        } else  {                   iframeId.removeEventListener("load", eventHandler, false); }
        // Message from server...
        if (iframeId.contentDocument) {         content = iframeId.contentDocument.body.innerHTML;
        } else if (iframeId.contentWindow) {    content = iframeId.contentWindow.document.body.innerHTML;
        } else if (iframeId.document) {         content = iframeId.document.body.innerHTML; }
        document.getElementById(div_id).innerHTML = content;

        if(document.getElementById(image_id).getAttribute("value") !== "") {
            crop_url +='?cmd=crop&cname=' + ctrl_name + '&pd=' + document.getElementById(image_id).getAttribute("value") + str;
            if(crop_type==="fancybox") {
                $.fancybox({
                    'width'				: '95%',
                    'height'			: '95%',
                    'maxWidth'          : 1000,
                    'maxHeight'         : 800,
                    'autoScale'     	: true,
                    'type'				: 'iframe',
                    'locked'            : 'true',
                    'href'				: (crop_url)
                });
            } else if(crop_type==="inline") {
                var outerDivId = "inline-crop-div-"+ctrl_name;
                if(document.getElementById(outerDivId) === null) {
                    var cropOuterDiv = document.createElement("div"); cropOuterDiv.setAttribute("id",outerDivId);
                    var cropDiv = document.createElement("div"); cropOuterDiv.appendChild(cropDiv);
                    if (document.getElementById(div_id).nextSibling) {
                        document.getElementById(div_id).parentNode.insertBefore(cropOuterDiv, document.getElementById(div_id).nextSibling);
                    } else {
                        document.getElementById(div_id).parentNode.appendChild(cropOuterDiv);
                    }
                }
                loadCropDoc(crop_url,function(){
                    if (crophttp.readyState===4 && crophttp.status===200 && croperror!==true) {
						document.getElementById("inline-crop-div-"+ctrl_name).innerHTML=crophttp.responseText; 
                        scrollToDiv(outerDivId);
                        
                    } 
                });
            }
        } 
        // Del the iframe...
        setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
    }
    if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
    if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);

    var oldaction = form.getAttribute('action');

    var oldtarget = form.getAttribute('target');

    //var oldmethod = form.getAttribute('method');
    // Set properties of form...
    form.setAttribute("target", iframe_id);
    form.setAttribute("action", action_url);
    form.setAttribute("method", "post");
    form.setAttribute("enctype", "multipart/form-data");
    form.setAttribute("encoding", "multipart/form-data");

    // Submit the form...
    form.submit();
    if(oldaction!==null){
        form.setAttribute("action", oldaction);
    } else {
        form.removeAttribute("action");
    }
    if(oldtarget !== null) {
        form.setAttribute("target", oldtarget);
    } else {
        form.removeAttribute("target");
    }
    document.getElementById(div_id).innerHTML = "<span id=\"message-green\"><span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Please wait while the image is uploaded and processed...</span>";			
	//window.location = window.location+'#image_begin';
	//scrollToDiv('image_begin');
}  

var crophttp, croperror; 
function loadCropDoc(url,cfunc) {
    if (window.XMLHttpRequest) { crophttp=new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari*/
    } else { crophttp=new ActiveXObject("Microsoft.XMLHTTP"); /* code for IE6, IE5 */ }
    crophttp.onreadystatechange=cfunc;
    crophttp.open("GET",url,true);
    crophttp.send();
}
function scrollToDiv(divId){
    var elmnt = document.getElementById(divId);
    elmnt.scrollIntoView();
}

function SubmitInlineCrop(url, ctrl){
    if (window.XMLHttpRequest) { crophttp=new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari*/
    } else { crophttp=new ActiveXObject("Microsoft.XMLHTTP"); /* code for IE6, IE5 */ }
    crophttp.onreadystatechange=function(){
                        if (crophttp.readyState===4 && crophttp.status===200) { 
                            var obj = JSON.parse(crophttp.responseText);
                            showUploadedImage(obj["ctr"],obj["file"],obj["img"],obj["maxHeight"]);
                            var outerDiv = document.getElementById("inline-crop-div-"+ctrl);
                            outerDiv.parentNode.removeChild(outerDiv);
                        }
                };
    crophttp.open("POST",url,true);
	//alert(url);
    crophttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    var varsStr = "x1="+document.getElementById("x1").value+"&y1="+document.getElementById("y1").value+"&w="+document.getElementById("w").value+"&h="+document.getElementById("h").value+"&ratio="+document.getElementById("ratio").value;
    //alert(varsStr);console.log(varsStr);
	crophttp.send(varsStr);
    scrollToDiv('image_begin');
}


/* Crop page functions */

function SetRatio(img){
    var imgId = img.getAttribute("id");
    var size = img.clientWidth;
    var originalWidth = img.getAttribute("data-original-width");
    var ratio = (originalWidth/size);
    
    var x1Init = 0;
    var y1Init = 0;
    
    var x2Init = document.getElementById("x2").value/ratio;
    document.getElementById("x2").value = x2Init;
    var y2Init = document.getElementById("y2").value/ratio;
    document.getElementById("y2").value = y2Init;
    var w = document.getElementById("w").value/ratio;
    document.getElementById("w").value = w;
    var h = document.getElementById("h").value/ratio;
    document.getElementById("h").value = h;
    document.getElementById("ratio").value = ratio;

    //Convert init selection to pre-select maxPossible image piece
    if(img.clientWidth/w < img.clientHeight/h){
        maxRatio = img.clientWidth/w;
        x2Init = img.clientWidth;
        y2Init = h*maxRatio;
        var addToCenterVal = (img.clientHeight - h*maxRatio)/2;
        y1Init += addToCenterVal;
        y2Init += addToCenterVal;
    } else {
        maxRatio = img.clientHeight/h
        x2Init = w*maxRatio;
        y2Init = img.clientHeight;
        var addToCenterVal = (img.clientWidth - w*maxRatio)/2;
        x1Init += addToCenterVal;
        x2Init += addToCenterVal;
    }

    //runCrop(imgId, 0, 0,(x2Init*maxPossibleRatio),(y2Init*maxPossibleRatio), w, h,(w*ratio),(h*ratio));
    runCrop(imgId, x1Init, y1Init, x2Init, y2Init, w, h,(w*ratio),(h*ratio));
}

function runCrop(imgId, x1Init, y1Init, x2Init, y2Init, wInit, hInit, minWidth, minHeight){
    new Cropper.Img(
        imgId , {
            onEndCrop: onEndCrop,
            displayOnInit: true,
            onloadCoords: { x1: x1Init, y1: y1Init, x2: x2Init, y2: y2Init },
            ratioDim: { x: wInit, y: hInit },
            minWidth: minWidth / 2,
            minHeight: minHeight / 2,

            // OLD:
            // minWidth: minWidth,
            // minHeight: minHeight,
        }
    )
}

function onEndCrop( coords, dimensions ) {
    var test = document.getElementById( "x1" );
    if(test!== null) {
        document.getElementById( "x1" ).value = coords.x1;
        document.getElementById( "y1" ).value = coords.y1;
        document.getElementById( "x2" ).value = coords.x2;
        document.getElementById( "y2" ).value = coords.y2;
        document.getElementById( "w" ).value = dimensions.width;
        document.getElementById( "h" ).value = dimensions.height;
    }
}

/* End Crop page functions */

function crop_image_component_select_main(n1, num1) {			
    var name_arr = n1.split("--");			
    var selected_ctrl = $("input[name^='chk-main-img-" + n1 + "']");
    var flag_checked = selected_ctrl.is(':checked');
    var flag_cover_visible = $("#link-gallery-img-" + n1).is(":visible");						
    ($("input[name^='chk-main-img-" + name_arr[0] + "']").attr('checked', false));			
    if(flag_checked && flag_cover_visible) {
        selected_ctrl.attr('checked', true); 				
    } else {
        for(k = 0; k < num1; k++) {
            //console.log("#link-gallery-img-" + name_arr[0] + '--' + k);
            if($("#link-gallery-img-" + name_arr[0] + '--' + k).is(":visible")) {
                ($("input[name^='chk-main-img-" + name_arr[0] + "--" + k + "']").attr('checked', true));
                break;
            }
        }
    }
}
		
function crop_image_component_delete(n1, num1){						
    $('#gallery-img-' + n1).hide();										
    $('#link-gallery-img-' + n1).hide();			
    $('#image-name-' + n1).val('');
    $('#old_image_name_' + n1).val('');

    var flag_found = false;
    var num_found = 0;
    var name_arr = n1.split("--");
    for(k = 0; k < num1; k++) {
        var t1 = name_arr[0] + "--" + k + "-cover";
        var t2 = 'image-name-' + name_arr[0] + "--" + k;
        var t3 = 'old_image_name_' + name_arr[0] + "--" + k;			

        if($("#" + t2).val() !== "") {
            num_found++;
            $("#" + t1).css("display","none");
        } else if($("#" + t3).length > 0 && $("#" + t3).val() !== "") { 
            num_found++;
            $("#" + t1).css("display","none");					
        } else if(flag_found) {
            $("#" + t1).css("display","none");
        } else {
            $("#" + t1).css("display","block");
            flag_found = true;
        }												
    }

    $("#number-" + name_arr[0]).html(num_found + ' / ' + num1);			

    //check old id 
    crop_image_component_select_main(n1, num1);
}
