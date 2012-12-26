<?php

function geo_destination($start,$dist,$brng){
	$brng = 360 - $brng;
	$brng = $brng + 180;
	while ($brng > 359) {
		$brng = $brng - 360;
	}
    $lat1 = toRad($start[0]);
    $lon1 = toRad($start[1]);
    $dist = $dist/6371.01; //Earth's radius in km
    $brng = toRad($brng);
 
    $lat2 = asin( sin($lat1)*cos($dist) +
                  cos($lat1)*sin($dist)*cos($brng) );
    $lon2 = $lon1 + atan2(sin($brng)*sin($dist)*cos($lat1),
                          cos($dist)-sin($lat1)*sin($lat2));
    $lon2 = fmod(($lon2+3*pi()),(2*pi())) - pi();  
 
    return array(toDeg($lat2),toDeg($lon2));
}
function toRad($deg){
    return $deg * pi() / 180;
}
function toDeg($rad){
    return $rad * 180 / pi();
}

?>