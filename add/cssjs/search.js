var Input = (function(){
    var instance;
    function createInstance() { return new Object; }
    return {
        init: function () {
            if (!instance) {
                instance = createInstance();
            }
            /*instance.current = null;*/
            instance.id = "products-search";
            instance.data = [];
                        
            instance.loaddata = function(main){
                instance.main = main;
                this.map = {};
                var url = "/add/data/product-search.json";
                if (window.XMLHttpRequest) { xmlhttpproducts=new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari */
                } else {  xmlhttpproducts=new ActiveXObject("Microsoft.XMLHTTP"); /* code for IE6, IE5 */}
                xmlhttpproducts.onreadystatechange = function() {   if (xmlhttpproducts.readyState === 4 && xmlhttpproducts.status === 200) { search.input.data2object(JSON.parse(xmlhttpproducts.responseText)); }};
                xmlhttpproducts.open("GET", url, true);
                xmlhttpproducts.send();
            };
            instance.data2object = function(data){
                for(x in data) { 
                    this.map[data[x]["str"]] = data[x];
                    instance.data.push(data[x]["str"]);
                }
                this.typeahead();
            };

            
            instance.typeahead = function(){
                if(typeof Bloodhound !== "undefined" && typeof $().typeahead !== "undefined"){
                //console.log("search.input.typeahead run", this.data);
                    this.list = new Bloodhound({
                      datumTokenizer: Bloodhound.tokenizers.whitespace,
                      queryTokenizer: Bloodhound.tokenizers.whitespace,
                      local: this.data
                    });
                    var fld = "#"+this.id;
                    this.typeaheadFld = $(fld).typeahead({ highlight: true, minLength: 1}, 
                    {
                        name: 'q',
                        source: this.list,
                        /*templates: {
                            header: '<p class="list-name">Matching products:</p>'
                        }*/

                    }/*,
                    {
                        name: 'q',
                        source: this.listattys,
                        templates: {
                            header: '<h3 class="league-name">Attorneys</h3>'
                        }
                    }*/);
                    $(fld).bind('typeahead:select', function(ev, suggestion) {
                        //console.log(suggestion);
                        search.input.set(search.input.map[suggestion], ev.type);
                    });
                    $(fld).bind('blur', function(ev) {
                            /*if($(this).val() && search.input.map[$(this).val()]){
                                if(search.input.current===null || search.input.map[$(this).val()]["id"]!==search.input.current["id"]){
                                    search.input.set(search.input.map[$(this).val()], ev.type);
                                }
                            } else if($(this).val().length>0) {
                                if($(this).val().length>0){
                                    $("#"+search.input.fldid).addClass(searchtop.errorClass);
                                }
                            }*/
                    }).keypress(function(ev) {
                        if(ev.which === 13) {
                            if($(this).val() && search.input.map[$(this).val()]){
                                if(search.input.current===null || search.input.map[$(this).val()]["id"]!==search.input.current["id"]){
                                    search.input.set(search.input.map[$(this).val()], ev.type+" (enter)");
                                }
                            } else {
                                search.input.current = null;
                            }
                        }
                    });
                    $(fld).bind('change', function(ev) {
                            if($(this).val() && search.input.map[$(this).val()]){
                                //console.log(search.input.map[$(this).val()]);
                            } else {
                                search.input.current = null;
                            }
                    });
                } else {
                    setTimeout("search.input.typeahead();", 300);
                }
                //console.log("end","search.input.typeahead();");
            };
            instance.set = function(val, eventName){
                instance.current = val;
                //console.log(instance.current.url+"?highlight-id="+instance.current.id);
                var url = ("/"+val.url+"?highlight-id="+val.id+"#p"+val.id);
                
                var currentUrl = window.location.href.substring((window.location.href.indexOf(window.location.host)+window.location.host.length));
                //console.log(currentUrl, url);
                if(url===currentUrl){ top.location.reload();
                } else {              top.location.assign(url);}
            };
            
            return instance;
        }
    };
})();

var Search = (function(){
    var instance;
    function createInstance() { return new Object; }
    var preload = ["/add/typeahead/bloodhound.min.js",
                   "/add/typeahead/typeahead.jquery.js"];
    loadJsOrCss = function(filename){
        if(filename.substr(filename.length-2)==="js"){ //if filename is a external JavaScript file
            var fileref=document.createElement('script'); fileref.setAttribute("type","text/javascript"); fileref.setAttribute("src", filename);
        } else { //if filename is an external CSS file
            var fileref=document.createElement("link"); fileref.setAttribute("rel", "stylesheet"); fileref.setAttribute("type", "text/css"); fileref.setAttribute("href", filename);
        }
        if (typeof fileref!=="undefined") document.getElementsByTagName("head")[0].appendChild(fileref);
    };

    return {
        init: function () {
            if (!instance) {
                instance = createInstance();
            }
            for (i = 0; i < preload.length; i++) {
                loadJsOrCss(preload[i]);
            }
            instance.input = Input.init();
            if(document.getElementById(instance.input.id)!==null){ //Grow a case where the cat will not be displayed - in ths case the loc come mandatory
                instance.input.loaddata(instance);
            }
            return instance;
        }
    };
})();
var search = Search.init();