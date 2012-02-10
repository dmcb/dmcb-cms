if (typeof(dmcb) === "undefined") { dmcb = {}; }

dmcb.addLoadEvent = function(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	}
	else {
	    window.onload = function() {
			if (oldonload) {
			    oldonload();
		    }
			func();
	    }
	}
};

dmcb.confirmation = function(message) {
	var answer = confirm(message);
	if (answer){
		return true;
	}
	else {
		return false;
	}
};

dmcb.confirmationLink = function(message, location) {
	var answer = confirm(message);
	if (answer){
		window.location = location;
	}
};

dmcb.disableUploadAs = function(dropdown, todisable) {
	if (dropdown.options[dropdown.selectedIndex].value != "") {
		tempobj = document.getElementById(todisable);
		tempobj.disabled = true;
	}
	else
	{
		tempobj = document.getElementById(todisable);
		tempobj.disabled = false;
	}
};

dmcb.goto = function(dropdown) {
	var index=dropdown.selectedIndex;
	location=dropdown.options[index].value;
};

dmcb.gotovalue = function(element) {
	location=element.value;
};

dmcb.linksubmit = function(formid) {
	thisform = document.getElementById(formid);
	thisform.submit();
	return false;
};

dmcb.searchsubmit = function(formid, type) {
	thisform = document.getElementById(formid);
	thisform.innerHTML = thisform.innerHTML + '<input type="hidden" name="searchtype" value="' + type + '" class="hidden" />';
	thisform.submit();
	return false;
};

dmcb.openNewWindow = function(URLtoOpen, windowName, windowFeatures) {
	newWindow=window.open(URLtoOpen, windowName, windowFeatures);
};

dmcb.submit = function(thisform) {
	//thisform.style.backgroundColor = '#ede9e4';

	var buttonchoice;

	for (var i=0; i<thisform.length; i++) {
		if (thisform.elements[i].name == "buttonchoice") {
			buttonchoice = thisform.elements[i];
		}
	}
	for (var i=0; i<thisform.length; i++) {
		if (((buttonchoice.value == "" && thisform.elements[i].name != "") || (buttonchoice.value != "" && buttonchoice.value == thisform.elements[i].name)) && ((thisform.elements[i].nodeName.toLowerCase() == "input" && (thisform.elements[i].type == "button" || thisform.elements[i].type == "submit")) || thisform.elements[i].nodeName.toLowerCase() == "button")) {
			buttonchoice.value = thisform.elements[i].name;
			if (thisform.elements[i].hasClassName('confirm')) {
				var answer = confirm("Are you sure you wish to confirm this action?");
				if (!answer) {
					//thisform.style.backgroundColor = '#444444';
					return false;
				}
			}
		}
		if ((thisform.elements[i].nodeName.toLowerCase() == "input" && (thisform.elements[i].type == "button" || thisform.elements[i].type == "submit")) && thisform.elements[i].value != "Please wait...") {
			thisform.elements[i].value = "Please wait...";
			thisform.elements[i].disabled = true;
		}
		else if (thisform.elements[i].nodeName.toLowerCase() == "button" && tempobj.innerHTML != "Please wait...") {
			thisform.elements[i].innerHTML = "Please wait...";
			thisform.elements[i].disabled = true;
		}
	}

	return true;
};

dmcb.submitRefresh = function() {
	for (var i=0; i<document.forms.length; i++) {
		for (var j=0; j<document.forms[i].elements.length; j++) {
			if ((document.forms[i].elements[j].nodeName.toLowerCase() == "input" && (document.forms[i].elements[j].type == "button" || document.forms[i].elements[j].type == "submit")) || document.forms[i].elements[j].nodeName.toLowerCase() == "button") {
				document.forms[i].elements[j].disabled = false;
			}
		}
	}
}

dmcb.submitRemoveValue = function(thisbutton) {
	var thisform = thisbutton.form;
	for (var i=0; i<thisform.length; i++) {
		if (thisform.elements[i].name == "buttonchoice")
		{
			thisform.elements[i].value = "";
		}
	}
}

dmcb.submitSetValue = function(thisbutton) {
	var thisform = thisbutton.form;
	for (var i=0; i<thisform.length; i++) {
		if (thisform.elements[i].name == "buttonchoice")
		{
			thisform.elements[i].value = thisbutton.name;
		}
	}
}

