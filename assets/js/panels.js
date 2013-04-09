document.write('<style type="text/css">div.panel { width: 0; height: 0; }</style>');

Effect.OpenUp = function(element, time) {
    element = $(element);
    new Effect.BlindDown(
		element,
		{
			duration:time,
			beforeStart: function(){
				element.addClassName('inuse');
				panels : Array;
				panels = document.getElementsByClassName('panel');
				var i;
				for (i=0; i<panels.length; i++) {
					panels[i].addClassName('inuse');
					if (panels[i].style.display != 'none' && !panels[i].hasClassName('alwaysopen'))  {
						new Effect.CloseDownSecondary(panels[i], time);
					}
			    }
			},
			afterFinish: function(){
				element.removeClassName('inuse');
				panels : Array;
				panels = document.getElementsByClassName('panel');
				var i;
				for (i=0; i<panels.length; i++) {
					panels[i].removeClassName('inuse');
			    }
			}
		}
	);
}

Effect.CloseDown = function(element, time) {
    element = $(element);
    new Effect.BlindUp(
		element,
		{
			duration:time,
			beforeStart: function(){
				element.addClassName('inuse');
				panels : Array;
				panels = document.getElementsByClassName('panel');
				var i;
				for (i=0; i<panels.length; i++) {
					panels[i].addClassName('inuse');
					if (panels[i].style.display != 'none' && !panels[i].hasClassName('alwaysopen'))  {
						new Effect.CloseDownSecondary(panels[i], time);
					}
			    }
			},
			afterFinish: function(){
				element.removeClassName('inuse');
				panels : Array;
				panels = document.getElementsByClassName('panel');
				var i;
				for (i=0; i<panels.length; i++) {
					panels[i].removeClassName('inuse');
			    }
			}
		}
	);
}

Effect.CloseDownSecondary = function(element, time) {
    element = $(element);
    new Effect.BlindUp(
		element,
		{
			duration:time
		}
	);
}

Effect.Combo = function(element) {
    element = $(element);

	if (!element.hasClassName('inuse') && !element.hasClassName('alwaysopen')) {
	    if (element.style.display == 'none') {
	        new Effect.OpenUp(element, 0);
	    }
		else {
	        new Effect.CloseDown(element, 0);
	    }
	}
}

Effect.InitializePage = function(element) {
    element = $(element);

	panels = document.getElementsByClassName('panel');
	var i;
	for (i=0; i<panels.length; i++) {
		if ((element !== null && element.id == panels[i].id) || (element === null && panels[i].hasClassName('open')) || (panels[i].hasClassName('alwaysopen'))) {
			panels[i].style.height = "auto";
			panels[i].style.width = "auto";
		}
		else {
			panels[i].style.display = "none";
			panels[i].style.height = "auto";
			panels[i].style.width = "auto";
		}
	}

	if (element !== null) {
		new Effect.ScrollTo(element, {duration:0});
	}
}