if(window.addEventListener){window.addEventListener('load',tagsSetup,false);} else if(window.attachEvent){window.attachEvent('onload',tagsSetup);}

function tagsSetup(){
    tagsFields = document.querySelectorAll(".inptags");
    for(i = 0; i < tagsFields.length; i++){
        inpTags.prepare(tagsFields[i], i);
    }
}

var inpTagsInstances = [];

var inpTags = new function(){
    this.textarea;
    this.select;
    this.valid;
    this.invalid;
    this.counter;
    this.index;
    this.prepare = function(fld, index){
        fld.setAttribute("data-instance",index);
        this.index = index;
        window.inpTagsInstances[index] = this;
        this.textarea = fld.getElementsByTagName("textarea")[0];
        this.prepareTextarea();
        this.select = fld.getElementsByTagName("select")[0];
        fld.getElementsByTagName("select")[0].setAttribute("data-instance",index);
        this.counter = Math.round(this.select.getAttribute("data-counter"));
        var fldSpans = fld.getElementsByTagName("span");
        for(i = 0; i < fldSpans.length; i++){
            if(fldSpans[i].className === "valid-tags"){
                this.valid = fldSpans[i];
                fldSpans[i].setAttribute("data-instance",index)
            } else if(fldSpans[i].className === "invalid-tags"){
                this.invalid = fldSpans[i];
                fldSpans[i].setAttribute("data-instance",index)
            }
        }
    };
    
    this.prepareTextarea = function(){
        this.textarea.setAttribute("data-instance",this.index);
        this.textarea.setAttribute("onkeyup","inpTags.type(this);");
        this.textarea.setAttribute("onblur","inpTags.blur(this);");
    };
    
    this.type = function(fld){
        var instanceNo = fld.getAttribute("data-instance");
        this.getInstance(instanceNo).typeProcess();
    };
    this.typeProcess = function(){
        var value = this.textarea.value.replace(/(\n|\s|;|:|~|<)/g,",");
        if(value.search(",")>=0) {
            if (value.substr(-1)==",") {
                value = value.substr(0,(value.length-1));
            }
        } else {return;}
        this.textarea.value="";
        if(value.length<=0) {return;}
        var values = value.split(",");
        for (i=0; i<values.length; i++){
            var matchingCounter=0;
            var activeOptions = this.select.options;
            var currentValue = values[i];
            for(i = 0; i < activeOptions.length; i++){
                if(activeOptions[i].value.toUpperCase()===currentValue.toUpperCase()){
                    matchingCounter=1;
                    if(activeOptions[i].hasAttribute("selected")===false){
                        activeOptions[i].setAttribute("selected", true);
                        var spanTag = document.createElement("span");
                        spanTag.innerHTML = values[i]+"&nbsp;<a onclick=\"inpTags.removeMe(this,0);\" title=\"Remove\"><i class=\"fa fa-times\"></i></a>";
                        this.valid.appendChild(spanTag);
                    }
                } 
            }
            if(matchingCounter===0) { 
                var optionTag = document.createElement("option"); optionTag.setAttribute("selected",true); optionTag.innerHTML=currentValue;
                this.select.appendChild(optionTag);
                var spanTag = document.createElement("span");
                spanTag.innerHTML = (currentValue+"&nbsp;<a onclick=\"inpTags.removeMe(this,0);\" title=\"Remove\"><i class=\"fa fa-times\"></i></a>");
                this.valid.appendChild(spanTag);
            }
        }
    };
    
    this.blur = function(fld){
        if(fld.value.trim().length>0){
            fld.value+=",";
            var instanceNo = fld.getAttribute("data-instance");
            this.getInstance(instanceNo).typeProcess();
        }
    };
    
    this.removeMe = function(lnk, index){
        var instanceNo = lnk.parentNode.parentNode.getAttribute("data-instance");
        this.getInstance(instanceNo).killMe(lnk, index);
    };
    this.killMe = function(lnk, index){
        this.select.options[index].removeAttribute("selected");
        lnk.parentNode.parentNode.removeChild(lnk.parentNode);
    };
    this.getInstance = function(index){
        return window.inpTagsInstances[index];
    };
};
