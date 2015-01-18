<?php
$countryIndex = file_get_contents(TMP_DIR . "/index.txt");
$countryIndex = unserialize($countryIndex);

$app = app();
$parser = $app->helper("Parser");

$countryName = app()->request->get('country');
$countryName = $parser->sanitize($countryName);

$dataFile = TMP_DIR . "/{$countryName}/data.txt";

header("Content-type: application/json");
if (!file_exists($dataFile)) {
    echo json_encode(array(
        'status'    => 401,
        'message'   => "Invalid country information"
    ));
    
    exit();
}

$stateIndex = file_get_contents($dataFile);
    
$states = $parser->parseStates($stateIndex);

foreach ($states as $state) {
    $stateName = $parser->sanitize($state['text']);
    
    $app->prepareDir("/{$countryName}/$stateName");
}

$indexFile = TMP_DIR . "/{$countryName}/index.txt";
file_put_contents($indexFile, serialize($states));

$output = array(
    'country'   => app()->request->get('country'),
    'states'    => $states
);
echo json_encode($output);