dmcb.removeElements = function(input, thisform, toremove) {
	if ((input.type == "checkbox" && input.checked == 1) || (input.type == "text" && input.value != ""))
	{
		for (var i=0; i<thisform.length; i++) {
			var tempobj = thisform.elements[i];
			for (var j=0; j<toremove.length; j++) {
				if (tempobj.name == toremove[j] || tempobj.htmlFor == toremove[j])
					tempobj.style.display = "none";
			}
		}
	}
	else
	{
		for (var i=0; i<thisform.length; i++) {
			var tempobj = thisform.elements[i];
			for (var j=0; j<toremove.length; j++) {
				if (tempobj.name == toremove[j] || tempobj.htmlFor == toremove[j])
					tempobj.style.display = "";
			}
		}
	}
};

dmcb.toUrlname = function(thisform, title, target) {
	for (var i=0; i<thisform.length; i++) {
		if (thisform.elements[i].name == target)
		{
			title = title.replace(/[^a-zA-Z0-9 \-_]/g,'');
			title = title.replace(/^\s+|\s+$/g,'');
			title = title.replace(/\s+/g,' ');
			title = title.replace(/\s/g,'-');
			title = title.replace(/_+/g,'_');
			title = title.replace(/\-+/g,'-');
			title = title.substring(0,30);
			thisform.elements[i].value = title.toLowerCase();
		}
	}
}

dmcb.Items = Class.create({
	initialize: function(prefix, nicename) {
		this.prefix = prefix;
		this.nicename = nicename;
		this.itemslist = new Array();
	},

	addItem:function(dropdown) {
		var itemslist = this.itemslist;
		var index = dropdown.selectedIndex;
		dropdown.selectedIndex = 0;
		var item = dropdown.options[index].text;
		var value = dropdown.options[index].value;
		if (value != "") {
			var match = false;
			for (var i=0; i<itemslist.length; i++) {
				if (itemslist[i][1] == value) {
					match = true;
				}
			}
			if (!match) {
				var newitem = Array(item, value);
				itemslist.push(newitem);
			}
			this.renderItems();
		}
	},

	addCustomItem:function(textbox) {
		var itemslist = this.itemslist;
		var item = textbox.value;
		textbox.value = "";
		var value = -1;
		if (item != "") {
			var match = false;
			for (var i=0; i<itemslist.length; i++) {
				if (itemslist[i][0] == item) {
					match = true;
				}
			}
			if (!match) {
				var newitem = Array(item, value);
				itemslist.push(newitem);
			}
			this.renderItems();
		}
	},

	generateItems:function() {
		var itemslist = this.itemslist;
		if (document.getElementById(this.prefix + "values") != null) {
			var values = document.getElementById(this.prefix + "values");
			var names = document.getElementById(this.prefix + "names");
			var itemvalues = "";
			var itemnames = "";

			if (values.value.length > 0) {
				itemvalues = values.getAttribute("value").split(";");
			}
			if (names.value.length > 0) {
				itemnames = names.getAttribute("value").split(";");
			}

			for (var i=0; i<itemvalues.length-1; i++) {
				var newitem = Array(itemnames[i],itemvalues[i]);
				itemslist.push(newitem);
			}
			this.renderItems();
		}
	},

	removeItem:function(value) {
		var itemslist = this.itemslist;
		for (var i=0; i<itemslist.length; i++) {
			if (itemslist[i][0] == value) {
				itemslist.splice(i,1);
			}
		}
		this.renderItems();
		return false;
	},

	renderItems:function() {
		var itemslist = this.itemslist;
		var list = document.getElementById(this.prefix + "s");
		var values = document.getElementById(this.prefix + "values");
		var names = document.getElementById(this.prefix + "names");
		values.value = "";
		names.value = "";
		if (list != null) {
			if (itemslist.length == 0) {
				list.innerHTML = "No " + this.nicename + " have been selected";
			}
			else {
				list.innerHTML = "";
				for (var i=0; i<itemslist.length; i++) {
					names.value = names.value + itemslist[i][0] + ";";
					values.value = values.value + itemslist[i][1] + ";";
					if (itemslist[i][1] == -1) {
						list.innerHTML = list.innerHTML + itemslist[i][0] + " (pending approval)";
					}
					else {
						list.innerHTML = list.innerHTML + itemslist[i][0];
					}
					list.innerHTML = list.innerHTML + " [<a href=\"\" onclick=\"return dmcb." + this.prefix + "list.removeItem('" + itemslist[i][0] + "')\">remove</a>]<br/>";
				}
			}
		}
	}
});

dmcb.addLoadEvent(function() {
	dmcb.submitRefresh();

	dmcb.categorylist = new dmcb.Items('category','categories');
	dmcb.categorylist.generateItems();

	dmcb.previouspostlist = new dmcb.Items('previouspost','previous posts');
	dmcb.previouspostlist.generateItems();
});