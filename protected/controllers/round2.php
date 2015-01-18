<?php
// app()->importModel("Round2");
$round = new Round(array(
    "baseUrl" => 'http://easyscript4u.com'
));

$parser = app()->helper("Parser");

$countryID = app()->request->get('countryID');
$refresh = app()->request->get('refresh');

$countryName = app()->request->get('countryName');
$countryName = $parser->sanitize($countryName);

$countryDir = TMP_DIR . "/{$countryName}";

if (! is_dir($countryDir)) {
    throw new Exception("Folder for country '{$countryName}' doesn't exist");
}

$dataFile = $countryDir . "/data.txt";

if (! empty($refresh) || ! file_exists($dataFile)) {
    $round->request(array(
        'url' => "/demo/csc_add/ajax_state.php",
        'type' => 'POST',
        'data' => array(
            "id" => $countryID
        ),
        "success" => function ($response, &$round)
        {
            global $countryDir, $dataFile;
            
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

// Return output
header("Content-type: application/json");
$out = array("status"   => 200);
echo json_encode($out);
