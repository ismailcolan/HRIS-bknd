var strAsc = String.fromCharCode(9660); //&#9650;
var strDesc = String.fromCharCode(9650);//&#9660;
var xmlhttp; var d;

var table = new function(){
    this.rq = null;
    
    this.ReloadData = function(tableId){
        var request = {};
        this.BuildRequest(request,tableId);
        this.LoadData(tableId,request);  
    }; 
    
    this.BuildRequest = function(request, crntTableId, skipPropertyArray){
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
                        this.rq.colNo = i;
                        this.rq.colOrd = (spanContent === window.strDesc) ? "desc" : "asc";
                    }
                }
            }
        };
        this.getFilter = function(){
            var r = this.getFilterFieldsByTbaleID(crntTableId);
            if(r.filterBy !== null){
                this.rq.filterBy = r.filterBy;
            }
            if(r.filterByLabel !== null){
                this.rq.filterByLabel = r.filterByLabel;
                if(r.filterByLabel === "Possible backorder") {
                    if(r.filter === null) r.filter = "Yes";
                }
            }
            if(r.filter !== null){
                this.rq.filter = r.filter;
            }
            //console.log(r);
        };

        /* Build request object */
        if(!this.checkSkip("sort")){            this.getSort();         }
        if(!this.checkSkip("filter")){          this.getFilter();       }

        this.rq.tableId = crntTableId;
        return this.rq;
    };

    this.RequestToUrl = function(rq){
        var url=location.pathname + ".json" + location.search;
        if(typeof rq === "object"){
            var getUrlVarName = {colNo: "col", colOrd: "ord", filter: "filter", filterBy: "filter-by", pageNo: "pg", exportType: "export", tableId: "table-id"};
            var flagFirst = location.search.length < 1 ? true : false;
            for(r in rq){
                url += (flagFirst===true ? "?" : "&") + getUrlVarName[r] + "=" + rq[r];
                flagFirst = false;
            }
        }
        return url;
    }

    this.Filter = function(filterField){
		var filter_name=filterField.getAttribute("name");
        var request = {};
		if (filter_name == 'filter-report'){
			request.filter = filterField.value;
			request.tableId='employee_onboarding-table';
			crntTableId = filterField.getAttribute("data-table-id");
		}
		else {
			var crntTableId = filterField.tagName.toLowerCase() === "select" 
			? filterField.parentNode.parentNode.getElementsByTagName("input")[0].getAttribute("data-table-id") 
			: 
			filterField.getAttribute("data-table-id");
			this.BuildRequest(request,crntTableId);
		}
		
		if(request.filter !== null){
			this.LoadData(crntTableId,request);    
		}		
    };

    this.GoPage = function(lnk){
        var request = {};
        var crntTableId = this.getParent(lnk,"table").getAttribute("id");
        this.BuildRequest(request,crntTableId);
        request.pageNo = isNaN(parseInt(lnk.innerHTML)) ? lnk.parentNode.getAttribute("data-page") : lnk.innerHTML;
        this.LoadData(crntTableId,request);
        return false;
    };

    this.Export = function(lnk,eType){
        var request = {};
        var crntTableId = this.getParent(lnk,"table").getAttribute("id");
        this.BuildRequest(request,crntTableId);
        request.exportType = ["CSV","Excel"].indexOf(eType)>=0 ? eType : "csv";
        window.open(this.RequestToUrl(request));
        return false;
    }

    this.Sort = function(colNo,lnk){
        var request = {};
        var crntTableId = this.getParent(lnk,"table").getAttribute("id");
        this.BuildRequest(request,crntTableId);
        if(Math.round(colNo) === request.colNo){
            request.colOrd = request.colOrd === "asc" ? "desc" : "asc";
        } else {
            request.colNo=Math.round(colNo);
            request.colOrd = "asc";
        }
		
        this.LoadData(crntTableId,request);
		
        /* Clear and add new sort arrow */
        var headSpans = this.getParent(lnk,"thead").getElementsByTagName("span");
        for(var i=0; i < headSpans.length; i++){
            headSpans[i].innerHTML = "";
        }
        lnk.getElementsByTagName("span")[0].innerHTML = (request.colOrd === "desc" ? window.strDesc : window.strAsc);
    };
    
    this.DrawSection = function(tableContainer, dt, tSection){
		
        var currentSection = tSection === "tfoot" ? "tfoot" : "tbody";
        var tSection = document.getElementById(tableContainer).getElementsByTagName(currentSection)[0];
        if(this.iePrior(9)){
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
                if(typeof row[cell] === "string" || typeof row[cell] === "number" ){
					var tCell = document.createElement("td");
                    tCell.innerHTML = row[cell];
					tRow.appendChild(tCell);
                } else if(typeof row[cell] === "object" && window._drowObject ){
					var tCell = document.createElement("td");
                    for(attr in row[cell]){
                        if(typeof row[cell][attr] === "string"){
                            tCell.innerHTML = row[cell][attr];
                        } else if(typeof row[cell][attr] === "object"){
                            for(v in row[cell][attr]){
                                tCell.setAttribute(v,row[cell][attr][v]);
                            }
                        }
                    }
					tRow.appendChild(tCell);
                }
				
                
            }
            tSection.appendChild(tRow);
            this.AppendRowCalback(tableContainer);
        }
    };

    this.SetTheTableColumnsHoverEffect = function (tableContainer){
        if(this.iePrior(9)) {return;}
        var tHcells = document.getElementById(tableContainer).rows[0].cells;
        for(var i=0; i < tHcells.length; i++){
            if(tHcells[i].firstChild.tagName === "A"){
                tHcells[i].firstChild.setAttribute("onmouseover","table.ColumnHover('"+tableContainer+"',"+i+");");
                tHcells[i].firstChild.setAttribute("onmouseout","table.ColumnHover('"+tableContainer+"');");
            }
        }
    };

    this.ColumnHover = function(tableContainer,index){
        if(this.iePrior(9)) {return;}
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
    };

    this.getFilterFieldsByTbaleID = function(tableID){
        var fields = {filterBy:null, filterByLabel:null, filter:null};
        var filterDiv = this.getFilterDivByTableIDOrNull(tableID);
        if(filterDiv!==null) {
            var selectObj = filterDiv.getElementsByTagName("select")[0];
            var textObj = filterDiv.getElementsByTagName("input")[0];
			if (! textObj ) {
				textObj = selectObj; // PV
				selectObj = null;
			}
			
            fields.filterBy = (selectObj===null || selectObj.options[selectObj.selectedIndex].value==="all") ? null : selectObj.options[selectObj.selectedIndex].value;
            fields.filterByLabel = (selectObj===null) ? null : selectObj.options[selectObj.selectedIndex].innerHTML;
            fields.filter = 
			(textObj===null 
			|| textObj.value.length === 0) 
			? 
			null : 
			encodeURIComponent(textObj.value.trim());
        }
        return fields;
    };

    this.getFilterDivByTableIDOrNull = function(tableID){
        var res = null;
        if(document.getElementById(tableID).parentNode.getElementsByTagName("div").length > 0){
            for (i = 0; i < document.getElementById(tableID).parentNode.getElementsByTagName("div").length; i++) { 
                if(document.getElementById(tableID).parentNode.getElementsByTagName("div")[i].getAttribute("class")==="filter"){
                    return document.getElementById(tableID).parentNode.getElementsByTagName("div")[i];
                }
            }

        }
        return res;
    };
	
	window._loadData = 0;
	window._drowObject=true;
    this.LoadData = function(tableContainer,rq){ 
	console.log(rq);
		window.loadData = 1;
        this.setVisability(tableContainer, false);
        if (window.XMLHttpRequest) { 
			xmlhttp = new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari */
        } else {  
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); /* code for IE6, IE5 */
		}
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
				//alert(xmlhttp.responseText);
                d = JSON.parse(xmlhttp.responseText);
				//console.log(d.body);
				window._drowObject= true;
                table.DrawSection(tableContainer,d.body);
				window._drowObject= true;
                table.DrawSection(tableContainer,d.footer,"tfoot");
                table.LoadEndCalback(tableContainer);
                table.setVisability(tableContainer, true);
				
                if(typeof rq === "object"){
                    table.ColumnHover(tableContainer,rq.colNo);
                }
				
				
            }
			window._loadData = 0;
        };
		var url = this.RequestToUrl(rq);
	    url=url+'&call=ajax';
		//alert(url);
	
        xmlhttp.open("GET",url , true);
        xmlhttp.send();
    };
    
    this.setVisability = function(tableContainer,rq){
        var tbl = document.getElementById(tableContainer);
        if(rq===true){
            tbl.style.opacity = "1";
            tbl.style.cursor = "auto";
        } else if(rq===false){
            tbl.style.opacity = "0.8";
            tbl.style.cursor = "wait";
        } else { console.log("table error in the rq value");}
    };
    
    this.getParent = function (obj,objType){
        while( obj && obj.tagName !== objType.toUpperCase() ){
            obj = obj.parentNode;
        } return obj;
    };
    
    this.init = function(tableId){
        this.SetTheTableColumnsHoverEffect(tableId);
    }

    this.iePrior = function(v) {var rv=false; if(navigator.appName==='Microsoft Internet Explorer') {var ua=navigator.userAgent; var re=new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})"); if(re.exec(ua)!==null) rv=parseFloat(RegExp.$1); rv=rv<v?true:false; } return rv;};
    this.loadJS = function(src){ var s = document.createElement('script'); s.src = src; document.getElementsByTagName('head')[0].appendChild(s);};
    this.loadCSS = function(src){ var s = document.createElement('link'); s.href = src; s.rel="stylesheet"; document.getElementsByTagName('head')[0].appendChild(s);};
    
    this.LoadEndCalback = function(tableId){};
    this.AppendRowCalback = function(tableId){};
};

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


function tablesLoadData(){
    var tables = document.getElementsByTagName("table");
    for (i = 0; i < tables.length; i++) { 
        if((table.iePrior(9) ? (typeof tables[i]["data-table"]!== 'undefined') : tables[i].hasAttribute("data-table")) && tables[i].getAttribute("data-table") === "js"){
            table.LoadData(tables[i].id);
            table.SetTheTableColumnsHoverEffect(tables[i].id);
        }
    }
    if(table.iePrior(10)){
        table.loadJS("/add/helpers/table/add/json2.js");
    }
    
    if(table.iePrior(8)){ //can be used to add apropriate tables links modifications
        // loadCSS("/add/helpers/table/add/ie7-and-down.css");
    }
}

/*if(window.addEventListener){window.addEventListener('load',tablesLoadData,false);} else if(window.attachEvent){window.attachEvent('onload',tablesLoadData);}*/