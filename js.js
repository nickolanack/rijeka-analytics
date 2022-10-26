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