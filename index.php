<?php


$db=json_decode(file_get_contents('../.sql.json'));

$conn = new mysqli('localhost', $db->username, $db->password, 'rijeka');
if ((!$conn)||$conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}



if($results = $conn->query('SELECT count(*) FROM events')){

	foreach ($results as $result) {
		print_r($result);
	}

}


?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Analytics</title>
		<script type="text/javascript">
			if(!window.console){
			window.console={
				"log":function(){},
				"error":function(){},
				"warn":function(){},
				"info":function(){}
			}
		}
		</script>
		<script type="text/javascript" src="https://aopfn.geoforms.ca/app/nickolanack/php-core-app/assets/js/ClassObject.js?1655843160"></script>
		<script type="text/javascript" src="https://aopfn.geoforms.ca/app/nickolanack/php-core-app/assets/js/Window.js?1655851684"></script>

		<script type="text/javascript" src="https://d26b395fwzu5fz.cloudfront.net/keen-analysis-1.2.2.js"></script>
		<script type="text/javascript" src="https://d26b395fwzu5fz.cloudfront.net/keen-dataviz-1.1.3.js"></script>
		<link type="text/css" href="https://d26b395fwzu5fz.cloudfront.net/keen-dataviz-1.1.3.css" rel="stylesheet">

		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js" ></script>

		<script type="text/javascript">
			google.charts.load('current', {
				'packages':['geochart'],
			});
			
			google.charts.setOnLoadCallback(drawRegionsMap);
			function drawRegionsMap() {
				var data = google.visualization.arrayToDataTable([
					['Country', 'Popularity'],
					['Germany', 200],
					['United States', 300],
					['Brazil', 400],
					['Canada', 500],
					['France', 600],
					['RU', 700]
					]);
				var options = {};
				var chart = new google.visualization.GeoChart(document.getElementById('regions_div'));
				chart.draw(data, options);
			}




			


		</script>
	</head>
	<body>
		<main>
			<section>
				<div id="regions_div" style="width: 900px; height: 500px;"></div>
			<section>

			<section>
				<div id="metrics_div" style="width: 900px; height: 500px;"></div>
			<section>

		</main>
	</body>

	<script type="text/javascript">
		
		var chart = new Keen.Dataviz()
			.el(document.getElementById('metrics_div'))
			.height(240)
			.title("Total Events")
			.type("metric")
			.prepare();

			chart.data({result:123}).render();

	</script>
</html>