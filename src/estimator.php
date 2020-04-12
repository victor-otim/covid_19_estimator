<?php
define('BASEPATH', $_SERVER['DOCUMENT_ROOT'] .'/');

#define('BASEPATH', $_SERVER['DOCUMENT_ROOT'] .'/covid_estimator/covid-19-estimator-php/');

function covid19ImpactEstimator($data)
{	
	$estimates['data'] = $data;
	
	# Get number of days and factor
	$timeFactor = timeFactor($data['timeToElapse'], $data['periodType']);
	
	$data['timeFactor'] = $timeFactor['factor'];
	
	$data['days'] = $timeFactor['days'];
		
	$estimates['impact'] = impact($data); 
		
	$estimates['severeImpact'] = severeImpact($data);
	
	return $estimates;
}

function timeFactor ($timeToElapse = 0, $periodType = 'days')
{
	$factor = 0;
	$days = 0;
	
	switch($periodType):
	
		case 'days':
			
			$days = $timeToElapse;
			
			$pow_number = intval($timeToElapse / 3);
			
			$numOfDays = $timeToElapse;
			
			break;
		
		case 'weeks':
			
			$days = $timeToElapse * 7;
		
			$pow_number = intval($days / 3);
			
			break;
		
		case 'months':
			
			$days = $timeToElapse * 30;
		
			$pow_number = intval($days / 3);
		
			break;
		
		default:
			
			$pow_number = 0;
	
	endswitch;
	
	$factor = pow(2, $pow_number);
	
	return array('factor'=>$factor, 'days'=>$days);
}

function hospitalBedsByRequestedTime ($totalHospitalBeds, $severeCases)
{
	$availableBeds = 0.35 * $totalHospitalBeds;
	
	return intval($availableBeds>$severeCases? $availableBeds : $availableBeds - $severeCases);
}


function impact($data)
{
	$impact['currentlyInfected'] = $data['reportedCases'] * 10;
	
	$impact['infectionsByRequestedTime'] = $impact['currentlyInfected'] * $data['timeFactor'];
	
	$impact['severeCasesByRequestedTime'] = intval($impact['infectionsByRequestedTime'] * 0.15);
	
	$impact['hospitalBedsByRequestedTime'] = hospitalBedsByRequestedTime($data['totalHospitalBeds'], $impact['severeCasesByRequestedTime']);	
	
	$impact['casesForICUByRequestedTime'] = intval(0.05 * $impact['infectionsByRequestedTime']);
	
	$impact['casesForVentilatorsByRequestedTime'] =  intval(0.02 * $impact['infectionsByRequestedTime']);
	
	$impact['dollarsInFlight'] = round($impact['infectionsByRequestedTime'] * 0.65 * 1.5 * $data['days'], 2);
	
	return $impact;
}


function severeImpact($data)
{
	$severeImpact['currentlyInfected'] = $data['reportedCases'] * 50;
	
	$severeImpact['infectionsByRequestedTime'] = $severeImpact['currentlyInfected'] * $data['timeFactor'];
	
	$severeImpact['severeCasesByRequestedTime'] = intval($severeImpact['infectionsByRequestedTime'] * 0.15);
	
	$severeImpact['hospitalBedsByRequestedTime'] = hospitalBedsByRequestedTime($data['totalHospitalBeds'], $severeImpact['severeCasesByRequestedTime']);
	
	$severeImpact['casesForICUByRequestedTime'] = intval(0.05 * $severeImpact['infectionsByRequestedTime']);
	
	$severeImpact['casesForVentilatorsByRequestedTime'] =  intval(0.02 * $severeImpact['infectionsByRequestedTime']);
	
	$severeImpact['dollarsInFlight'] = round($severeImpact['infectionsByRequestedTime'] * 0.65 * 1.5 * $data['days'], 2);
	
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