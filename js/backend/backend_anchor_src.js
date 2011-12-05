//<![CDATA[

var checkAnchorHook = new Array();
var currentAnchor = null;

$(function() {
	// Anchor navigation
	setInterval(function() {
		// Check if it has changes
		if(currentAnchor != document.location.hash){
			currentAnchor = document.location.hash;
		}
		
		// Parse anchor functions
		var i = 0;
		for (i = 0; i < checkAnchorHook.length; i++)
		{
			eval(checkAnchorHook[i]);
		}
	}, 100);
});

//]]>
