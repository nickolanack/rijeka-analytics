<?php

date_default_timezone_set('Europe/Zagreb');


$q=function(){

	$db=json_decode(file_get_contents('../.sql.json'));

	$conn = new mysqli('localhost', $db->username, $db->password, 'rijeka');
	if ((!$conn)||$conn->connect_error) {
	  die("Connection failed: " . $conn->connect_error);
	}

	include_once __DIR__.'/q.php';
	return new Q($conn);

};
$q=$q();



$ipmap=json_decode(file_get_contents('../.ipmap.json'), true);
$countries=array();

$localIps=array();

foreach($ipmap as $ip=>$country){
	if(!array_key_exists($country, $countries)){
		$countries[$country]=0;
	}
	$countries[$country]++;

	if($country==='Croatia'){
		$localIps[]=$ip;
	}
}


$regionmap=json_decode(file_get_contents('../.ipregionmap.json'), true);
$regions=array();

foreach($regionmap as $ip=>$data){

	$continent=$data['continent_name'];

	if(!array_key_exists($continent, $regions)){
		$regions[$continent]=array(
			'cities'=>array()
		);
	}

	$city=$data['city'];
	if(!array_key_exists($city, $regions[$continent]['cities'])){
		$regions[$continent]['cities'][$city]=array(
			'counter'=>0,
			'name'=>$city,
			'location'=>array($data['latitude'], $data['longitude'])
		);
	}
	$regions[$continent]['cities'][$city]['counter']++;
	
}


$fileAge=time()-filemtime('../.ipmap.json');
if($fileAge>3600){
	touch('../.ipmap.json');
	shell_exec('php ./geocodeip.php '.escapeshellarg($_SERVER['HTTP_HOST']).' > .log.txt 2>&1 &');
}




