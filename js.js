'use strict'

var drawCloroplethMap = (function(){


	google.charts.load('current', {
		'packages':['geochart'],
	});



	var loaded=false;
	var queue=[];
	function onLoadCharts() {
		loaded=true;
		if(queue.length){
			var q=queue.slice(0);
			queue=[];
			q.forEach(function(f){ f(); });
		}
	}
			
	

	



	var drawCloroplethMap=function(region, chartData, divId){

		var render=function(){

			var data = google.visualization.arrayToDataTable(([['Country', 'Unique IPs']]).concat(chartData));
			var options = {
				colorAxis: {colors: ['rgb(161, 199, 211)', 'rgb(0, 187, 222)', 'rgb(254, 102, 114)']},
				region:region
			};
			var chart = new google.visualization.GeoChart(document.getElementById(divId));
			chart.draw(data, options);

		};


		if(loaded){
			render();
			return;
		}

		queue.push(render);


	};

	google.charts.load('current', {
		'packages':['geochart'],
	});
	google.charts.setOnLoadCallback(onLoadCharts);

	return drawCloroplethMap;

})();



var showRegion=function(){

	$$('button.region')[0].addClass('active');
	$$('button.world')[0].removeClass('active');

	$$('#regions_div_')[0].addClass('hidden');
	$$('#regions_div')[0].removeClass('hidden');

}

var showWorld=function(){

	$$('button.region')[0].removeClass('active');
	$$('button.world')[0].addClass('active');


	$$('#regions_div')[0].addClass('hidden');
	$$('#regions_div_')[0].removeClass('hidden');
	

}



var addMetric=(function(){


	var addMetric=function(div, title, result, options){

		options=ObjectAppend_({
			height:240,
			colors:["#00bbde"]
		}, options);

		var chart = new Keen.Dataviz()
		.el(div instanceof HTMLElement?div:document.getElementById(div))
		.height(options.height)
		.title(title)
		.type("metric")
		.colors(options.colors)
		.prepare();

		chart.data(result).render();


	};

	return addMetric;

})();



var addChart=(function(){


	var addChart=function(div, title, result, options){


		options=ObjectAppend_({
			height:340
		}, options);

		var chart = new Keen.Dataviz()
		.el(div instanceof HTMLElement?div:document.getElementById(div))
		.height(options.height)
		.title(title)
		.type("bar")
		.stacked(true)
		.chartOptions({
			 bar: {
		        width: {
		            ratio: 0.9 // this makes bar width 90% of length between ticks
		        }
		    },
		    colors:["#66cdaa"]
		
		});

		if(options.colors){
			chart.colors(options.colors);
		}


		if(options.colorMapping){
			chart.colorMapping(options.colorMapping);
		}


		


		chart.prepare();

		chart.data(result).render();
	}


})();

