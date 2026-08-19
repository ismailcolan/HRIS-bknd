var ShoppingCart = (function(){
    var instance;
    function createInstance() { return new Object; }
    function calcTotals(fields){
        $("#grand-total").hide();
        fields = document.querySelectorAll(".qty .fld input[data-index]");
        var total = 0;
        for (i = 0; i < fields.length; i++) { 
            if(fields[i].hasAttribute("onblur")===false){
                fields[i].setAttribute("onblur", "cart.qtyblur(this);")
                fields[i].setAttribute("onchange", "validateFld(this);")
            }
            var index = fields[i].getAttribute("data-index");
            var qty  = parseInt(fields[i].value.replace(",",""));
            var price  = parseFloat(document.getElementById("price-"+index).innerHTML.replace(",","").replace("$","").trim());
            //console.log(qty, price);
            var itemTotal = qty*price;
            total += itemTotal;
            document.getElementById("total-"+index).innerHTML = instance.price(itemTotal); 
            
        }
        document.getElementById("grand-total").innerHTML = instance.price(total);
        $("#grand-total").fadeIn("slow");
    } 
    return {
        init: function () {
            
            var cartTable = document.getElementById("shopping-cart-table");
            if(cartTable===null || cartTable.getElementsByTagName("td").length===1){return null;}
            
            if (!instance) {instance = createInstance();}
            
            instance.price = function(n){
                //console.log(n, ("$"+n.toFixed(2).replace(/(\d)(?=(?:[0-9]{3})+\b)/gm, ",")), parseFloat(n).toLocaleString('en-US', {style: 'currency', currency: 'USD'}));
                //return ("$"+n.toFixed(2).replace(/(\d)(?=(?:[0-9]{3})+\b)/gm, ",")); -> wriong for >1000
                return parseFloat(n).toLocaleString('en-US', {style: 'currency', currency: 'USD'});
            };
            
            
            instance.qtyblur = function(input){
                if(input.getAttribute("class").indexOf("error")===-1){
                    instance.totals();
                }
            };
            
            instance.totals = function(){
                calcTotals();
            };
            
            calcTotals(); //To run on load
            
            /*instance.validateQty = function(input, cahngeAction){
                var qty = parseInt(input.value.replace(",",""));
                var err = input.parentNode.querySelector(".err");
                if(qty>0 && qty<=1000){
                    err.innerHTML = "";
                    $(input).removeClass("error");
                    return qty;
                } else if(cahngeAction===true && input.value.length===0) { //Onchange
                    $(input).removeClass("error");
                    err.innerHTML = "";
                } else {
                    err.innerHTML = input.value.length>0 ? "Please use integer (1-1,000)": "Please fill out the Qty.";
                    $(input).addClass("error");
                    input.focus();
                    return false;
                }
            };*/
            /*instance.fieldKeyPress = function(input, ev){
                if(ev.which=== 13){
                    var btn = document.querySelector("button[data-for^=\""+input.getAttribute("id")+"\"]");
                    instance.add2cart(btn);
                } else if(ev.which=== 27){
                    input.value="";
                }
            };*/

            return instance;
        }
    };
})();
var cart = ShoppingCart.init();

