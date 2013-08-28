function GetDays(month, year) {
	var count = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
	if (1 == month && ((0 == year % 4 && 0 != year % 100) || (0 == year % 400))) {
		count++;
	}
	return count;
}

function GetToday() {
	this.now = new Date();
	this.year = this.now.getFullYear();
	this.month = this.now.getMonth();
	this.day = this.now.getDate();
}

function MakeCalendar(calendar_name) {
	var current = new Date(
		 parseInt(getObj(calendar_name + "year")[getObj(calendar_name + "year").selectedIndex].text)
		,getObj(calendar_name + "month").selectedIndex
		,1
		);
	var startday = current.getDay();
	if (startday == 0) {
		startday = 7;
	}

	var today    = new GetToday();
	var markday  = -1;
	if ((today.year == current.getFullYear()) && (today.month == current.getMonth())) {
	   markday = today.day;
	}

	var mycal = getObj(calendar_name + "dayList");
	if (!mycal) return;
	
	var count = GetDays(current.getMonth(), current.getFullYear());
	var label;
	
	/* 
	 * Mandory kludge for Mozilla-like browsers :-( 
	 * Otherwise table content is severely mangled (!?)
	 * The bug is triggered when table display property is set to 'block' (!)
	 *
	 * Another work-around would be to enclose the table within a div. - mg
	 */
	hide_obj(calendar_name);
	while (mycal.hasChildNodes()) {
		mycal.removeChild(mycal.firstChild);
	}

	LOOP: for (var week = 0, daily = 0; week < 6; week++) {
		var row = document.createElement("tr");
		for (var day = 1; day <= 7; day++) {
			if ((day == startday) && (0 == daily)) {
				daily = 1;
			}
			var cell = document.createElement("td");
			cell.setAttribute("width", 12);
			cell.setAttribute("height", 12);
			cell.setAttribute("valign", "middle");
			cell.style.color = (markday == daily) ? "#f00" : "";
			if ((daily > 0) && (daily <= count)) {
				label = daily++;
				cell.onmouseover = function() {
						this.style.bgColor = this.style.backgroundColor;
						this.style.backgroundColor = "#c99";
					};
				cell.onmouseout  = function() {
						this.style.backgroundColor = this.style.bgColor;
					};
			} else {
				if (5 == week && 0 == day) {
				// exit main loop
					break LOOP;
				}
				label = "";
				cell.onmouseover = cell.onmouseout = function() { return false; };
			}
			var textNode = document.createTextNode(label);
			cell.appendChild(textNode);
			row.appendChild(cell);
		}
		mycal.appendChild(row);
	}
	show_obj(calendar_name);
	$('#' + calendar_name + ' iframe').height($('#' + calendar_name + ' table').height());
	$('#' + calendar_name + ' iframe').width($('#' + calendar_name + ' table').width());
}

function InputNameSuffixed(calendar_name, suffix) {
	if (calendar_name.match(/\]$/)) {
		var element_prefix = calendar_name.substring(0, calendar_name.length - 'calendar'.length - 1);
		return element_prefix + suffix + "]";

	} else {
		var element_prefix = calendar_name.substring(0, calendar_name.length - 'calendar'.length);
		return element_prefix + suffix;
	}
}

function FillInputDate(calendar_name, event) {
	var target = event.srcElement ? event.srcElement : event.target;
	
	if (3 == target.nodeType) {
		target = target.parentNode;
	}
	if ("td" == (target.tagName + '').toLowerCase()) {
		var day = target.innerHTML;
		if ("" != day) { 
			var month = getObj(calendar_name + "month").selectedIndex + 1;
			var year  = getObj(calendar_name + "year") [getObj(calendar_name + "year").selectedIndex].text;
			getObj(InputNameSuffixed(calendar_name, "[d]")).value   = day < 10 ? '0' + day : day;
			getObj(InputNameSuffixed(calendar_name, "[m]")).value = month < 10 ? '0' + month : month;
			getObj(InputNameSuffixed(calendar_name, "[Y]")).value  = year;
			target.onmouseout();
			HideCalendar(calendar_name);
		}
	}
}

function FillDate(calendar_name, event) {
	var target = event.srcElement ? event.srcElement : event.target;
	
	if (3 == target.nodeType) {
		target = target.parentNode;
	}
	if ("td" == (target.tagName + '').toLowerCase()) {
		var day = target.innerHTML;
		if ("" != day) { 
			var month = getObj(calendar_name + "month").selectedIndex + 1;
			var year  = getObj(calendar_name + "year") [getObj(calendar_name + "year").selectedIndex].text;
			getObj(InputNameSuffixed(calendar_name, "day")).value   = day < 10 ? '0' + day : day;
			getObj(InputNameSuffixed(calendar_name, "month")).value = month < 10 ? '0' + month : month;
			getObj(InputNameSuffixed(calendar_name, "year")).value  = year;
			target.onmouseout();
			HideCalendar(calendar_name);
		}
	}
}

function ShowCalendar(calendar_name) {
	MakeCalendar(calendar_name);
}

function HideCalendar(calendar_name) {
	hide_obj(calendar_name);
}

function ToggleCalendar(calendar_name, elem_name) {
	if (state_obj(calendar_name) == false) {
		ShowCalendar(calendar_name);
	} else {
		HideCalendar(calendar_name);
	}
}