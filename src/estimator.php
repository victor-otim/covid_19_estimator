<?php
define('BASEPATH', $_SERVER['DOCUMENT_ROOT'] .'/');

#define('BASEPATH', $_SERVER['DOCUMENT_ROOT'] .'/covid_estimator/covid-19-estimator-php/');

function covid19ImpactEstimator($data)
{	
	$estimates['data'] = $data;
	
	# Get number of days and factor
	$data['timeFactor'] = timeFactor($data['timeToElapse'], $data['periodType']);
		
	$estimates['impact'] = impact($data); 
		
	$estimates['severeImpact'] = severeImpact($data);
	
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
	
	return floor($availableBeds>$severeCases? $availableBeds : $availableBeds - $severeCases);
}


function impact($data)
{
	$impact['currentlyInfected'] = floor($data['reportedCases'] * 10);
	
	$impact['infectionsByRequestedTime'] = floor($impact['currentlyInfected'] * $data['timeFactor']);
	
	$impact['severeCasesByRequestedTime'] = floor($impact['infectionsByRequestedTime'] * 0.15);
	
	$impact['hospitalBedsByRequestedTime'] = hospitalBedsByRequestedTime($data['totalHospitalBeds'], $impact['severeCasesByRequestedTime']);	
	
	$impact['casesForICUByRequestedTime'] = floor(0.05 * $impact['severeCasesByRequestedTime']);
	
	$impact['casesForVentilatorsByRequestedTime'] =  floor(0.02 * $impact['severeCasesByRequestedTime']);
	
	$impact['dollarsInFlight'] = floor($impact['infectionsByRequestedTime'] * 0.65 * 1.5 * 30);
	
	return $impact;
}


function severeImpact($data)
{
	$severeImpact['currentlyInfected'] = floor($data['reportedCases'] * 50);
	
	$severeImpact['infectionsByRequestedTime'] = floor($severeImpact['currentlyInfected'] * $data['timeFactor']);
	
	$severeImpact['severeCasesByRequestedTime'] = floor($severeImpact['infectionsByRequestedTime'] * 0.15);
	
	$severeImpact['hospitalBedsByRequestedTime'] = hospitalBedsByRequestedTime($data['totalHospitalBeds'], $severeImpact['severeCasesByRequestedTime']);
	
	$severeImpact['casesForICUByRequestedTime'] = floor(0.05 * $severeImpact['severeCasesByRequestedTime']);
	
	$severeImpact['casesForVentilatorsByRequestedTime'] =  floor(0.02 * $severeImpact['severeCasesByRequestedTime']);
	
	$severeImpact['dollarsInFlight'] = floor($severeImpact['infectionsByRequestedTime'] * 0.65 * 1.5 * 30);
	
	return $severeImpact;
}


# handle form post
if(!empty($_POST['goestimate'])):
	
	# remove goestimate from post data
	unset($_POST['goestimate']);
	
	$estimates = covid19ImpactEstimator($_POST);
	
	print '<pre>';
    print_r($estimates);
    print '</pre>';

endif;