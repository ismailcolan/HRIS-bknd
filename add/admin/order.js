var OrderEdit = (function(){
    var instance;
    function createInstance() { return new Object; }
     
    return {
        init: function () {
            
            if (!instance) {instance = createInstance();}
            
            instance.itemDelOrAdd = function(index){
                var i = parseInt(index);
                var checkbox = document.getElementById("delete-by-index-"+i);
                var dellnk =  document.getElementById("delete-lnk-"+i);
                var qtyInput = document.getElementById("qty-by-index-"+i);
                if(checkbox.checked){
                    checkbox.checked = false;
                    $(dellnk).removeClass("btn-default").addClass("btn-danger");
                    qtyInput.setAttribute("type","text");
                } else {
                    checkbox.checked = true;
                    $(dellnk).removeClass("btn-danger").addClass("btn-default");
                }
                qtyInput.type=(checkbox.checked?"hidden":"text");
                dellnk.getElementsByTagName("i")[0].setAttribute("class",(checkbox.checked?"fa fa-undo":"fa fa-times"));
            };
            
            return instance;
        }
    };
})();
var order = OrderEdit.init();