var strAsc = String.fromCharCode(9650); //&#9650;
var strDesc = String.fromCharCode(9660);//&#9660;

/** Moves custom created filter to the Table's filter
 * @param {string} filterId
 * @param {string} tableId
 * @param {boolean} delay - needed in case the table is istill not executed */
function moveSelectorToTheTableFilter(filterId, tableId, delay){
    if(delay===true){
        setTimeout(function(){
            filterDiv = document.getElementById(tableId).getElementsByTagName("div")[0];
            filterDiv.appendChild(document.getElementById(filterId));
        },500);
    } else {
        filterDiv = document.getElementById(tableId).getElementsByTagName("div")[0];
        filterDiv.appendChild(document.getElementById(filterId));
    }
}
function changeListCustomFilter(selectObj){
    fId = selectObj.options[selectObj.selectedIndex].value;
    varName = selectObj.getAttribute("name");
    varPos = document.URL.indexOf(varName);
    if(varPos>0){
        url = fId!=="{!--Empty--!}" ? document.URL.substring(0,varPos) : document.URL.substring(0,(varPos-1));
    } else {
        separator = document.URL.indexOf("?")>0 ? "&" : "?";
        url = document.URL + (fId!=="{!--Empty--!}" ? separator : "");
    }
    newUrl = url + (fId!=="{!--Empty--!}" ? (varName + "=" + fId) : "");
    location.assign(newUrl);
}

function tableLoadEndCalback(tableId){}
function tableAppendRowCalback(tableId){}

function tableReloadData(tableId){
    var request = {};
    tableBuildRequest(request,tableId);
    tableLoadData(tableId,request);  
}

function tableBuildRequest(request, crntTableId, skipPropertyArray){
    this.rq = request;
    this.checkSkip = function(skipProperty){
        var result = false;
        if(skipPropertyArray && Object.prototype.toString.call( skipPropertyArray ) === '[object Array]'){
            if(skipPropertyArray.indexOf(skipProperty)>=0){
                result = true;
            }
        }
        return result;
    };
    this.getSort = function(){
        var table = document.getElementById(crntTableId);
        var thTags = table.getElementsByTagName("thead")[0].getElementsByTagName("th");
        for(i = 0; i < thTags.length; i++ ){
            if(thTags[i].getElementsByTagName("a")[0] && thTags[i].getElementsByTagName("a")[0].getElementsByTagName("span").length === 1){
                var spanContent = thTags[i].getElementsByTagName("a")[0].getElementsByTagName("span")[0].innerHTML;
                if(spanContent.length === 1){
                    rq.colNo = i;
                    rq.colOrd = (spanContent === window.strDesc) ? "desc" : "asc";
                }
            }
        }
    };
    this.getFilter = function(){
        var r = getFilterFieldsByTbaleID(crntTableId);
        if(r.filter !== null){
            rq.filter = r.filter;
        }
        if(r.filterBy !== null){
            rq.filterBy = r.filterBy;
        }
    };
    
    /* Build request object */
    if(!this.checkSkip("sort")){            this.getSort();         }
    if(!this.checkSkip("filter")){          this.getFilter();       }
    
    return rq;
}

function tableRequestToUrl(rq){
    var url=location.pathname + ".json" + location.search;
    if(typeof rq === "object"){
        var getUrlVarName = {colNo: "col", colOrd: "ord", filter: "filter", filterBy: "filter-by", pageNo: "pg", exportType: "export"};
        var flagFirst = location.search.length < 1 ? true : false;
        for(r in rq){
            url += (flagFirst===true ? "?" : "&") + getUrlVarName[r] + "=" + rq[r];
            flagFirst = false;
        }
    }
    return url;
}

function tableFilter(filterField){
    var request = {};
    var crntTableId = filterField.tagName.toLowerCase() === "select" ? filterField.parentNode.parentNode.getElementsByTagName("input")[0].getAttribute("data-table-id") : filterField.getAttribute("data-table-id");
    tableBuildRequest(request,crntTableId);
    if(request.filter !== null){
        tableLoadData(crntTableId,request);    
    }
}

function tableGoPage(lnk){
    var request = {};
    var crntTableId = getParent(lnk,"table").getAttribute("id");
    tableBuildRequest(request,crntTableId);
    request.pageNo = isNaN(parseInt(lnk.innerHTML)) ? lnk.parentNode.getAttribute("data-page") : lnk.innerHTML;
    tableLoadData(crntTableId,request);
    return false;
}

function tableExport(lnk,eType){
    var request = {};
    var crntTableId = getParent(lnk,"table").getAttribute("id");
    tableBuildRequest(request,crntTableId);
    request.exportType = ["CSV","Excel"].indexOf(eType)>=0 ? eType : "csv";
    window.open(tableRequestToUrl(request));
    return false;
}

function tableSort(colNo,lnk){
    var request = {};
    var crntTableId = getParent(lnk,"table").getAttribute("id");
    tableBuildRequest(request,crntTableId);
    if(Math.round(colNo) === request.colNo){
        request.colOrd = request.colOrd === "asc" ? "desc" : "asc";
    } else {
        request.colNo=Math.round(colNo);
        request.colOrd = "asc";
    }
    tableLoadData(crntTableId,request);
    /* Clear and add new sort arrow */
    var headSpans = getParent(lnk,"thead").getElementsByTagName("span");
    for(var i=0; i < headSpans.length; i++){
        headSpans[i].innerHTML = "";
    }
    lnk.getElementsByTagName("span")[0].innerHTML = (request.colOrd === "desc" ? window.strDesc : window.strAsc);
}

