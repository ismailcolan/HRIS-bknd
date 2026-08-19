var Prices = (function () {
    var instance;
    var root;
    var pboCheck;

    function createInstance() { return new Object; }
    return {
        getInstance: function (product) {
            if (!instance) {
                instance = createInstance();
                root = product;
            }
            instance.selectChange = function(select){
                var value = select.options[select.selectedIndex].value;
                if(value!=="{!--Empty--!}"){
                    document.getElementById("vp-button").focus();
                } 
            };
            instance.add = function(){
                var select = document.getElementById("vp-select");
                var value = select.options[select.selectedIndex].value;
                select.options[select.selectedIndex].setAttribute("disabled", true);
                if(value!=="{!--Empty--!}"){
                    var innerHTML = select.options[select.selectedIndex].innerHTML;
                    select.selectedIndex = 0;
                    
                    var fldSrc = document.getElementById("vp-new").getElementsByTagName("div")[0];
                    var fld = fldSrc.cloneNode(true);
                    var id = parseInt(value);
                    
                    fld.getElementsByTagName("span")[0].innerHTML = innerHTML;
                    fld.querySelector("input[type=\"hidden\"]").setAttribute("name", "vp-vendor["+id+"]"); //Vednors hidden id 
                    fld.querySelector("input[type=\"hidden\"]").setAttribute("data-vid", id); //Vednors hidden id

                    fld.querySelector("input[type=\"text\"]").name += id;
                    var name = fld.querySelector("input[type=\"text\"]").name;
                    
                    fld.getElementsByTagName("div")[0].innerHTML+="<span class=\"err\"></span>";
                    
                    msgs1[name] = {
                        "required" : "Please fill out the vendor price.",
                        "data-re" : "Please use valid number format (1,234.56).",
                        "data-min" : "Vendor price cannot be 0."
                    };
                    
                    document.getElementById("vp-count").value = parseInt(document.getElementById("vp-count").value)+1;
                    
                    var added = document.getElementById("vp-added");
                    added.insertBefore(fld, added.childNodes[0]);
                    
                    var activeSelect = document.getElementById("vp-active-vendor-id");
                    var newActive = document.createElement("option"); newActive.setAttribute("value", id); newActive.innerHTML = innerHTML;
                    activeSelect.appendChild(newActive);
                    
                    /*var formField = document.querySelector("form[data-no=\"1\"]").querySelector("input[name=\""+name+"\"]");
                    //console.log(document.querySelector("form[data-no=\"1\"]").querySelector("input[name=\""+name+"\"]"));
                    //validateFld(document.querySelector("form[data-no=\"1\"]").querySelector("input[name=\""+name+"\"]"));
                    //formField.setAttribute("onchange", "validateFld(this)");
                    console.log(document.querySelector("form[data-no=\"1\"]").elements);
                    formField.focus();*/
                    
                    fld.querySelector("input[type=\"text\"]").focus();
                    
                    //Hide vp-count error if any
                    document.getElementById("vp-fld").querySelector(".err").innerHTML = "";
                    //console.log(fld, id);
                } else {
                    select.focus();
                }
            };
            instance.rem = function(lnk){
                //Need to restore disabled select
                var priceDiv = lnk.parentNode.parentNode;
                var vid = parseInt(lnk.parentNode.querySelector("input[type='hidden']").getAttribute("data-vid"));
                
                var select = document.getElementById("vp-select");
                select.querySelector("option[value='"+vid+"']").removeAttribute("disabled");
                
                var activeSelect = document.getElementById("vp-active-vendor-id");
                activeSelect.removeChild(activeSelect.querySelector("option[value='"+vid+"']"));
                    
                priceDiv.parentNode.removeChild(priceDiv);
                document.getElementById("vp-count").value = parseInt(document.getElementById("vp-count").value)-1;
                
                if(activeSelect.options.length>1){
                    instance.setActive();
                }
            };
            instance.keydown = function(priceFld, e){
                key = e.charCode || e.keyCode || 0;
                if (key === 13) {
                    document.getElementById("vp-select").focus();
                    e.preventDefault();
                    return false;
                }
            };
            instance.blur = function(priceFld){
                if(parseFloat(priceFld.value.replace(",",""))===0){
                    priceFld.value="";
                    priceFld.setAttribute("class", priceFld.getAttribute("class")+" error");
                    
                    var activeSelect = document.getElementById("vp-active-vendor-id");
                    activeSelect.selectedIndex = activeSelect.querySelector("option[value='{!--Empty--!}']").index;
                    
                    //priceFld.focus();
                    setTimeout(function() {
                        priceFld.parentNode.querySelector(".err").innerHTML = "Price must be positive number.";
                    }, 800/*1500*/);
                    return false;
                } else if(priceFld.value.length>0) {
                    instance.setActive();
                }
            };
            instance.setActive = function(){
                var pricesFields = document.querySelectorAll("#vp-added input[type='text']");
                var minPrice = null;
                var activeVendorId = null;
                for (f = 0; f < pricesFields.length; f++){
                    var priceAsFloat = parseFloat(pricesFields[f].value.replace(",",""));
                    if(minPrice===null || priceAsFloat<minPrice){
                        minPrice = parseFloat(pricesFields[f].value.replace(",",""));
                        activeVendorId = parseInt(pricesFields[f].parentNode.querySelector("input[type='hidden']").getAttribute("data-vid"));
                    }
                }

                var activeSelect = document.getElementById("vp-active-vendor-id");
                var countPricesMatchMinimal = 0;
                for(c = 0; c < activeSelect.options.length; c++){
                    if(activeSelect.options[c].value!=="{!--Empty--!}"){
                        var price = document.querySelector("#vp-added [name='vp-vendor-price-"+activeSelect.options[c].value+"']").value.replace(",","");
                        if(parseFloat(price)>minPrice){
                            //activeSelect.options[c].setAttribute("disabled", true);
                            if(activeSelect.options[c].hasAttribute("data-minimal")){
                                activeSelect.options[c].removeAttribute("data-minimal");
                            }
                        } else {
                           /* if(activeSelect.options[c].hasAttribute("disabled")){
                                activeSelect.options[c].removeAttribute("disabled");
                            }*/
                            countPricesMatchMinimal++;
                        }
                    }
                }
                if(countPricesMatchMinimal===1){
                    activeSelect.removeAttribute("value");
                    var newIndex = activeSelect.querySelector("option[value='"+activeVendorId+"']").index;
                    if(newIndex != activeSelect.selectedIndex) {
                        activeSelect.selectedIndex = newIndex;
                        var pboCheck = document.getElementById("pPossibleBackorder");
                        pboCheck.checked = false;
                    }
                    activeSelect.parentNode.querySelector(".err").innerHTML = "";
                } else {
                    activeSelect.selectedIndex = activeSelect.querySelector("option[value='{!--Empty--!}']").index;
                }
            };
            
            return instance;
        }
    };
})();

var product = new function(){
    this.init = function(){
        this.prices = Prices.getInstance(this);
    };
    this.changeActivePrice = function(obj) {
        var pboCheck = document.getElementById("pPossibleBackorder");
        pboCheck.checked = false;
    }
};
product.init();

$(function(){
	if ($( "a.img-container" ).length){
		$('a.img-container').magnificPopup({
		  type: 'image'
		});
	}
});
