<?php
include('libs/mining.php');
$script = new \Mining\MiningComponent ();

$calls = $script->mining ( 'http://finance.yahoo.com/q/op?s=AAPL',  \Mining\MiningComponent::CALLS);

$puts = $script->mining ( 'http://finance.yahoo.com/q/op?s=AAPL',  \Mining\MiningComponent::PUTS);

$data = array(
	'calls'	=> $calls['rows'],
	'puts'	=> $puts['rows']
);

header("Content-type: application/json");
echo json_encode($data);