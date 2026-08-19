$(document).ready(function() {
    inpCal.setCalsOnPage();
});

var inpCal = new function(){
    
    this.setCalsOnPage = function(){
        $("input[data-calendar]").each(function(){
            inpCal.setCal($(this).attr("name"));
        });
        this.fixGoToToday();
        $("input[data-calendar]").each(function(){
            inpCal.setCalClearWhereAllowed($(this).attr("name"));
        });
    };
    
    this.setCal = function(cName){
        var cId = "#"+cName;
        $(cId).datepicker({
            dateFormat: $("input[name=\""+cName+"\"]").attr("data-format"),
            setDate: $("input[name=\""+cName+"\"]").val(),
            altField: "input[name=\""+cName+"\"]",
            altFormat: "yy-mm-dd",
            changeMonth: true,
            showButtonPanel: true
        }).datepicker( "setDate", $.datepicker.parseDate("yy-mm-dd",$("input[name=\""+cName+"\"]").val())).change(function(){
            if($(cId).val().length<1){ $("input[name=\""+cName+"\"]").val(""); }
        }).keyup(function(e) {
            if(e.keyCode === 8 || e.keyCode === 46) { $.datepicker._clearDate(this); this.blur(); }
        }).after("<span class=\"fa fa-calendar input-group-addon calendar-symbol\" id=\""+cName+"-lnk\" onclick=\"$(\'"+cId+"\').trigger(\'focus\');\" ></span>");
    };
    
    this.fixGoToToday = function(){
        var old_goToToday = $.datepicker._gotoToday;
        $.datepicker._gotoToday = function(id) {
            old_goToToday.call(this,id);
            this._selectDate(id);
            $(id).blur();
        };
    };
    
    this.setCalClearWhereAllowed = function(inp){
        var clearAttr = $("input[name=\""+inp+"\"]").attr("data-clear");
        if (typeof clearAttr !== undefined && clearAttr !== "false") {
            //wrap up the redraw function with our new shiz
            var dpFunc = $.datepicker._generateHTML; //record the original
            $.datepicker._generateHTML = function(inst){
                var thishtml = $( dpFunc.call($.datepicker, inst) ); //call the original
                thishtml = $("<div />").append(thishtml); //add a wrapper div for jQuery context
                //locate the button panel and add our button - with a custom css class.
                if($(".ui-datepicker-buttonpane", thishtml).find(".ui-datepicker-clear").length<1){
                    $(".ui-datepicker-buttonpane", thishtml).append(
                        $("<button class=\"ui-datepicker-clear ui-state-default ui-priority-secondary ui-corner-all\">Clear</button>"
                        ).click(function(){
                            $("input[name=\""+$(inst).attr("id")+"\"]").val("");
                            $("#"+$(inst).attr("id")).val("");
                            inst.input.datepicker("hide").blur();
                        })
                    );
                }
                thishtml = thishtml.children(); //remove the wrapper div
                return thishtml; //assume okay to return a jQuery
            }
        };
    };
    
};

function requireCal(test){
    var id = test.getAttribute("id");
    var value = document.getElementById("hidden-"+id).getAttribute("value");
    return (typeof value !== undefined && value !== "");
}
