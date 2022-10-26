'use strict'

var drawCloroplethMap = (function(){


	google.charts.load('current', {
		'packages':['geochart'],
	});



	var loaded=false;
	function onLoadCharts() {
		loaded=true;
		if(buff.length){
			var b=buff.slice(0);
			buff=[];
			b.forEach(function(f){ f(); });
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

		buff.push(render);


	};

	google.charts.load('current', {
		'packages':['geochart'],
	});
	google.charts.setOnLoadCallback(onLoadCharts);

	return drawCloroplethMap;

})();