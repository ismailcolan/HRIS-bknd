
var d, map={}, firmsData = [];
var firms = new function(){
    this.changeFirm = function(index){
        console.log("Will change to (ID|Name)",index, d[index]);
        document.getElementById("firm-fld").value = index;
        var changeLink = document.getElementById("firm-change-link");
        document.getElementById("firm-current").innerHTML = d[index].substr(0,d[index].indexOf(","))+"&nbsp;|&nbsp;";
        document.getElementById("firm-current").appendChild(changeLink);
        $('#firm-selector').fadeOut();
        $('#firms-typeahead').val("");
            console.log("changeFirm");        
        this.changedLoadOffices(index);
    };
    
    this.changedLoadOffices = function(id){
        $(document.getElementById("firm-offices")).fadeOut("fast");
        if (window.XMLHttpRequest) { xmlhttp=new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari */
        } else {  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); /* code for IE6, IE5 */}
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                console.log("attempt");
                var newDiv = document.createElement("div");
                newDiv.innerHTML = xmlhttp.responseText;
                var officesHTML = newDiv.getElementsByTagName("div")[2].innerHTML;
                document.getElementById("firm-offices").innerHTML = officesHTML;
                $(document.getElementById("firm-offices")).fadeIn("slow");
                console.log(newDiv.getElementsByTagName("table")[0].getElementsByTagName("tr")[2]);
                var officeID = newDiv.getElementsByTagName("table")[0].getElementsByTagName("tr")[2].getAttribute("data-id");
                document.getElementById("office-active").value = officeID;
                top.offices.init();
            }
        };
        xmlhttp.open("GET", document.getElementById("firm-fld").getAttribute("data-url")+"?firm="+id, true);
        xmlhttp.send();
    };
    
    this.typeahead = function(data){
        for(x in data) { 
            map[data[x]] = x;
            firmsData.push(data[x]);
        }

        var firmsList = new Bloodhound({
          datumTokenizer: Bloodhound.tokenizers.whitespace,
          queryTokenizer: Bloodhound.tokenizers.whitespace,
          // url points to a json file that contains an array of country names, see
          // https://github.com/twitter/typeahead.js/blob/gh-pages/data/countries.json
          local: firmsData
        });

        $('#firms-typeahead').typeahead({ highlight: true, minLength: 1,
        },{ name: 'lFID', source: firmsList, limit: 20 });

        $('#firms-typeahead').bind('typeahead:select', function(ev, suggestion) {
            firms.changeFirm(map[suggestion]);
        });
        $('#firms-typeahead').bind('blur', function(ev) {
            if(map[$(this).val()] >0){
                firms.changeFirm(map[$(this).val()]);
            } else {
                $(this).addClass("error");
            }
        });
        $('#firms-typeahead').bind('focus', function(ev) {
            $(this).removeClass("error");
        });
    };

    this.loadData = function(){
        if (window.XMLHttpRequest) { xmlhttp=new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari */
        } else {  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); /* code for IE6, IE5 */}
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                console.log("loadData");
                d = JSON.parse(xmlhttp.responseText);
                firms.typeahead(d);
            }
        };
        xmlhttp.open("GET", document.getElementById("firm-fld").getAttribute("data-url")+".json?firms", true);
        xmlhttp.send();
   };

    this.init = function(){
        var firmFld = document.getElementById("firm-fld");
        if(typeof firmFld !== undefined){
            this.loadData();
        }
    };
}; 
firms.init();

