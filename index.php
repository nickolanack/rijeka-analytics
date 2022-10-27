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



			var chartData=<?php echo json_encode(array_map(function($country)use($countries){

					return array(
						$country,
						$countries[$country]
					);

				}, array_keys($countries)), JSON_PRETTY_PRINT); ?>;

			drawCloroplethMap(150, chartData, 'regions_div');
			drawCloroplethMap('world', chartData, 'regions_div_');


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



		addMetric('metric_total', "Total App Section Views", <?php echo json_encode(array('result'=>$q->count())); ?>,{
			colors:['rgb(254, 102, 114)']
		});
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


			$results12Months=$q->monthRanges(12, function($start, $end) use($q){

				return array(

 					'start'=>date('Y-m', $start),
 					'end'=>date('Y-m', $end),
 					'unique'=>$q->countDistinct('ip','WHERE timestamp >= '.$start.' AND timestamp < '.$end),
 					'total'=>$q->count('WHERE timestamp >= '.$start.' AND timestamp < '.$end)
				);


			});
		

		?>

		var year=<?php echo json_encode(array_map(function($value){

			return array(
				'value'=>array(

					array(
						'name'=>'Unique Users',
						'result'=>$value['unique']
					),
					array(
						'name'=>'Total Section Views',
						'result'=>$value['total']
					)

				),
				'timeframe'=>array(
					'start'=>$value['start'].'-01',
					'end'=>$value['end'].'-01',
				)

			);

		}, $results12Months), JSON_PRETTY_PRINT);?>


		addChart('chart_12_months', 'Last 12 months', {result:year});






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
				return $formatted[$k
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