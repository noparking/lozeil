function ajax_xhr_new() {
	xhr = false;
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		xhr = new ActiveXObject("Microsoft.XMLHTTP");
	}
	return xhr;
}

function ajax_xhr_send(xhr, url, param, treat) {
	xhr.open("POST", url, true);
	xhr.onreadystatechange = function() {
		if(xhr.readyState == 4) {
			eval(xhr.responseText);
			eval(treat);
		}
	}
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(param);
}

function projectmenuReplace(div_name) {
	document.getElementById(div_name+"_view").style.visibility = "hidden";
	document.getElementById(div_name+"_view").style.display = "none";
	document.getElementById(div_name+"_edit").style.visibility = "visible";
	document.getElementById(div_name+"_edit").style.display = "block";
	document.getElementById(div_name+"_edit").focus();
}

function projectmenuUpdate(div_name, something, id) {
	document.getElementById(div_name+"_edit").value= something;
	document.getElementById("project_id").value= id;
	document.getElementById(div_name+"_form").submit();
}

function get_projectmenu(div_name, userid, useraccess, something) {
	req = ajax_xhr_new();
	if (req) {
		urlparams = "method=json&action=search&userid=" + userid + "&useraccess=" + useraccess + "&value=" + something;
		ajax_xhr_send(req, "index.php?content=menu.ajax.php&", urlparams, "treat_projetmenu('" + div_name + "', element)");
	}
}

function treat_projetmenu(div_name, element) {
	var list = "<ul>";
	for (var i=0 ; i<element.length ; i++) {
		list += '<li onclick="javascript:projectmenuUpdate(\'projectmenu\', \'' + element[i][1].replace(/'/, "\\'").replace(/"/, '\\"') + '\', \'' + element[i][0] + '\');">' + element[i][1] + '</li>';
	}
	list += "</ul>";
	document.getElementById(div_name+"_result").style.visibility = "visible";
	document.getElementById(div_name+"_result").innerHTML = list;
}

function Check(status) {
	chk = window.confirm(status);
	return chk;
}

function openwindow(url,width,height) {
	if (width != 0 || height != 0) {
		mywindow = window.open(url, "details", "width=" + width + ",height=" + height + ",scrollbar=auto,scrollbars=yes,menubar=no,toolbar=no,status=no,rezizable=no");
	} else {
		mywindow = window.open(url, "parent");
	}
	mywindow.focus();
}

function copyfield(from,to) {
	to.value = from.value;
}

function selectmultiple(option) {
	for (i=0; i<option.length; i++){
		option.options[i].selected = true;
	}
}

function gotourl(object) {
	if (object.selectedIndex > 0) {
		window.location.href = object.options[object.selectedIndex].value;
	}
}

function in_array(array, search_term) {
	var i = array.length;
	do {
		if (array[i] === search_term) {
			return true;
		}
	} while (i--);
	return false;
}

function add_list(tbox, json) {
	alert(json);
	var list = eval('{"test":"user10","test1":"user101"}');
	alert(list);
}

function show_tip(what) {
	what = eval(what);
	what.style.display = 'block';
}

function mask_tip(what) {
	what = eval(what);
	what.style.display = 'none';
}         

function check_all(what, pattern, status) {
	for (i=0; i < what.elements.length; i++) {
		if (what.elements[i].name.indexOf(pattern) !=-1) {
			if (status == 'true') {
				what.elements[i].checked = true;
			} else {
				what.elements[i].checked = false;
			}
		}
	}
}

function check_all_radio(what, pattern) {
	for (i=0; i < what.elements.length; i++) {
		var element = what.elements[i];
		if(element.nodeType == 1 && element.type == "radio" && element.id == pattern) {
			what.elements[i].checked = true;
		}
	}
}

function validate_email(what, id) {
	if (what.value.match(/^[_a-z0-9-]+([._a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]{2,5})+$/)) {
		change(id, 'ok');
	} else {
		change(id, 'error');
	}
}

function validate_telephone(what, id) {
	if (what.value.match(/^[\+ \.\(\)0-9]{5,20}$/)) {
		change(id, 'ok');
	} else {
		change(id, 'error');
	}
}

function change(id, css_class) {
	identity = document.getElementById(id);
	identity.className = css_class;
}

function edit_list(what, id) {
	var re = new RegExp('((^' + id + '$)|(^' + id + ', )|( ' + id + ',)|(, ' + id + '$))');
	if (what.value.match(re))	{
		what.value = what.value.replace(re, '');
	} else {
		what.value = what.value + ', ' + id;
	}
	var redevant = new RegExp('(^, )');
	what.value = what.value.replace(redevant, '');
}

function getObj(id) {
	if (document.getElementById) {
		return document.getElementById(id);
	}
	if (document.all) {
		return document.all[id];
	}
	return null;
}

