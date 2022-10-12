<?php

date_default_timezone_set('Europe/Zagreb');


$q=function(){

	$db=json_decode(file_get_contents('../.sql.json'));

	$conn = new mysqli('localhost', $db->username, $db->password, 'rijeka');
	if ((!$conn)||$conn->connect_error) {
	  die("Connection failed: " . $conn->connect_error);
	}


	class Q{

		protected $conn;
		public function __construct($conn){
			$this->conn=$conn;
		}

		protected function _w($where=null){
			return empty($where)?'':' '.$where;

		}

		public function count($where=null){

			if($results = $this->conn->query('SELECT count(*) as count FROM event'.$this->_w($where))){
				foreach ($results as $result) {
				 	return intval($result['count']);
				 } 
			}
			return 0;
	
		}

		public function countDistinct($field, $where=null){

			if($results = $this->conn->query(
				'SELECT count(*) as count FROM (SELECT DISTINCT '.$field.' FROM event'.$this->_w($where).') as a'
			)){
				foreach ($results as $result) {
				 	return intval($result['count']);
				 } 
			}
			return 0;
	
		}

		public function countDistinctGroups($where=null){

			if($results = $this->conn->query(
				'SELECT count(*) as count, data FROM event'.$this->_w($where).' GROUP BY data'
			)){
				return $results->fetch_all(MYSQLI_ASSOC);
			}
			return [];
	
		}

	}

	return new Q($conn);

};
$q=$q();

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
				var options = {
					colorAxis: {colors: ['#cccccc', 'rgb(0, 187, 222)']},
				};
				var chart = new google.visualization.GeoChart(document.getElementById('regions_div'));
				chart.draw(data, options);
			}




			


		</script>
		<style type="text/css">
			

			div#metrics_div>div {
			    width: 30%;
			    margin: 10px;
			    display: inline-block;
			}

			div#metrics_tours_div>div {
			    width: 30%;
			    margin: 10px;
			    display: inline-block;
			}

			div#metrics_div, div#metrics_tours_div  {
			    width: 900px;
			    margin: 50px auto;
			    text-align: center;
			}

			div#regions_div {
			    margin: auto;
			    text-align: center;
			    margin: 50px auto;
			}






			h1 {
			    font-family: sans-serif;
			    font-weight: 100;
			    font-size: 50px;
			    text-align: center;
			}

			h2 {
			    font-family: sans-serif;
			    font-weight: 100;
			    font-size: 40px;
			    text-align: center;
			}

			h1>span {
			    font-weight: 400;
			    color: #00bbde;
			    font-size: 70px;
			}


		</style>
	</head>
	<body>
		<main>
			<h1>Some Analytics. <span>Rijeka in Flux</span> mobile app</h1>
			<section>
				<div id="regions_div" style="width: 900px; height: 500px;">
					
				</div>
			<section>

			<section>
				<div id="metrics_div">
					<div id="metric_total"></div>
					<div id="metric_ips"></div>
					<div id="metric_today"></div>
					<div id="metric_7days"></div>
					<div id="metric_month"></div>
					<div id="metric_lastMonth"></div>
				</div>


				<div id="chart_12_months">
				</div>


				<h2>Curated tour views</h2>
				<div id="metrics_tours_div">
					
				</div>

			<section>

		</main>
	</body>

	<script type="text/javascript">


		var addMetric=function(div, title, result, options){

			options=ObjectAppend_({
				height:240
			}, options);

			var chart = new Keen.Dataviz()
			.el(div instanceof HTMLElement?div:document.getElementById(div))
			.height(options.height)
			.title(title)
			.type("metric")
			.prepare();

			chart.data(result).render();


		};


		var addChart=function(div, title, result, options){


			const chart = new KeenDataviz({
			  container: div instanceof HTMLElement?div:document.getElementById(div),
			  type: 'bar',
			  title: title,
			  stacking: 'percent',
			  legend: {
			  	position: 'bottom',
			  },
			  labelMapping: {
			  	total: 'Total',
			    unigue: 'Unique'
			  },
			  renderOnVisible: true,
			  palette: 'modern'
			});

			var example={"result": [


			{"value": [
					{"product.name": "apps", "result": 53}, 
					{"product.name": "books", "result": 47}, 
					{"product.name": "games", "result": 24}, 
					{"product.name": "sounds", "result": 76}
				], 
			"timeframe": {"start": "2019-03-20T00:00:00.000Z", "end": "2019-03-21T00:00:00.000Z"}
			

			}, {"value": [
				{"product.name": "apps", "result": 32}, 
				{"product.name": "books", "result": 24}, 
				{"product.name": "games", "result": 56}, 
				{"product.name": "sounds", "result": 32}], 

			"timeframe": {"start": "2019-03-21T00:00:00.000Z", "end": "2019-03-22T00:00:00.000Z"}


			}, {"value": [{"product.name": "apps", "result": 27}, {"product.name": "books", "result": 32}, {"product.name": "games", "result": 18}, {"product.name": "sounds", "result": 33}], "timeframe": {"start": "2019-03-22T00:00:00.000Z", "end": "2019-03-23T00:00:00.000Z"}}, {"value": [{"product.name": "apps", "result": 68}, {"product.name": "books", "result": 56}, {"product.name": "games", "result": 65}, {"product.name": "sounds", "result": 59}], "timeframe": {"start": "2019-03-23T00:00:00.000Z", "end": "2019-03-24T00:00:00.000Z"}}, {"value": [{"product.name": "apps", "result": 38}, {"product.name": "books", "result": 48}, {"product.name": "games", "result": 50}, {"product.name": "sounds", "result": 26}], "timeframe": {"start": "2019-03-24T00:00:00.000Z", "end": "2019-03-25T00:00:00.000Z"}}, {"value": [{"product.name": "apps", "result": 34}, {"product.name": "books", "result": 15}, {"product.name": "games", "result": 18}, {"product.name": "sounds", "result": 14}], "timeframe": {"start": "2019-03-25T00:00:00.000Z", "end": "2019-03-26T00:00:00.000Z"}}]}


				

			chart.render(result);
		}

		addMetric('metric_total', "Total Events", <?php echo json_encode(array('result'=>$q->count())); ?>);
		addMetric('metric_ips', "Unique IPs", <?php echo json_encode(array('result'=>$q->countDistinct('ip'))); ?>);



		<?php
		
		$today = strtotime(date('Y-m-d'));

		?>

		addMetric('metric_today', "Today", <?php echo json_encode(array('result'=>$q->countDistinct('ip','WHERE timestamp >= '.$today))); ?>);




		<?php

		$last7days = strtotime(date('Y-m-d', time()-(3600*24*7)));

		?>

		addMetric('metric_7days', "Last 7 days", <?php echo json_encode(array('result'=>$q->countDistinct('ip','WHERE timestamp >= '.$last7days))); ?>);




		<?php

		$thisMonth = strtotime(date('Y-m').'-01');

		?>

		addMetric('metric_month', "This month", <?php echo json_encode(array('result'=>$uniqueThistMonth=$q->countDistinct('ip','WHERE timestamp >= '.$thisMonth))); ?>);




		<?php



		$lastMonth=strtotime(date('Y-m', $thisMonth-(3600*24*10)).'-01');

		?>

		addMetric('metric_lastMonth', "Last month", <?php echo json_encode(array('result'=>$uniqueLastMonth=$q->countDistinct('ip','WHERE timestamp >= '.$lastMonth.' AND timestamp < '.$thisMonth))); ?>);




		<?php


			$nextMonth=strtotime(date('Y-m', $thisMonth+(3600*24*20)).'-01');
			if($thisMonth==$nextMonth){
				$nextMonth=strtotime(date('Y-m', $thisMonth+(3600*24*35)).'-01');
			}



			$end=$lastMonth;
			$results12Months=array(
				array(
					'start'=>date('Y-m', $lastMonth),
					'end'=>date('Y-m', $thisMonth),
 					'unique'=>$uniqueLastMonth,
 					'total'=>$q->count('WHERE timestamp >= '.$thisMonth)
				),
				array(
					'date'=>date('Y-m', $thisMonth),
					'end'=>date('Y-m', $nextMonth),
 					'unique'=>$uniqueThistMonth,
 					'total'=>$q->count('WHERE timestamp >= '.$lastMonth.' AND timestamp < '.$thisMonth)
				)
			);

			for($i=0;$i<10;$i++){


				$start=strtotime(date('Y-m', $end-(3600*24*10)).'-01');

				array_unshift($results12Months, array(
 					'date'=>date('Y-m', $start),
 					'unique'=>$q->countDistinct('ip','WHERE timestamp >= '.$start.' AND timestamp < '.$end),
 					'total'=>$q->count('WHERE timestamp >= '.$start.' AND timestamp < '.$end)
				));

				$end=$start;

			}
		

		?>

		var year=<?php echo json_encode(array_map(function($value){

			return array(
				'value'=>array(
					array(
						'name'=>'total',
						'result'=>$value['total']
					),
					array(
						'name'=>'unigue',
						'result'=>$value['unique']
					)

				),
				'timeframe'=>array(
					'start'=>$value['start'],
					'end'=>$value['end'],
				)

			);

		}, $results12Months), JSON_PRETTY_PRINT);?>


		addChart('chart_12_months', 'Last 12 Months', year);



		
		<?php 

			$formatted=[];

			foreach ($q->countDistinctGroups() as $result) {
				$data=json_decode($result['data']);
				if(isset($data->filter->filterTour)){

					$title=$data->filter->filterTour;
					$title=explode(':', $title);
					$title=array_pop($title);
					$title=trim($title);

					if(!array_key_exists($title, $formatted)){
						$formatted[$title]=0;
					}
					$formatted[$title]+=intval($result['count']);

					
				}
			}

			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_tours_div').appendChild(new Element('div')), 
						<?php echo json_encode($key); ?>, 
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200
						});
				<?php
			}
		?>

	</script>
</html>