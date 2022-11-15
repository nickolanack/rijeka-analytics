<?php

class Q {

	protected $conn;
	public function __construct($conn) {
		$this->conn = $conn;
	}

	protected function _w($where = null) {
		return empty($where) ? '' : ' ' . $where;

	}

	public function count($where = null) {

		if ($results = $this->conn->query('SELECT count(*) as count FROM event' . $this->_w($where))) {
			foreach ($results as $result) {
				return intval($result['count']);
			}
		}
		return 0;

	}

	public function countDistinct($field, $where = null) {

		if ($results = $this->conn->query(
			'SELECT count(*) as count FROM (SELECT DISTINCT ' . $field . ' FROM event' . $this->_w($where) . ') as a'
		)) {
			foreach ($results as $result) {
				return intval($result['count']);
			}
		}
		return 0;

	}

	public function countDistinctGroups($where = null) {

		if ($results = $this->conn->query(
			'SELECT count(*) as count, data FROM event' . $this->_w($where) . ' GROUP BY data'
		)) {
			return $results->fetch_all(MYSQLI_ASSOC);
		}
		return [];

	}

	public function countDistinctGroupsIps($where = null) {

		if ($results = $this->conn->query(
			'SELECT count(*) as count, data FROM (SELECT  data, ip FROM event' . $this->_w($where) . ' GROUP BY data, ip) as t GROUP BY data'
		)) {

			return $results->fetch_all(MYSQLI_ASSOC);
		}
		return [];

	}

	public function distinctDayIntervals($where = null) {

		if ($results = $this->conn->query(
			'SELECT count, maxinterval/(3600*24) as days FROM (SELECT count(*) as count, ip, (max(timestamp)-min(timestamp)) as maxinterval FROM event' . $this->_w($where) . ' GROUP BY ip) as t WHERE maxinterval>(3600*24);'
		)) {

			return $results->fetch_all(MYSQLI_ASSOC);
		}
		return [];

	}

	public function formatGroups($results, $section) {

		$formatted = [];

		foreach ($results as $result) {
			$data = json_decode($result['data']);
			if (isset($data->filter->$section)) {

				$title = $data->filter->$section;
				$title = explode(':', $title);
				$title = array_pop($title);
				$title = trim($title);

				if (!array_key_exists($title, $formatted)) {
					$formatted[$title] = 0;
				}
				$formatted[$title] += intval($result['count']);

			}
		}

		return $formatted;

	}

	public function distributionThreshold($field, $n, $comparator) {
		return 'SELECT ' . $field . ' FROM (SELECT ' . $field . ', count(*) as count, data FROM event GROUP BY ' . $field . ') as a WHERE a.count' . $comparator . $n;
	}

	public function distribution($field, $where = null) {

		if ($results = $this->conn->query(

			'SELECT ' . $field . ', count(*) as count, data FROM event' . $this->_w($where) . ' GROUP BY ' . $field
		)) {
			return $results->fetch_all(MYSQLI_ASSOC);
		}
		return [];

	}

	public function distinct($field, $where = null) {

		if ($results = $this->conn->query(
			'SELECT DISTINCT ' . $field . ' FROM event' . $this->_w($where)
		)) {
			return $results->fetch_all(MYSQLI_ASSOC);
		}
		return [];

	}

	public function monthRanges($n = 12, $map = null) {

		$thisMonth = strtotime(date('Y-m') . '-01');

		$nextMonth = strtotime(date('Y-m', $thisMonth + (3600 * 24 * 20)) . '-01');
		if ($thisMonth == $nextMonth) {
			$nextMonth = strtotime(date('Y-m', $thisMonth + (3600 * 24 * 35)) . '-01');
		}

		$end = $nextMonth;

		$dateRanges = array();

		for ($i = 0; $i < $n; $i++) {

			$start = strtotime(date('Y-m', $end - (3600 * 24 * 10)) . '-01');

			array_unshift($dateRanges, array(

				'start' => $start,
				'end' => $end,
			));

			$end = $start;

		}

		if ($map instanceof \Closure) {

			return array_map(function ($range) use ($map) {

				return $map($range['start'], $range['end']);

			}, $dateRanges);

		}

		return $dateRanges;

	}

	public function histogram($results, $type = 'log2', $field='count') {

		$group = function ($v) {
			return (int) $v;
		};
		$range = function ($i) {
			return [$i, $i + 1];
		};

		$useLogarithmicScale = function ($base) use (&$group, &$range) {

			$group = function ($v) use ($base) {
				return (int) log($v, $base);
			};

			$range = function ($i) use ($base) {
				return [pow($base, $i), pow($base, $i + 1)];
			};

		};

		$useLinearScale = function ($segmentSize) use (&$group, &$range) {

			$group = function ($v) use ($segmentSize) {
				return (int) $v / $segmentSize;
			};

			$range = function ($i) use ($segmentSize) {
				return [$i * $segmentSize, ($i + 1) * $segmentSize];
			};

		};

		if (strpos($type, 'log') === 0) {
			$base = intval(substr($type, 3));
			$useLogarithmicScale($base);
		}

		if (is_int($type)) {
			$useLinearScale($type);
		}

		$dist = array();

		$max = 0;
		foreach ($results as $result) {
			$index = $group($result[$field]); // $interact['count']/$groupSize;

			$max = max($index, $max);

			if (isset($dist[$index])) {
				$dist[$index]++;
				continue;
			}

			$dist[$index] = 1;
		}

		$values = array();

		for ($i = 0; $i <= $max; $i++) {
			$v=0;
			
			if (isset($dist[$i])) {
				$v = $dist[$i];
			} 

			$values[]=array('range'=>$range($i), 'value'=>$v);
		}

		return $values;

	}


	public function toTimeframe($value){

		$start=$value['start'];
		$end=$value['end'];

		unset($value['start']);
		unset($value['end']);

		$values=array();

		foreach($value as $label=>$v){
			$values[]=array(
				'name'=> $label,
				'result'=>$v
			);
		}

		return array(
			'value'=>$values,
			'timeframe'=>array(
				'start'=>$start.'-01',
				'end'=>$end.'-01',
			)
		);
	}





	public function getRegionsFilters($iplist){


		$sectionGroups=array(
			'all'=>'',
			'croatia'=>'ip in ('.$iplist.')',
			'other'=>'ip not in ('.$iplist.')'
		);



		$regions=array();


		foreach($sectionGroups as $section=>$filter){


			


			$regions[]=(object) array(
				'name'=>$section,
				'filter'=>$filter,
				'id'=>$section=='all'?'':('_'.$section),
				'where'=>$section=='all'?null:'WHERE '.$filter,
				'and'=>$section=='all'?'':' AND '.$filter,
			);

		}

		return $regions;


	}






}