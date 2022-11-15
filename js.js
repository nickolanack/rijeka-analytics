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


var showMetrics=function(active){

	var list=['tours', 'categories', 'researchers'];
	showSection(active, list);

};


var showMap=function(active){

	var list=['region', 'world'];
	showSection(active, list);

};



var showMainMetric=function(active){

	var list=['all-metrics', 'croatia-metrics', 'other-metrics'];
	showSection(active+'-metrics', list);

};


var showSection=function(active, list){

	
	list.forEach(function(type){
			$$('button.'+type)[0].removeClass('active');
			$$('div.'+type)[0].addClass('hidden');
	});

	$$('button.'+active)[0].addClass('active');
	$$('div.'+active)[0].removeClass('hidden');

};


var showRegion=function(){
	showMap('region');
}

var showWorld=function(){
	showMap('world');
}



var addMetric=(function(){


	return function(div, title, result, options){

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

})();



var addChart=(function(){


	return function(div, title, result, options){


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
	};

})();



var addDonut=(function(){


	return function(div, title, result, options){


		options=ObjectAppend_({
			height:340
		}, options);

		var chart = new Keen.Dataviz()
		.el(div instanceof HTMLElement?div:document.getElementById(div))
		.height(options.height)
		.title(title)
		.type("donut")
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
	};



})();


