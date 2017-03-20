<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class cls_map {
	/*
	 * lat : 纬度 ，lng : 经度
	 */
	static function get_distance($lng1, $lat1, $lng2, $lat2 , $len = 0) {
		$earthRadius = 6367000;
		$lat1 = ($lat1 * pi() ) / 180;
		$lng1 = ($lng1 * pi() ) / 180;

		$lat2 = ($lat2 * pi() ) / 180;
		$lng2 = ($lng2 * pi() ) / 180;

		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
		$calculatedDistance = $earthRadius * $stepTwo;

		return round($calculatedDistance , $len);
	}
	//根据经纬度和距离获取最大和最小范围
	static function get_range($lng , $lat , $distance) {
		//以下为核心代码
		$range = 180 / pi() * $distance / 6372.797;     //里面的 1 就代表搜索 1km 之内，单位km
		$lngR = $range / cos($lat * pi() / 180);
		$arr_reutrn = array();
		$arr_reutrn['max_lat'] = $lat + $range;//最大纬度
		$arr_reutrn['min_lat'] = $lat - $range;//最小纬度
		$arr_reutrn['max_lng'] = $lng + $lngR;//最大经度
		$arr_reutrn['min_lng'] = $lng - $lngR;//最小经度
		return $arr_reutrn;
	}
}