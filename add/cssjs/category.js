var Products = (function(){
    var instance;
    function createInstance() { return new Object; }
    function prepareFieldsAndButtons(){
        var btns = document.querySelectorAll("button[data-for^=\"qty\"]");
        for (i = 0; i < btns.length; i++) { 
            btns[i].setAttribute("onclick","product.add2cart(this);");
            var input = document.getElementById(btns[i].getAttribute("data-for"));
            input.setAttribute("onblur", "product.validateQty(this, true);");
            input.setAttribute("onkeyup", "product.fieldKeyPress(this, event);");
        }
        
    } 
    return {
        init: function () {
            if (!instance) {
                instance = createInstance();
                instance.productId = -1;
                instance.productQty = 0;
            }
            prepareFieldsAndButtons();
            
            instance.add2cart = function(btn){

                var trId = btn.parentElement.parentElement.id;
                var possibleBackend = document.querySelector("#"+trId+" > td > span.possible-backorder");

                var pid = parseInt(btn.getAttribute("data-for").split("-")[1]);
                var qty = instance.validateQty(document.getElementById(btn.getAttribute("data-for")));
                document.getElementById(btn.getAttribute("data-for")).value="";
                if(pid>0 && qty>0){
                    instance.productId = pid;
                    instance.productQty = qty;
                    if(possibleBackend) {
                        $('#select-can-wait').modal();
                    } else {
                        this.add2cardProcess(null);
                    }
                }
            }
            instance.getSelectedCanYouWait = function () {
                var optionsList = document.getElementsByName('can-you-wait');
                for(i=0; i<optionsList.length; i++) {
                    if(optionsList[i].checked) return optionsList[i].value;
                }
                return null;
            }
            instance.add2cardProcess = function(canYouWait) {
                var pid = instance.productId;
                var qty = instance.productQty;
                var url = document.URL.split('?').shift()+"?addtocart=true&pid="+pid+"&qty="+qty+"&canYouWait="+canYouWait;
                if (window.XMLHttpRequest) { xmlhttpproducttocart=new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari */
                } else {  xmlhttpproducttocart=new ActiveXObject("Microsoft.XMLHTTP"); /* code for IE6, IE5 */}
                xmlhttpproducttocart.onreadystatechange = function() {   if (xmlhttpproducttocart.readyState === 4 && xmlhttpproducttocart.status === 200) { product.updateCart(JSON.parse(xmlhttpproducttocart.responseText)); }};
                xmlhttpproducttocart.open("GET", url, true);
                xmlhttpproducttocart.send();
            }
            instance.validateQty = function(input, cahngeAction){
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
                    err.innerHTML = input.value.length>0 ? "Please use integer (1-1,000)": "Please enter quantity.";
                    $(input).addClass("error");
                    input.focus();
                    return false;
                }
            };
            instance.fieldKeyPress = function(input, ev){
                if(ev.which=== 13){
                    var btn = document.querySelector("button[data-for^=\""+input.getAttribute("id")+"\"]");
                    instance.add2cart(btn);
                } else if(ev.which=== 27){
                    input.value="";
                }
            };
            instance.updateCart = function(jsonresp){
                var resp = jsonresp[0];
                if(parseInt(resp.index)>=0){
                    /*
                    $('html, body').animate({
                        'scrollTop' : $(".cart_anchor").position().top
                    });
                    $("#cart-items-count").hide();
                    document.getElementById("cart-items-count").innerHTML = (parseInt(resp.index)+1);
                    //Select item image and pass to the function
                    var itemImg = $("[data-for='qty-"+resp.id+"']").parent().parent().find('img').eq(0);
                    flyToElement($(itemImg), $('.cart_anchor'));
                    
                    $("#cart-items-count").delay(600).fadeIn();
                    */
                    window.location.assign("/shopping-cart");
                } else {
                    console.log(jsonresp);
                }
            };
            
            /*instance.input = Input.init();
            if(document.getElementById(instance.input.id)!==null){ //Grow a case where the cat will not be displayed - in ths case the loc come mandatory
                instance.input.loaddata(instance);
            }*/
            return instance;
        }
    };
})();
var product = Products.init();

$(function(){
	if ($( "a.img-container" ).length){
		$('a.img-container').magnificPopup({
		  type: 'image'
		});
	}
	
	/*$('.add-to-cart').on('click',function(){
        //Scroll to top if cart icon is hidden on top
        $('html, body').animate({
            'scrollTop' : $(".cart_anchor").position().top
        });
        //Select item image and pass to the function
        var itemImg = $(this).parent().parent().find('img').eq(0);
		//console.log(itemImg);
        flyToElement($(itemImg), $('.cart_anchor'));
    });*/
});

function flyToElement(flyer, flyingTo) {
    var $func = $(this);
    var divider = 3;
    var flyerClone = $(flyer).clone();
    $(flyerClone).css({position: 'absolute', top: $(flyer).offset().top + "px", left: $(flyer).offset().left + "px", opacity: 1, 'z-index': 1000});
    $('body').append($(flyerClone));
    var gotoX = $(flyingTo).offset().left + ($(flyingTo).width() / 2) - ($(flyer).width()/divider)/2;
    var gotoY = $(flyingTo).offset().top + ($(flyingTo).height() / 2) - ($(flyer).height()/divider)/2;
     
    $(flyerClone).animate({
        opacity: 0.4,
        left: gotoX,
        top: gotoY,
        width: $(flyer).width()/divider,
        height: $(flyer).height()/divider
    }, 700,
    function () {
        $(flyingTo).fadeOut('fast', function () {
            $(flyingTo).fadeIn('fast', function () {
                $(flyerClone).fadeOut('fast', function () {
                    $(flyerClone).remove();
                });
            });
        });
    });
}