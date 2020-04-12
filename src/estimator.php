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
	
	return round($availableBeds>$severeCases? $availableBeds : $availableBeds - $severeCases, 0, PHP_ROUND_HALF_DOWN);
}


function impact($data)
{
	$impact['currentlyInfected'] = round($data['reportedCases'] * 10, 0, PHP_ROUND_HALF_DOWN);
	
	$impact['infectionsByRequestedTime'] = round($impact['currentlyInfected'] * $data['timeFactor'], 0, PHP_ROUND_HALF_DOWN);
	
	$impact['severeCasesByRequestedTime'] = round($impact['infectionsByRequestedTime'] * 0.15, 0, PHP_ROUND_HALF_DOWN);
	
	$impact['hospitalBedsByRequestedTime'] = hospitalBedsByRequestedTime($data['totalHospitalBeds'], $impact['severeCasesByRequestedTime']);	
	
	$impact['casesForICUByRequestedTime'] = round(0.05 * $impact['severeCasesByRequestedTime'], 0, PHP_ROUND_HALF_DOWN);
	
	$impact['casesForVentilatorsByRequestedTime'] =  round(0.02 * $impact['severeCasesByRequestedTime'], 0, PHP_ROUND_HALF_DOWN);
	
	$impact['dollarsInFlight'] = round($impact['infectionsByRequestedTime'] * 0.65 * 1.5 * 30, 0, PHP_ROUND_HALF_DOWN);
	
	return $impact;
}


function severeImpact($data)
{
	$severeImpact['currentlyInfected'] = round($data['reportedCases'] * 50, 0, PHP_ROUND_HALF_DOWN);
	
	$severeImpact['infectionsByRequestedTime'] = round($severeImpact['currentlyInfected'] * $data['timeFactor'], 0, PHP_ROUND_HALF_DOWN);
	
	$severeImpact['severeCasesByRequestedTime'] = round($severeImpact['infectionsByRequestedTime'] * 0.15, 0, PHP_ROUND_HALF_DOWN);
	
	$severeImpact['hospitalBedsByRequestedTime'] = hospitalBedsByRequestedTime($data['totalHospitalBeds'], $severeImpact['severeCasesByRequestedTime']);
	
	$severeImpact['casesForICUByRequestedTime'] = round(0.05 * $severeImpact['severeCasesByRequestedTime'], 0, PHP_ROUND_HALF_DOWN);
	
	$severeImpact['casesForVentilatorsByRequestedTime'] =  round(0.02 * $severeImpact['severeCasesByRequestedTime'], 0, PHP_ROUND_HALF_DOWN);
	
	$severeImpact['dollarsInFlight'] = round($severeImpact['infectionsByRequestedTime'] * 0.65 * 1.5 * 30, 0, PHP_ROUND_HALF_DOWN);
	
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