<?php

function hospitalBedsByRequestedTime ($totalHospitalBeds, $covidCases)
{
	$availableBeds = 0.35 * $totalHospitalBeds;
	
	$currentUsage = ($availableBeds>$covidCases? $availableBeds : $availableBeds - $covidCases);
}


function impact($data)
{
	$impact['currentlyInfected'] = $data['reportedCases'] * 10;
	
	$impact['infectionsByRequestedTime'] = $impact['currentlyInfected'] * 512;
}


function severeImpact($data)
{
	$severeImpact['currentlyInfected'] = $data['reportedCases'] * 50;
	
	$severeImpact['infectionsByRequestedTime'] = $impact['currentlyInfected'] * 512;
}

function covid19ImpactEstimator($data)
{
	$input = $data;
	
	$data['data'] = $input;
	
	$data['impact'] = impact($data); 
		
	$data['severeImpact'] = severeImpact($data);
	
	$data['severeCasesByRequestedTime'] = $data['severeImpact']['infectionsByRequestedTime'] * 0.15;
	
	$input['hospitalBedsByRequestedTime'] = hospitalBedsByRequestedTime($input['totalHospitalBeds'], $data['severeCasesByRequestedTime']);
	
	$data['casesForICUByRequestedTime'] = 0.05 * $data['severeCasesByRequestedTime'];
	
	$data['casesForVentilatorsByRequestedTime'] =  0.02 * $data['severeCasesByRequestedTime'];
	
	$data['dollarsInFlight'] = $data['severeImpact']['infectionsByRequestedTime'] * 0.65 * 1.5 * 30;;
	
	return $data;
}