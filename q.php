<?php



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

		public function countDistinctGroupsIps($where=null){

			if($results = $this->conn->query(
				'SELECT count(*) as count, data FROM (SELECT  data, ip FROM event'.$this->_w($where).' GROUP BY data, ip) as t GROUP BY data'
			)){
				return $results->fetch_all(MYSQLI_ASSOC);
			}
			return [];
	
		}

		public function distributionThreshold($field, $n, $comparator){
			return 'SELECT '.$field.' FROM (SELECT '.$field.', count(*) as count, data FROM event GROUP BY '.$field.') as a WHERE a.count'. $comparator.$n;
		}

		public function distribution($field, $where=null){

			if($results = $this->conn->query(
				'SELECT '.$field.', count(*) as count, data FROM event'.$this->_w($where).' GROUP BY '.$field
			)){
				return $results->fetch_all(MYSQLI_ASSOC);
			}
			return [];
	
		}

		public function distinct($field, $where=null){

			if($results = $this->conn->query(
				'SELECT DISTINCT '.$field.' FROM event'.$this->_w($where)
			)){
				return $results->fetch_all(MYSQLI_ASSOC);
			}
			return [];
	
		}



		public function monthRanges($n=12, $map=null){


			$thisMonth = strtotime(date('Y-m').'-01');


			$nextMonth=strtotime(date('Y-m', $thisMonth+(3600*24*20)).'-01');
			if($thisMonth==$nextMonth){
				$nextMonth=strtotime(date('Y-m', $thisMonth+(3600*24*35)).'-01');
			}

			$end=$nextMonth;

			$dateRanges=array();

			for($i=0;$i<$n;$i++){


				$start=strtotime(date('Y-m', $end-(3600*24*10)).'-01');

				array_unshift($dateRanges, array(

 					'start'=>$start,
 					'end'=>$end,
				));

				$end=$start;

			}


			if($map instanceof \Closure){

				return array_map(function($range)use ($map){

					return $map($range['start'], $range['end']);

				}, $dateRanges);

			}



			return $dateRanges;


		}



	}