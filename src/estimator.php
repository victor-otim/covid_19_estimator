<?php

function covid19ImpactEstimator($data)
{
	
	$data['impact']['currentlyInfected'] = $data['reportedCases'] * 10; 
	
	return $data;
}