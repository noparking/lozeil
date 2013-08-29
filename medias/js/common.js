function getObj(id) {
	if (document.getElementById) {
		return document.getElementById(id);
	}
	if (document.all) {
		return document.all[id];
	}
	return null;
}

function isFunction(a) {
	return typeof a == 'function';
}
 
 function isObject(a) {
	return (typeof a == 'object' && !!a) || isFunction(a);
}

function isUndefined(a) {
	return typeof a == 'undefined';
}

function hide_obj(id) {
	if (isObject(id)) {
		var obj = id;
	} else {
		var obj = getObj(id);
	}

	obj.style.visibility = "hidden";
	obj.style.display = "none";
}

function show_obj(id, display) {
	if (show_obj.arguments.length == 1) {
		display = "inline";
	}

	if (isObject(id)) {
		var obj = id;
	} else {
		var obj = getObj(id);
	}

	obj.style.visibility = "visible";
	try {
		obj.style.display = display;
	} catch(e) {
		obj.style.display = "block";
	}
}

function state_obj(id) {
	var obj = getObj(id);
	if ("block" == obj.style.display || "visible" == obj.style.visibility || "visible" == obj.className) {
		return true;
	} else {
		return false;
	}
}