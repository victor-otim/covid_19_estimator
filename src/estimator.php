<?php
define('BASEPATH', $_SERVER['DOCUMENT_ROOT'] .'/');


function covid19ImpactEstimator($data)
{	
	$estimates['data'] = $data;
	
	# Get number of days and factor
	$data['timeFactor'] = timeFactor($data['timeToElapse'], $data['periodType']);
		
	$estimates['impact'] = impact($data); 
		
	$estimates['severeImpact'] = severeImpact($data);
	
	$estimates['severeCasesByRequestedTime'] = round($estimates['severeImpact']['infectionsByRequestedTime'] * 0.15);
	
	$estimates['hospitalBedsByRequestedTime'] = hospitalBedsByRequestedTime($data['totalHospitalBeds'], $estimates['severeCasesByRequestedTime']);
	
	$estimates['casesForICUByRequestedTime'] = round(0.05 * $estimates['severeCasesByRequestedTime']);
	
	$estimates['casesForVentilatorsByRequestedTime'] =  round(0.02 * $estimates['severeCasesByRequestedTime']);
	
	$estimates['dollarsInFlight'] = round($estimates['severeImpact']['infectionsByRequestedTime'] * 0.65 * 1.5 * 30, 2);
	
	return $estimates;
}

function timeFactor ($timeToElapse = 0, $periodType = 'days')
{
	$factor = 0;
	
	switch($periodType):
	
		case 'days':
			
			$pow_number = floor($timeToElapse / 3);
			
			break;
		
		case 'weeks':
			
			$days = $timeToElapse * 7;
		
			$pow_number = floor($days / 3);
			
			break;
		
		case 'months':
			
			$days = $timeToElapse * 30;
		
			$pow_number = floor($days / 3);
		
			break;
		
		default:
			
			$pow_number = 0;
	
	endswitch;
	
	return pow(2, $pow_number);
}

function hospitalBedsByRequestedTime ($totalHospitalBeds, $severeCases)
{
	$availableBeds = 0.35 * $totalHospitalBeds;
	
	return round($availableBeds>$severeCases? $availableBeds : $availableBeds - $severeCases);
}


function impact($data)
{
	$impact['currentlyInfected'] = round($data['reportedCases'] * 10);
	
	$impact['infectionsByRequestedTime'] = round($impact['currentlyInfected'] * $data['timeFactor']);
	
	return $impact;
}


function severeImpact($data)
{
	$severeImpact['currentlyInfected'] = round($data['reportedCases'] * 50);
	
	$severeImpact['infectionsByRequestedTime'] = round($severeImpact['currentlyInfected'] * $data['timeFactor']);
	
	return $severeImpact;
}