function deactivateObj(id) {
	var obj = getObj(id);
	if (isObject(obj)) {
		obj.disabled = true;
	}
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

function toggle_obj(id, display) {
	if (toggle_obj.arguments.length == 1) {
		display = "block";
	}

	if (state_obj(id) == false) {
		show_obj(id, display);
	} else {
		hide_obj(id);
	}
}

function show_all_tr(what, pattern, status) {
	for (i=0; i < what.getElementsByTagName("tr").length; i++) {
		if (what.getElementsByTagName("tr")[i].id.indexOf(pattern) !=-1) {
			id = what.getElementsByTagName("tr")[i].id;
			if (status == 'true') {
				show_obj(id, 'table-row');
			} else {
				hide_obj(id);
			}
		}
	}
}

function show_all_tr_day(what, pattern, day) {
	
	show_all_tr(what, pattern, '');

	for (i=0; i < what.getElementsByTagName("tbody").length; i++) {
		if (what.getElementsByTagName("tbody")[i].id.indexOf(day) !=-1) {
			what = what.getElementsByTagName("tbody")[i];
			for (i=0; i < what.getElementsByTagName("tr").length; i++) {
				id = what.getElementsByTagName("tr")[i].id;
				if (id != "") {
					show_obj(id, 'table-row');
				}
			}
		}
	}
}

function get_obj_from_id_and_type(id, type) {
	var objects =  document.getElementsByTagName(type);
	for(i=0; i<objects.length; i++) {
		if (objects[i].id == id) {
			return objects[i];
		}
	}
}

function opt(id, text, select) {
	var optionName = new Option(text, id, false, false);
	var length = select.length;
	select.options[length] = optionName;
}

function show_triple_select(block_id, select) {
	var a, b;
	var id = select.id;
	var tree = eval('get_tree_' + block_id + '();');
	var block = document.getElementById(block_id);
	var selects = block.getElementsByTagName('select');
	var select_1 = selects[0];
	var select_2 = selects[1];
	var select_3 = selects[2];
	var inputs = block.getElementsByTagName('input');
	var hidden_2 = inputs[0];
		
	switch (id) {
		case select_1.id :
			hidden_2.value = 0;
			break;
		case select_2.id :
			if (select.selectedIndex == 0) {
				hidden_2.value = 0;
			} else {
				hidden_2.value = select.options[select.selectedIndex].value;
			}
			break;
		case select_3.id :
			if (select.selectedIndex == 0) {
				hidden_2.value = select_2.options[select_2.selectedIndex].value;
			} else {
				hidden_2.value = select.options[select.selectedIndex].value;
			}
			break;
	}
		
		
	if (id != select_3.id) {
		
		select_3.options.length = 0;

		var selected = select_1.selectedIndex;
		var index = select_1.options[selected].value;

		if (id == select_1.id) {
			hide_obj(select_3);
			
			select_2.options.length = 0;
			
			if (!isUndefined(tree[index]['children'])) {
				for (a in tree[index]['children']) {
					if (!isUndefined(tree[index]['children'][a]['value'])) {
						var option = new Option(tree[index]['children'][a]['value'], a);
					
						// vérification IE
						if (navigator.appName=="Microsoft Internet Explorer") {
							select_2.add(option, select_2.options.length);
						} else {
							select_2.appendChild(option);
						}
					}
				}
				if (index != 0) {
					show_obj(select_2, 'inline');
				} else {
					hide_obj(select_2);
					hide_obj(select_3);
				}
			}
		}

		if (id == select_2.id) {
			var selected = select_2.selectedIndex;
			var index2 = select_2.options[selected].value;	

			if (!isUndefined(tree[index]['children'][index2]['children'])) {
				for (b in tree[index]['children'][index2]['children']) {
					var option = new Option(tree[index]['children'][index2]['children'][b]['value'], b);
					
					// vérification IE
					if (navigator.appName=="Microsoft Internet Explorer") {
						select_3.add(option, select_3.options.length);
					} else {
						select_3.appendChild(option);
					}
				}
			}
			if (index2 != 0) {
				show_obj(select_3, 'inline');
			} else {
				hide_obj(select_3);
			}
		}
	}
}

function addOnloadEvent(fnc){
	if ( typeof window.addEventListener != "undefined" ) {
		window.addEventListener( "load", fnc, false );
	} else if ( typeof window.attachEvent != "undefined" ) {
		window.attachEvent( "onload", fnc );
	} else {
		if ( window.onload != null ) {
			var oldOnload = window.onload;
			window.onload = function ( e ) {
				oldOnload( e );
				window[fnc]();
			};
		} else {
			window.onload = fnc;
		}
	}
}

$(document).ready(function() {
	console.log($(".form_add_edit_writing #id").attr("value"));
	if($(".form_add_edit_writing #id").attr("value") > 0) {
		$(".form_add_edit_writing").show();
		$("#form_hide").show();
		$("#form_show").hide();
	}
	
	$(".actions").hide();
	$(".more").on("click", function() {
		if ($(this).hasClass("hide")) {
			$(this).addClass("show");
			$(this).removeClass("hide");
			$(".actions").slideDown(400, function() {
				$("#menu_more").hide();
				$("#menu_less").show();
			});
			
		} else {
			$(this).addClass("hide");
			$(this).removeClass("show");
			$(".actions").slideUp(400, function() {
				$("#menu_less").hide();
				$("#menu_more").show();
			});
		}
	})
	
	$("#form_show").on("click", function() {
		$(".form_add_edit_writing").slideDown(1, function() {
			$("html, body").animate({ scrollTop: $(document).height() }, "slow");
		});
		$(this).hide();
		$("#form_hide").show();
	})
	
	$("#form_hide").on("click", function() {
		$(".form_add_edit_writing").slideUp();
		$(this).hide();
		$("#form_show").show();
	})
})

$(document).keyup(function(e) {

  if (e.keyCode == 27) {
	  if ($(".more").hasClass("show")) {
			$(".more").addClass("hide");
			$(".more").removeClass("show");
			$(".actions").slideUp(400, function() {
				$("#menu_less").hide();
				$("#menu_more").show();
			});
		}
		$(".table_drag_drop input[type='text']").attr("type", "hidden");
		$(".form_add_edit_writing").slideUp();
		$("#form_hide").hide();
		$("#form_show").show();
		$(".further_information").slideUp();
  }   
});