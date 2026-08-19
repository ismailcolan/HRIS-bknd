if(window.addEventListener){window.addEventListener('load',initAnimate,false);
} else if(window.attachEvent){window.attachEvent('onload',initAnimate);}

function initAnimate(){
    animate.init();
}

var animate = new function(){
    
    this.addAndReset = function(obj, classes, resetDelay){
        var resetTime = (typeof resetDelay !== undefined) && Math.round(resetDelay)>0 ? Math.round(resetDelay) : 3000;
        var element = (typeof obj==="string") ? document.getElementById(obj) : obj;
        var currentClass = this.add(obj, classes);
        setTimeout( function(){
            if(currentClass===null){
                element.removeAttribute("class");
            } else {
                element.setAttribute("class",currentClass);
            }
        }, resetTime );
    };
    
    this.add = function(obj, classes){
        var element = (typeof obj==="string") ? document.getElementById(obj) : obj;
        var addClass = (classes.indexOf("animated") > 0) ? classes : ("animated " + classes);
        var currentClass = element.getAttribute("class");
        if(currentClass === null){
            element.setAttribute("class",addClass);
        } else {
            element.setAttribute("class",(currentClass+" "+addClass));
        }
        return currentClass;
    };
    
    this.init = function(){
        var jsFilename = "animate.js";
        var js = document.getElementById(jsFilename);
        if(js!==null){
            var path = js.src.substring(0,js.src.indexOf(jsFilename));
            var fileref = document.createElement("link");
            fileref.rel = "stylesheet"; fileref.type = "text/css"; 
            fileref.href = path +"animate.min.css";
            document.getElementsByTagName("head")[0].appendChild(fileref);
        } else {
            console.log("Animate.js: Missing own id tag. Expected: <script id=\"animate.js\" sr=\"...\">.");
        }
    };
};



