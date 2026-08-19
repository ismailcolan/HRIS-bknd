var searchLocs = document.getElementById("search-locs");

if(searchLocs !== null){
    var locs = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.whitespace,
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      // url points to a json file that contains an array of country names, see
      // https://github.com/twitter/typeahead.js/blob/gh-pages/data/countries.json
      prefetch: '/Search/attorneys-search-locations.json'
    });
    var cats = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.whitespace,
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      // url points to a json file that contains an array of country names, see
      // https://github.com/twitter/typeahead.js/blob/gh-pages/data/countries.json
      prefetch: '/Search/attorneys-search-categories.json'
    });
    
    $('#search-locs .typeahead').typeahead({ hint: false, highlight: true, minLength: 1,
    },{ name: 'loc', source: locs, limit: 10 });
    
    $('#search-cases .typeahead').typeahead({ hint: false, highlight: true, minLength: 1,
    },{ name: 'case', source: cats, limit: 10 });
    
	$('#locs .typeahead').typeahead({
		hint: false,
		highlight: true,
		minLength: 1,
	},{
	  name: 'loc',
	  source: locations,
	  limit: 10
	});
    
    //Reset attr "data-selected" on focus
    $('#search-locs .typeahead').bind('typeahead:active', function(ev, suggestion) {
        $('#search-locs .typeahead').removeAttr("data-selected");
    });
    $('#search-cases .typeahead').bind('typeahead:active', function(ev, suggestion) {
        $('#search-cases .typeahead').removeAttr("data-selected");
    });
    
    //Setting field attr "data-selected" on select
    $('#search-locs .typeahead').bind('typeahead:select', function(ev, suggestion) {
        $('#search-locs .typeahead').attr("data-selected",suggestion);
        console.log('Loc selection: ' + suggestion);
    });
    $('#search-cases .typeahead').bind('typeahead:select', function(ev, suggestion) {
        $('#search-cases .typeahead').attr("data-selected",suggestion);
        console.log('Case selection: ' + suggestion);
    });
    
    function preprocessSearchCaseLoc(){
        
        return false;
    }

} else {
    var locations = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.whitespace,
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      // url points to a json file that contains an array of country names, see
      // https://github.com/twitter/typeahead.js/blob/gh-pages/data/countries.json
      prefetch: '/add/data/attorneys-claim-locations.json'
    });
    $('#locs .typeahead').typeahead({
        hint: false,
        highlight: true,
        minLength: 1,
    },{
      name: 'loc',
      source: locations,
      limit: 10
    });
}



// passing in `null` for the `options` arguments will result in the default
// options being used
/*$('#locations .typeahead').typeahead(null, {
  name: 'locations',
  source: locations
});
*/


