"use strict";
//var windowWidthMenuDropdown=460; //Window width for changing the menu into dropdown

// fix for console.log for ie8
  if (!window.console) console = {log: function() {}};
  
$(function(){
	
  /* fix for select input's dropdown arrow for IE */
  // var ua = window.navigator.userAgent;
  // var msie = ua.indexOf("MSIE ");

  // if (msie > 0){
  //   $("form select").css({
  //     'background-image':'none',
  //     'padding-right':'6px'
  //   });
  // }

	$(window).on('load resize',function(){

    //fixMenu();
    //setNavItemWidth(); // center menu items

		


  //   if($(window).width()+getScrollBarWidth()>windowWidthMenuDropdown){
		// 	if($('ul.main-menu li').eq(1).css('display')==='none'){
		// 		$('ul.main-menu li:not(.menu-icon-mobile)').show();
		// 	}
		// }else{
		// 	$('ul.main-menu li:not(.menu-icon-mobile)').hide();
		// }
	});
	
});

/* Copyright Year */
function copyrightYear(s){var d=new Date();return(s!=d.getFullYear())?s+" &#45; "+d.getFullYear():s}

function fixMenu(){
  if($(window).width()+getScrollBarWidth()>windowWidthMenuDropdown){

    // MENU IS NOT DROPDOWN
    if($('ul.main-menu li').eq(1).css('display')==='none'){
      $('ul.main-menu li:not(.menu-icon-mobile)').show();
    }

    

  }else{
    // MENU IS DROPDOWN
    $('ul.main-menu li:not(.menu-icon-mobile)').hide();
  }
}

/* Brwoser Scrollbar Width */
function getScrollBarWidth() {
  var inner = document.createElement('p');
  inner.style.width = "100%";
  inner.style.height = "200px";

  var outer = document.createElement('div');
  outer.style.position = "absolute";
  outer.style.top = "0px";
  outer.style.left = "0px";
  outer.style.visibility = "hidden";
  outer.style.width = "200px";
  outer.style.height = "150px";
  outer.style.overflow = "hidden";
  outer.appendChild (inner);

  document.body.appendChild (outer);
  var w1 = inner.offsetWidth;
  outer.style.overflow = 'scroll';
  var w2 = inner.offsetWidth;
  if (w1 == w2) w2 = outer.clientWidth;

  document.body.removeChild (outer);

  return (w1 - w2);
};