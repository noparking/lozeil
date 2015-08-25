function buildgraph() {

    var margin = {top: 20, right: 30, bottom: 30, left: 40};
    var height = 500 ;
    var barWidth = width / data.length;
    
    var x = d3.scale.ordinal()
	.domain(domain)
	.rangeBands([0, width])
    
    var y = d3.scale.linear()
   	.domain(d3.range(-max , max +1),100)
   	.range([height, 0]);
    
    var xAxis = d3.svg.axis()
	.scale(x)
	.orient("bottom");

    var yAxis = d3.svg.axis()
	.scale(y)
	.orient("left");  
    
    
    var chart = d3.select(".chart")
	.attr("width", width + margin.left + margin.right)
	.attr("height", height + margin.top + margin.bottom)
	.append("g")
  	.attr("transform", "translate(" + margin.left + "," + margin.top + ")");;
    
    
    chart.append("g")
	.attr("id","axis")
	.attr("transform", "translate(0," + height/2 + ")")
	.call(xAxis);

    chart.append("g")
  	.attr("id","yaxis")
	.call(yAxis);

    var bar =  chart.selectAll(".bar")
	.data(data)
	.enter();
    
    bar.append("rect")
	.attr("y", function (d){
	    return (d.value > 0)?height/2 - Math.abs((d.value/max)* (height/2)):height/2 ;
	})
	.attr("x",function(d, i) { return  i * 35; })
	.attr("height", function(d) { 
	    return Math.abs((d.value/max)* (height/2));
	})
	.attr("width", barWidth - 1)
	.style("fill",function(d){return (d.value>0)?"steelblue":"#FF6666"});
    
    
    bar.append("text")
	.attr("x", function(d, i) { return   15 + i * 35; })
	.attr("y", function(d) { return (d.value > 0)?height/2 - Math.abs((d.value/max)* (height/2)) +6 :(height/2)+Math.abs((d.value/max)* (height/2)) - 15; })
	.attr("dy", ".75em")
	.text(function(d) { return Math.floor(d.value); });
    
    function scaly(d) {
	if (d>=0) { 
	    return "translate(" + 0 + "," + ((height/2)-((d/(max))*(height/2))) + ")";
	}
	else { 
	    return "translate(" + 0 + "," + ((height/2)+((d/(-max))*(height/2)))  + ")";
	}
    }
    
    chart.selectAll(".chart #yaxis .tick")
 	.attr( "transform", scaly );
    
    
    chart.selectAll(".chart #axis line").data(data).attr("y2", function (d){ return (d.value>= 0)?6:-6 });
    
    chart.selectAll(".chart #axis text").data(data)
 	.attr("y", function (d){ return (d.name.toString().length < 5)?((d.value >= 0)?9:-15 ):(-6 ) ;})
 	.attr("x", function (d){ return (d.name.toString().length < 5)?(0):((d.value >= 0)?(-(d.name.toString().length*2 + 30)):(d.name.toString().length*2 + 30) ) ;})
 	.attr("transform", function (d){ return (d.name.toString().length > 5)?"rotate(-90)":"" ;});


}

$(document).ready(
    function () {
	buildgraph();
    }
);