<?php

require 'index.php';

if(isset($_POST['submit'])){
if(isset($_GET['go'])){
if(preg_match("/^[  a-zA-Z]+/", $_POST['name'])){
	$name=$_POST['name'];
	$db=mysql_connect("HOST", "DB",  "PASSWORD") or die ("I cannot connect to the database  because: " . mysql_error());
	//-select  the database to use
	$mydb=mysql_select_db("creceren_search");
	search($name);
  	}
}
}


class Digger
{
	public $searchLimit = 19;
	
	public function search($for_text){
	
	global $search_params;
	$search_params = words($for_text);
	$wrds = array();
	
	foreach ($search_params as $param){
		array_push ($wrds, "stem = '$param'");}
	
	$word_sql = "SELECT * from words where 'join(" or ", $wrds)'";
	global $search_words;
	$search_words = mysql_query($word_sql);
	$tables = array();
	$joins = array();
	$ids = array();
	foreach($search_words as $w => $index){
		array_push ($tables, "locations loc$index");
		array_push ($joins, "loc$index.page_id = loc($index+1).page_id");
		array_push ($ids, "loc$index.word_id = $w.id");}
	array_pop($joins);
	global $common_select;
	$common_select = "from join(', ', $tables) where join(', ', ($joins + $ids)) group by loc0.page_id";
	
	array_slice(rank(),0,$searchLimit);
	}
	
	public function rank(){
		merge_rankings(frequency_ranking, location_ranking, distance_ranking);
	}
	
	public function frequency_ranking(){
		$freq_sql = "select loc0.page_id, count(loc0.page_id) as count $common_select order by count desc";
		$list = mysql_query($freq_sql);
		$rank = array();
		$end = count($list);
		for($i = 0; $i < $end; $i++){
			$rank[$list[i].page_id] = floatval(array_sum($list[i]))/floatval(array_sum($list[0]));
		}
		return $rank;
	}
	
	public function location_ranking(){
		$total = array();
		foreach($search_words as $w => $index){
			array_push($total, "loc$index.position + 1");}
		$loc_sql = "select loc0.page_id, join(' + ', $total) as total $common_select order by total asc";
		$list = mysql_query($loc_sql);
		$rank = array();
		$end = count($list);
		for($i = 0; $i < $end; $i++){
			$rank[$list[i].page_id] = floatval(array_sum($list[0]))/floatval(array_sum($list[i]));
		}
		return $rank;
	}
	
	public function distance_ranking(){
		if(count($search_words) == 1){
			$empty = array();	
			return $empty;
		}
		$dist = array();
		$total = array();
		foreach($search_words as $w => $index){
			array_push($total, "loc$index.position");}
		$end = count($total);
		for($i = 0; $i < $end; $i++){
			if ($i != ($end - 1)){
				$j = $i + 1;
				array_push($dist, "abs($total[$i] - $total[$j])");
			}
		}
		$dist_sql = "select loc0.page_id, join(' + ', $dist) as dist $common_select order by dist asc";
		$list = mysql_query($dist_sql);
		$rank = array();
		$end = count($list);
		for($i = 0; $i < $end; $i++){
			$rank[$list[i].page_id] = floatval($list[0].dist)/floatval($list[i].dist);
		}
		return $rank;
	}
	
	public function merge_rankings($rankings){
		$r = array();
		foreach($rankings as $ranking){
			array_merge($r, $ranking);
		}
		asort($r);
	}
	
}
?>