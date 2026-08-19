var multySelect = new function(){
    
    this.nodesHolder = null;
    this.actionUrl = null;
    this.nodesSubmitData = null;
    
    /* Nodes list apply action */
    this.setNodes = function(nodesHolder,action){
        var nodesParent = (typeof nodesHolder === "string") ? document.getElementById(nodesHolder): nodesHolder;
        if(this.nodesHolder === null){
            this.nodesHolder = nodesParent;
            this.actionURL = document.URL;
        }
        var nodes = nodesParent.getElementsByTagName("span");
        if(action==="submit"){ this.nodesSubmitData = [];}
        for(i=0; i<nodes.length; i++){
            if(nodes[i].parentNode === nodesParent){
                this.setNode(nodes[i],action);
            }
        }
        if(action==="submit"){
            this.submitData();
        }
    };
    
    /* A Node action */
    this.setNode = function(el,action){
        if(action==="reset"){
           status = el.getAttribute("data-init");
        } else if(action==="change"){
           if(el.getAttribute("class")==="yes"){
               status = "no";
           } else if(el.getAttribute("class")==="no"){
               status = "yes";
           }
       } else if(action==="submit"){
           status = el.getAttribute("class");
           this.nodesSubmitData.push({id:el.getAttribute("data-value"),status:status});
       } else { alert("Error: Incorrect action request."); }
       el.setAttribute("class",status);
    };
    
    /*Subits all nodes data */
    this.submitData = function(){
        var xmlhttp;
        var actionValue = this.nodesHolder.getAttribute("id");
        if (window.XMLHttpRequest) { xmlhttp=new XMLHttpRequest(); /* code for IE7+, Firefox, Chrome, Opera, Safari*/
        } else { xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");/* code for IE6, IE5 */}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState===4 && xmlhttp.status===200) {
                var rem = document.getElementById("assignment-list");
                var remParent = rem.parentNode;
                remParent.removeChild(rem);
                //console.log(xmlhttp.responseText);
                //return;
                remParent.innerHTML=xmlhttp.responseText;
                tableReloadData(document.getElementsByTagName("table")[0].getAttribute("id"));
            };
        };
        xmlhttp.open("POST",this.actionURL,true);
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xmlhttp.send("action="+actionValue+"&data="+encodeURIComponent(JSON.stringify(this.nodesSubmitData)));
    };
};