var xmlhttp; var d;
function tableLoadData(tableContainer,rq){
    if (window.XMLHttpRequest) { xmlhttp=new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari */
    } else {  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); /* code for IE6, IE5 */}
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
            d = JSON.parse(xmlhttp.responseText);
            tableDrawSection(tableContainer,d.body);
            tableDrawSection(tableContainer,d.footer,"tfoot");
            tableLoadEndCalback(tableContainer);
            if(typeof rq === "object"){
                tableColumnHover(tableContainer,rq.colNo);
            }
        }
    };
    xmlhttp.open("GET", tableRequestToUrl(rq), true);
    xmlhttp.send();
}

function tablesLoadData(){
    var tables = document.getElementsByTagName("table");
    for (i = 0; i < tables.length; i++) { 
        if((iePrior(9) ? (typeof tables[i]["data-table"]!== 'undefined') : tables[i].hasAttribute("data-table")) && tables[i].getAttribute("data-table") === "js"){
            tableLoadData(tables[i].id);
            tableSetTheTableColumnsHoverEffect(tables[i].id);
        }
    }
    if(iePrior(10)){
        loadJS("/add/helpers/table/add/json2.js");
    }
    
    if(iePrior(8)){ //can be used to add apropriate tables links modifications
        // loadCSS("/add/helpers/table/add/ie7-and-down.css");
    }
}

function iePrior(v) {var rv=false; if(navigator.appName==='Microsoft Internet Explorer') {var ua=navigator.userAgent; var re=new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})"); if(re.exec(ua)!==null) rv=parseFloat(RegExp.$1); rv=rv<v?true:false; } return rv;}
function loadJS(src){ var s = document.createElement('script'); s.src = src; document.getElementsByTagName('head')[0].appendChild(s);}
function loadCSS(src){ var s = document.createElement('link'); s.href = src; s.rel="stylesheet"; document.getElementsByTagName('head')[0].appendChild(s);}

function tableDrawSection(tableContainer, dt, tSection){
    var currentSection = tSection === "tfoot" ? "tfoot" : "tbody";
    var tSection = document.getElementById(tableContainer).getElementsByTagName(currentSection)[0];
    if(iePrior(9)){
        if(tSection.firstChild){
            while (tSection.firstChild) {
                tSection.removeChild(tSection.firstChild);
            }  
        }
    } else {
        tSection.innerHTML="";
    }
    for(var i=0; i < dt.length; i++) {
        var row = dt[i];
        var tRow = document.createElement("tr");
        for(var cell in row){
            var tCell = document.createElement("td");
            if(typeof row[cell] === "string"){
                tCell.innerHTML = row[cell];
            } else if(typeof row[cell] === "object"){
                for(attr in row[cell]){
                    if(typeof row[cell][attr] === "string"){
                        tCell.innerHTML = row[cell][attr];
                    } else if(typeof row[cell][attr] === "object"){
                        for(v in row[cell][attr]){
                            tCell.setAttribute(v,row[cell][attr][v]);
                        }
                    }
                }
            }
            tRow.appendChild(tCell);
        }
        tSection.appendChild(tRow);
        tableAppendRowCalback(tableContainer);
    }
}

function tableSetTheTableColumnsHoverEffect(tableContainer){
    if(iePrior(9)) {return;}
    var tHcells = document.getElementById(tableContainer).rows[0].cells;
    for(var i=0; i < tHcells.length; i++){
        if(tHcells[i].firstChild.tagName === "A"){
            tHcells[i].firstChild.setAttribute("onmouseover","tableColumnHover('"+tableContainer+"',"+i+");");
            tHcells[i].firstChild.setAttribute("onmouseout","tableColumnHover('"+tableContainer+"');");
        }
    }
}

function tableColumnHover(tableContainer,index){
    if(iePrior(9)) {return;}
    var tRow = document.getElementById(tableContainer).rows;
    index = Math.round(index);
    for(var i=0; i < (tRow.length-1); i++){
        if(index >= 0){
            tRow[i].cells[index].setAttribute("lang","col-hover");
        } else {
            for(var j=0; j < tRow[i].cells.length; j++){
                if(tRow[i].cells[j].lang){
                    tRow[i].cells[j].removeAttribute("lang");
                }
            }
        }
    }
}

function getFilterFieldsByTbaleID(tableID){
    var fields = {filterBy:null, filter:null};
    var filterDiv = getFilterDivByTableIDOrNull(tableID);
    if(filterDiv!==null) {
        var selectObj = filterDiv.getElementsByTagName("select")[0];
        var textObj = filterDiv.getElementsByTagName("input")[0];
        fields.filterBy = (selectObj===null || selectObj.options[selectObj.selectedIndex].value==="all") ? null : selectObj.options[selectObj.selectedIndex].value;
        fields.filter = (textObj===null || textObj.value.length === 0) ? null : encodeURIComponent(textObj.value.trim());
    }
    return fields;
}

function getParent(obj,objType){
    while( obj && obj.tagName !== objType.toUpperCase() ){
        obj = obj.parentNode;
    } return obj;
}

function getFilterDivByTableIDOrNull(tableID){
    var res = null;
    if(document.getElementById(tableID).parentNode.getElementsByTagName("div").length > 0){
        for (i = 0; i < document.getElementById(tableID).parentNode.getElementsByTagName("div").length; i++) { 
            if(document.getElementById(tableID).parentNode.getElementsByTagName("div")[i].getAttribute("class")==="filter"){
                return document.getElementById(tableID).parentNode.getElementsByTagName("div")[i];
            }
        }
        
    }
    return res;
}



/*if(window.addEventListener){window.addEventListener('load',tablesLoadData,false);} else if(window.attachEvent){window.attachEvent('onload',tablesLoadData);}*/