var Order = (function(){
    var instance;
    function createInstance() { return new Object; }
    return {
        init: function () {

            var shoppingCartTable = document.querySelector(".table.shopping-cart");
            if(shoppingCartTable){
/*
                var pCanWaitSelects =  document.querySelectorAll(".fld.form-group.not-received-can-wait");
                for (i = 0; i < pCanWaitSelects.length; i++) {
                    if(pCanWaitSelects[i].getElementsByTagName("optgroup").length===0){
                        var canWaitNoOptions = pCanWaitSelects[i].querySelectorAll("option[value^='-']");
                        var canWaitOptgroup = document.createElement("optgroup"); canWaitOptgroup.setAttribute("label", "No, I need it:");
                        for (o = 0; o < canWaitNoOptions.length; o++){
                            canWaitOptgroup.appendChild(canWaitNoOptions[o]);
                        }
                        pCanWaitSelects[i].appendChild(canWaitOptgroup);
                    }
                }
*/
                return null;
            }

            var orderTable = document.querySelector(".table.order");
            if(orderTable===null){return null;}
            
            
            if (!instance) {instance = createInstance();}
            
            instance.type = orderTable.getAttribute("data-type");
            
            if(instance.type==="pending"){ //Only when order is pending those must become "disabled"
                var pQty = document.querySelectorAll(".qty.received.pending input.form-control");
                for (i = 0; i < pQty.length; i++) {
                    var sel = document.getElementById("received-"+i);

                    if(sel.options[sel.selectedIndex].value==="full" || sel.options[sel.selectedIndex].value==="{!--Empty--!}"){
                        pQty[i].parentNode.parentNode.style.display="none";
                        
                        //Move all option of the "Can you wait" select starting with "-" to a new optgroup;
                        /*if(document.getElementById("not-received-can-wait-"+i).getElementsByTagName("optgroup").length===0){
                            var canWaitNoOptions = document.getElementById("not-received-can-wait-"+i).querySelectorAll("option[value^='-']");
                            var canWaitOptgroup = document.createElement("optgroup"); canWaitOptgroup.setAttribute("label", "No, I need it:");
                            for (o = 0; o < canWaitNoOptions.length; o++){
                                canWaitOptgroup.appendChild(canWaitNoOptions[o]);
                            }
                            document.getElementById("not-received-can-wait-"+i).getElementsByTagName("select")[0].appendChild(canWaitOptgroup);
                        }*/
                    }
                }
            } else {
                var pQty = document.querySelectorAll(".qty.received input.form-control");
                for (i = 0; i < pQty.length; i++) {
                    if(pQty[i].value===""){
                        pQty[i].value = "0";
                    }
                }
                //console.log(pQty);
            }

            /*
            var pCanWaitSelects =  document.querySelectorAll(".fld.form-group.not-received-can-wait");
            for (i = 0; i < pCanWaitSelects.length; i++) {
                if(pCanWaitSelects[i].getElementsByTagName("optgroup").length===0){
                    var canWaitNoOptions = pCanWaitSelects[i].querySelectorAll("option[value^='-']");
                    var canWaitOptgroup = document.createElement("optgroup"); canWaitOptgroup.setAttribute("label", "No, I need it:");
                    for (o = 0; o < canWaitNoOptions.length; o++){
                        canWaitOptgroup.appendChild(canWaitNoOptions[o]);
                    }
                    pCanWaitSelects[i].getElementsByTagName("select")[0].appendChild(canWaitOptgroup);
                    var matchingReceivedQtyInput = document.getElementById(pCanWaitSelects[i].getAttribute("id").replace("not-received-can-wait-","received-fld-")).getElementsByTagName("input")[0];
                    //Will allow in case of server validation error the field to stay visible
                    if(parseInt(matchingReceivedQtyInput.value)>=parseInt(matchingReceivedQtyInput.getAttribute("data-max"))){ 
                        pCanWaitSelects[i].style.display = "none";
                    }
                }
            }
            */
            
            var pRet = document.querySelectorAll(".qty.returns input.form-control");
            for (i = 0; i < pRet.length; i++) {
                var sel = document.getElementById("returns-"+i);
                //if(pRet[i].value==="0" && pRet[i].hasAttribute("class")===false || pRet[i].getAttribute("class").indexOf("error")===-1){
                if(sel.options[sel.selectedIndex].value==="No"){
                    pRet[i].parentNode.parentNode.style.display="none";
                    var retNotesID = pRet[i].parentNode.parentNode.getAttribute("id").replace("fld","notes");
                    document.getElementById(retNotesID).style.display="none";
                }
            }
            /*Received select (visible only for pending orders)*/
            instance.pendigReceivedChange = function(select){
                var val = select.options[select.selectedIndex].value;
                var fld = document.getElementById("received-fld-"+select.getAttribute("data-index"));
                var fldNotes = document.getElementById("received-notes-"+select.getAttribute("data-index"));
                if(val==="part"){
                    fld.getElementsByTagName("input")[0].removeAttribute("disabled");
                    $(fld).fadeIn();
                    $(fldNotes).fadeIn();
                    fld.getElementsByTagName("input")[0].value = null;
                } else {
                    $(fld).fadeOut();
                    $(fldNotes).fadeOut();
                    fld.getElementsByTagName("input")[0].setAttribute("disabled", 1);                    
                    if(fld.getElementsByTagName("input")[0].value.length===0){
                        fld.getElementsByTagName("input")[0].value = fld.getElementsByTagName("input")[0].getAttribute("data-max");
                    }
                }
            };
            /*Received qty input*/
            instance.ReceivedQtyChange = function(receivedQtyInput){
                var receivedQty = parseInt(receivedQtyInput.value) || 0;
                var receivedQtyDataMax = parseInt(receivedQtyInput.getAttribute("data-max"))
                
                var returnsQtyInput = document.getElementById(receivedQtyInput.getAttribute("name").replace("received-qty-","returns-fld-")).getElementsByTagName("input")[0];
                var returnsQty = parseInt(returnsQtyInput.value) || 0;
                
                var canWaitFldId = "not-received-can-wait-"+receivedQtyInput.getAttribute("name").replace("received-qty-","");
                if(receivedQty<receivedQtyDataMax || returnsQty>0){
                    //document.getElementById(canWaitFldId).getElementsByTagName("select")[0].selectedIndex=0; //Selected come: "Please select"
                    $("#"+canWaitFldId).fadeIn();
                    $("#"+canWaitFldId+">div>select").prop('required',true);
                    $("#"+canWaitFldId+">label").html('Can you wait:<span title="Required field">*</span>');
                } else {
                    //document.getElementById(canWaitFldId).getElementsByTagName("select")[0].selectedIndex=1; //Selected come: "Yes, I can wait, it's not urgent"
                    $("#"+canWaitFldId).fadeOut();
                    $("#"+canWaitFldId+">div>select").prop('required',false);
                    $("#"+canWaitFldId+">label").html('Can you wait:');
                }
                /*var val = select.options[select.selectedIndex].value;
                var fld = document.getElementById("returns-fld-"+select.getAttribute("data-index"));
                var fldNotes = document.getElementById("returns-notes-"+select.getAttribute("data-index"));
                if(val==="Yes"){
                    $(fld).fadeIn();
                    $(fldNotes).fadeIn();
                    if(fld.getElementsByTagName("input")[0].value==="0"){
                        fld.getElementsByTagName("input")[0].value = null;
                    }
                } else {
                    fld.getElementsByTagName("input")[0].value = 0;
                    $(fld).fadeOut();
                    $(fldNotes).fadeOut();
                }*/
            };
            /*Returns select input*/
            instance.ReturnsChange = function(select){
                var val = select.options[select.selectedIndex].value;
                var fld = document.getElementById("returns-fld-"+select.getAttribute("data-index"));
                var fldNotes = document.getElementById("returns-notes-"+select.getAttribute("data-index"));
                if(val==="Yes"){
                    $(fld).fadeIn();
                    $(fldNotes).fadeIn();
                    if(fld.getElementsByTagName("input")[0].value==="0"){
                        fld.getElementsByTagName("input")[0].value = null;
                    }
                } else {
                    fld.getElementsByTagName("input")[0].value = 0;
                    $(fld).fadeOut();
                    $(fldNotes).fadeOut();
                }
            };
            /*Returns qty input*/
            instance.ReturnsQtyChange = function(returnsQtyInput){
                var returnsQty = parseInt(returnsQtyInput.value) || 0;
                
                var receivedQtyInput = document.getElementById(returnsQtyInput.getAttribute("name").replace("returns-qty-","received-fld-")).getElementsByTagName("input")[0];
                var receivedQty = parseInt(receivedQtyInput.value) || 0;
                var receivedQtyDataMax = parseInt(receivedQtyInput.getAttribute("data-max"));
                
                var canWaitFldId = "not-received-can-wait-"+returnsQtyInput.getAttribute("name").replace("returns-qty-","");
                if(receivedQty<receivedQtyDataMax || returnsQty>0){
                    //document.getElementById(canWaitFldId).getElementsByTagName("select")[0].selectedIndex=0; //Selected come: "Please select"
                    $("#"+canWaitFldId).fadeIn();
                    $("#"+canWaitFldId+">div>select").prop('required',true);
                    $("#"+canWaitFldId+">label").html('Can you wait:<span title="Required field">*</span>');
                } else {
                    //document.getElementById(canWaitFldId).getElementsByTagName("select")[0].selectedIndex=1; //Selected come: "Yes, I can wait, it's not urgent"
                    $("#"+canWaitFldId).fadeOut();
                    $("#"+canWaitFldId+">div>select").prop('required',false);
                    $("#"+canWaitFldId+">label").html('Can you wait:');
                }
                /*var val = select.options[select.selectedIndex].value;
                var fld = document.getElementById("returns-fld-"+select.getAttribute("data-index"));
                var fldNotes = document.getElementById("returns-notes-"+select.getAttribute("data-index"));
                if(val==="Yes"){
                    $(fld).fadeIn();
                    $(fldNotes).fadeIn();
                    if(fld.getElementsByTagName("input")[0].value==="0"){
                        fld.getElementsByTagName("input")[0].value = null;
                    }
                } else {
                    fld.getElementsByTagName("input")[0].value = 0;
                    $(fld).fadeOut();
                    $(fldNotes).fadeOut();
                }*/
            };
            
            return instance;
        }
    };
})();
var order = Order.init();

function enableSelectFields() {
    var selects = document.getElementsByTagName("select");
    for(var i = 0; i < selects.length; i++) {
        selects[i].disabled = false;
    }
}

$(function(){
	if ($( "a.img-container" ).length){
		$('a.img-container').magnificPopup({
		  type: 'image'
		});
	}
});