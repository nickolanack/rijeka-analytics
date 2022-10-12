<?php

// if(!isset($argv)){
// 	die('can only be run from term');
// }


// if(basename($argv[0])!==basename(__FILE__)){
// 	die('can only be run directly from term');
// }

$file='../.ipmap.json';
if(!file_exists($file)){
	die('expects ipmap file');
}


$domain='some.domain.com';

if(isset($_SERVER['HTTP_HOST'])){
	$domain=$_SERVER['HTTP_HOST'];
}

if(isset($argv)&&count($argv)>1){
	$domain=$argv[1];
}



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


		public function distinct($field, $where=null){

			if($results = $this->conn->query(
				'SELECT DISTINCT '.$field.' FROM event'.$this->_w($where)
			)){
				return $results->fetch_all(MYSQLI_ASSOC);
			}
			return [];
	
		}

	}

	return new Q($conn);

};
$q=$q();

$ips=$q->distinct('ip');

$options = array(
  'https'=>array(
    'method'=>"GET",
    'header'=>"User-Agent: keycdn-tools:https://fra.geoforms.ca\r\n" // i.e. An iPad 
  )
);

$context = stream_context_create($options);

$map=json_decode(file_get_contents($file), true);


$max=700;
foreach($ips as $value){

	$value=(object) $value; //make sure it is an object

	if(key_exists($value->ip, $map)){
		continue;
	}

	$ch = curl_init();
	$headers = array('User-Agent: keycdn-tools:https://'.$domain);
	$url='https://tools.keycdn.com/geo.json?host='.$value->ip;

	echo $url."\n";

	curl_setopt($ch, CURLOPT_URL, $url); # URL to post to
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers ); # custom headers, see above
	$result = curl_exec( $ch ); # run!
	curl_close($ch);
		
	$obj=json_decode($result);
	print_r($obj);
	if(isset($obj->status)&&isset($obj->data->geo->country_name)){
		$map[$value->ip]=$obj->data->geo->country_name;
	}
	$max--;
	echo $max."\n";
	if($max<=0){
		break;
	}
	
	if($max%10===0){
		file_put_contents($file, json_encode($map, JSON_PRETTY_PRINT));
		sleep(5);
	}
	if($max%20===0){
		file_put_contents($file, json_encode($map, JSON_PRETTY_PRINT));
		sleep(10);
	}

	sleep(1);
	
}

file_put_contents($file, json_encode($map, JSON_PRETTY_PRINT));

