if(window.addEventListener){window.addEventListener('load',ckeditorInitTextareas,false);
} else if(window.attachEvent){window.attachEvent('onload',ckeditorInitTextareas);}

function ckeditorInitTextareas(){
    var textAreas = document.getElementsByTagName("textarea");
    if(textAreas.length>0){
        for(i = 0; i < textAreas.length; i++){
            var ck = textAreas[i].getAttribute("data-ckeditor");
            if(ck!==null){
                var req = false;
                if(typeof textAreas[i].getAttribute("required") === "string"){ 
                    var req = true;
                } 
                if(ck==="true"){
                    var ck = CKEDITOR.replace(textAreas[i]);
                } else {
                    var ck = CKEDITOR.replace(textAreas[i],eval("("+ck+")"));        
                }
                if(req === true){
                    ck.on("change",function(event){ //validateFld(this);
                        if(event.editor.getData().length <=0){
                            /*
                            varTa = event.editor.element.$;
                            msg = window["msgs"+varTa.form.getAttribute("data-no")][varTa.getAttribute("name")]["required"];
                            varTa.nextSibling.nextSibling.innerHTML = msg;
                            addTempClass(varTa.nextSibling.nextSibling,"blink");
                            varTa.setAttribute("data-minlength","2");
                            var fField = new Field(varTa.nextSibling, varTa.form);
                            console.log(fField);
                            */
                        }
                    });
                }
            }
        }
    }
    
}
