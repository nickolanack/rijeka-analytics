<?php




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
				<div id="metrics_div" style="width: 900px; height: 500px;">
					<div id="metric_total"></div>
					<div id="metric_ips"></div>
				</div>

				<h2>Curated tour views</h2>
				<div id="metrics_tours_div" style="width: 900px; height: 500px;">
					
				</div>

			<section>

		</main>
	</body>

	<script type="text/javascript">


		var addMetric=function(div, title, result){

			var chart = new Keen.Dataviz()
			.el(div instanceof HTMLElement?div:document.getElementById(div))
			.height(240)
			.title(title)
			.type("metric")
			.prepare();

			chart.data(result).render();


		};

		addMetric('metric_total', "Total Events", <?php echo json_encode(array('result'=>$q->count())); ?>);
		addMetric('metric_ips', "Unique IPs", <?php echo json_encode(array('result'=>$q->countDistinct('ip'))); ?>);
		
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
					addMetric(document.getElementById('metrics_tours_div').appendChild(new Element('div')), <?php echo json_encode($key); ?>, <?php echo json_encode(array('result'=>$value)); ?>);
				<?php
			}
		?>

	</script>
</html>