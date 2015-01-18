<?php
$app = app();
$parser = $app->helper("Parser");

$country = $app->request->get('country');
$country = $parser->sanitize($country);

$state = $app->request->get('state');
$state = $parser->sanitize($state);

$stateID = $app->request->get('stateID');

$dir = TMP_DIR . "/{$country}/{$state}/";

if (! is_dir($dir)) {
    throw new Exception("Folder /{$country}/{$state}/ doesn't exist");
}

$dataFile = $dir . "/data.txt";

$round = new Round(array(
    "baseUrl" => 'http://easyscript4u.com'
));

$content = "";

if (! empty($refresh) || ! file_exists($dataFile)) {
    $round->request(array(
        'url' => "/demo/csc_add/ajax_city.php",
        'type' => 'POST',
        'data' => array(
            "id" => $stateID
        ),
        "success" => function ($response, &$round)
        {
            global $dir, $dataFile, $content;
            
            $content = $response->getBody()
                ->getContents();
            
            // save data
            file_put_contents($dataFile, $content);
        }
    ));
} else {
    $content = file_get_contents($dataFile);
}

// Processing data here
// parsing city
$cities = $parser->parseCities($content);

// Return output
$indexFile = $dir . "/index.txt";

file_put_contents($indexFile, serialize($cities));

header("Content-type: application/json");
$output = array(
    'country'   => $app->request->get('country'),
    'state'     => $state = $app->request->get('state'),
    'cities'    => $cities
);
echo json_encode($output);