?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="viewport" content="width=500, initial-scale=1" />
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

		<script type="text/javascript" src="js.js"></script>

		<script type="text/javascript" src="https://d26b395fwzu5fz.cloudfront.net/keen-analysis-1.2.2.js"></script>
		<script type="text/javascript" src="https://d26b395fwzu5fz.cloudfront.net/keen-dataviz-1.1.3.js"></script>
		<link type="text/css" href="https://d26b395fwzu5fz.cloudfront.net/keen-dataviz-1.1.3.css" rel="stylesheet">

		<link type="text/css" href="css.css" rel="stylesheet">

		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js" ></script>
		<script type="text/javascript" src="js.js"></script>
		
	</head>
	<body>
		<?php 
		include __DIR__.'/main.php'; 
		?>
	</body>

	<script type="text/javascript">


			var europeData=<?php echo json_encode(array_map(function($city){

				return array(
					$city['location'][0],
					$city['location'][2],
					$city['name'],
					$city['counter'],
					'Unique IPs'
				);

			}, $regions['Europe']['cities'])); ?>;


			var northAmericaData=<?php echo json_encode(array_map(function($city){

				return array(
					$city['location'][0],
					$city['location'][2],
					$city['name'],
					$city['counter'],
					'Unique IPs'
				);

			}, $regions['North America']['cities'])); ?>;


			var chartData=<?php echo json_encode(array_map(function($country)use($countries){

					return array(
						$country,
						$countries[$country]
					);

				}, array_keys($countries)), JSON_PRETTY_PRINT); ?>;

			var apiKey=<?php echo json_encode(file_get_contents('../.key.txt')); ?>;

			googleMapCharts.init(apiKey);

			googleMapCharts.drawCloroplethMap('150', chartData, 'regions_div');
			googleMapCharts.drawCloroplethMap('world', chartData, 'regions_div_');

			//googleMapCharts.drawCloroplethMarkerMap('HR', chartData, 'regions_cluster_div');
			//googleMapCharts.drawCloroplethMarkerMap('021', chartData, 'regions_cluster_div_');


		addDonut('donut_local', 'Croatia total views', {result:(function(){

			return <?php

				$iplist=implode(', ', array_map(function($ip){ return json_encode($ip); }, $localIps));

				echo json_encode(array(
					array(
						'Croatia', $q->count('WHERE ip in ('.$iplist.')'),
					), array(
						'Other', $q->count('WHERE ip not in ('.$iplist.')')
					)
				));

			?>;


		})()},{
			colors:["rgb(0, 187, 222)", "#72949a"]
		});


		addDonut('donut_local_views', 'Croatia unique users', {result:(function(data){

			var out={'Croatia':0, 'Other':0};
			data.forEach(function(item){
				if(typeof out[item[0]]!='undefined'){
					out[item[0]]+=item[1];
					return;
				}
				out['Other']+=item[1];
			});

			return Object.keys(out).map(function(key){
				return [key,out[key]];
			});



		})(chartData)},{
			colors:["rgb(0, 187, 222)", "#72949a"]
		});



		<?php 



	
		$regionsFilters=$q->getRegionsFilters($iplist);


		foreach($regionsFilters as $reg){



			?>




				addMetric('metric_total<?php echo $reg->id; ?>', "Total App Section Views", <?php echo json_encode(array('result'=>$q->count($reg->where))); ?>,{
					colors:['rgb(254, 102, 114)']
				});
				addMetric('metric_ips<?php echo $reg->id; ?>', "Unique IPs", <?php echo json_encode(array('result'=>$q->countDistinct('ip', $reg->where))); ?>);



				<?php
				
				$today = strtotime(date('Y-m-d'));

				?>

				addMetric('metric_today<?php echo $reg->id; ?>', "Today", <?php echo json_encode(array('result'=>$q->countDistinct('ip','WHERE timestamp >= '.$today.$reg->and))); ?>);




				<?php

				$last7days = strtotime(date('Y-m-d', time()-(3600*24*7)));

				?>

				addMetric('metric_7days<?php echo $reg->id; ?>', "Last 7 days", <?php echo json_encode(array('result'=>$q->countDistinct('ip','WHERE timestamp >= '.$last7days.$reg->and))); ?>);




				<?php

				$thisMonth = strtotime(date('Y-m').'-01');

				?>

				addMetric('metric_month<?php echo $reg->id; ?>', "This month", <?php echo json_encode(array('result'=>$uniqueThistMonth=$q->countDistinct('ip','WHERE timestamp >= '.$thisMonth.$reg->and))); ?>);




				<?php



				$lastMonth=strtotime(date('Y-m', $thisMonth-(3600*24*10)).'-01');

				?>

				addMetric('metric_lastMonth<?php echo $reg->id; ?>', "Last month", <?php echo json_encode(array('result'=>$uniqueLastMonth=$q->countDistinct('ip','WHERE timestamp >= '.$lastMonth.' AND timestamp < '.$thisMonth.$reg->and))); ?>);


		<?php

		}

		?>

		addChart('chart_12_months', 'Last 12 months', {result:<?php echo json_encode($q->monthRanges(12, function($start, $end) use($q){

				return $q->toTimeframe(array(

 					'start'=>date('Y-m', $start),
 					'end'=>date('Y-m', $end),
 					'Unique Users'=>$q->countDistinct('ip','WHERE timestamp >= '.$start.' AND timestamp < '.$end),
 					'Total Section Views'=>$q->count('WHERE timestamp >= '.$start.' AND timestamp < '.$end)
				));


			}), JSON_PRETTY_PRINT);?>});



		addChart('chart_12_months_region', 'Croatia Only last 12 months', {result:<?php echo json_encode($q->monthRanges(12, function($start, $end) use($q, $iplist){

				return $q->toTimeframe(array(

 					'start'=>date('Y-m', $start),
 					'end'=>date('Y-m', $end),
 					'Unique Users'=>$q->countDistinct('ip','WHERE ip in ('.$iplist.') AND timestamp >= '.$start.' AND timestamp < '.$end),
 					'Total Section Views'=>$q->count('WHERE ip in ('.$iplist.') AND timestamp >= '.$start.' AND timestamp < '.$end)
				));


			}), JSON_PRETTY_PRINT);?>});






		<?php 


			$formatted=$q->formatGroups($q->countDistinctGroupsIps(), 'filterTour');

			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_tours_div_unique').appendChild(new Element('div')), 
						<?php echo json_encode($key); ?>, 
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200
						});
				<?php
			}
		?>




		
		<?php 

			$formatted=$formatted=$q->formatGroups($q->countDistinctGroups(), 'filterTour');

			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_tours_div').appendChild(new Element('div')), 
						<?php echo json_encode($key); ?>, 
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200,
							colors:['rgb(254, 102, 114)']
						});
				<?php
			}
		?>




		<?php 


			$formatted=$q->formatGroups($q->countDistinctGroupsIps(), 'filterCategory');

			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_categories_div_unique').appendChild(new Element('div')), 
						<?php echo json_encode($key); ?>, 
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200
						});
				<?php
			}
		?>




		
		<?php 

			$formatted=$formatted=$q->formatGroups($q->countDistinctGroups(), 'filterCategory');

			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_categories_div').appendChild(new Element('div')), 
						<?php echo json_encode($key); ?>, 
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200,
							colors:['rgb(254, 102, 114)']
						});
				<?php
			}
		?>


		<?php 


			$formatted=$q->formatGroups($q->countDistinctGroupsIps(), 'filterResearcher');

			foreach(array_keys($formatted) as $k){
				if(strpos($k, '{')===0){
					unset($formatted[$k]);
				}
			}

			arsort($formatted);

			$formatted=array_slice($formatted, 0, 6);
			$keysForNext=array_keys($formatted);

			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_researchers_div_unique').appendChild(new Element('div')), 
						<?php echo json_encode($key); ?>, 
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200
						});
				<?php
			}
		?>




		
		<?php 

			$formatted=$formatted=$q->formatGroups($q->countDistinctGroups(), 'filterResearcher');

			$formatted=array_map(function($k)use($formatted){
				return $formatted[$k];
			}, $keysForNext);

			$formatted=array_combine($keysForNext, $formatted);

			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_researchers_div').appendChild(new Element('div')), 
						<?php echo json_encode($key); ?>, 
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200,
							colors:['rgb(254, 102, 114)']
						});
				<?php
			}
		?>






		<?php 

		$values=$q->histogram($q->distribution('ip'), 'log2');

		?>


		var distribution=<?php echo json_encode(array_map(function($value){

			return array(
				'label'=>$value['range'][0].' - '.$value['range'][1].' Section views',
				'result'=>$value['value']
			);

		}, $values), JSON_PRETTY_PRINT);?>


		addChart('chart_distribution', 'Unique user activity distribution (log2)', {result:([]).concat(distribution), query:{
			//group_by:'activity'
		}}, {
			colors:["#91a8a0"]
			 //colors:["#66cdaa"]
		});




		<?php 


			$formatted=$formatted=$q->formatGroups($q->countDistinctGroups(' WHERE ip in ('. $q->distributionThreshold('ip', 16, '>=') .') '), 'filterTour');


			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_tours_div_active_items').appendChild(new Element('div')), 
						<?php echo json_encode($key); ?>, 
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200,
							colors:["#66cdaa"]
						});
				<?php
			}
		?>



		<?php 

			$formatted=$formatted=$q->formatGroups($q->countDistinctGroups(' WHERE ip in ('. $q->distributionThreshold('ip', 16, '<') .') '), 'filterTour');

			foreach ($formatted as $key => $value) {
				?>
					addMetric(
						document.getElementById('metrics_tours_div_casual').appendChild(new Element('div')), 
						"Casual",
						<?php echo json_encode(array('result'=>$value)); ?>,
						{
							height:200,
							colors:["#f0f0f0"]
						});
				<?php
			}
		?>



		





		(function(year){


			addChart('chart_12_months_active', 'Active and casual users - last 12 months', {result:year},{
				colors:["#66cdaa", "#e0e0e0"]
			});




		})(<?php echo json_encode(array_map(function($range) use($q){

			return $q->toTimeframe(array(

					'start'=>date('Y-m', $range['start']),
					'end'=>date('Y-m', $range['end']),
					'Active Users'=>$q->countDistinct('ip', 'WHERE timestamp >= '. $range['start'] .' AND timestamp < '. $range['end'] .' AND ip in ('. $q->distributionThreshold('ip', 16, '>=') .')'),
					'Casual Users'=>$q->countDistinct('ip', 'WHERE timestamp >= '. $range['start'] .' AND timestamp < '. $range['end'] .' AND ip in ('. $q->distributionThreshold('ip', 16, '<') .')')
			));


		}, $q->monthRanges(12)), JSON_PRETTY_PRINT);?>);









	

		addDonut('donut_active', 'All time active and casual', {result:<?php 


			$active=$q->countDistinct('ip', 'WHERE ip in ('. $q->distributionThreshold('ip', 16, '>=') .')');
 			$casual=$q->countDistinct('ip', 'WHERE ip in ('. $q->distributionThreshold('ip', 16, '<') .')');

		 			echo json_encode(array(
		 				array(
		 					'name'=>'Casual Users',
		 					'result'=>$casual
		 				),
		 				array(
		 					'name'=>'Active Users',
		 					'result'=>$active
		 				)
		 			));


			?>},{
			colors:["#e0e0e0","#66cdaa"]
		});

	

		addDonut('donut_active_last1Months', 'Last 3 months active and casual', {result:<?php 


			$range=$q->monthRanges(3)[0];


			$active=$q->countDistinct('ip', 'WHERE timestamp >= '. $range['start'] .' AND ip in ('. $q->distributionThreshold('ip', 16, '>=') .')');
 			$casual=$q->countDistinct('ip', 'WHERE timestamp >= '. $range['start'] .' AND ip in ('. $q->distributionThreshold('ip', 16, '<') .')');

		 			echo json_encode(array(
		 				array(
		 					'name'=>'Casual Users',
		 					'result'=>$casual
		 				),
		 				array(
		 					'name'=>'Active Users',
		 					'result'=>$active
		 				)
		 			));


			?>},{
			colors:["#e0e0e0","#66cdaa"]
		});




	
		var returns=<?php echo json_encode(array_map(function($value){

			return array(
				'label'=>$value['range'][0].' - '.$value['range'][1].' Days',
				'result'=>$value['value']
			);

		}, $q->histogram($q->distinctDayIntervals('ip'), 'log2', 'days')), JSON_PRETTY_PRINT);?>


		addChart('chart_retention', 'User retention - days of activity (log2)', {result:([]).concat(returns), query:{
			//group_by:'activity'
		}}, {
			colors:["#91a8a0"]
			 //colors:["#66cdaa"]
		});





	</script>
</html>