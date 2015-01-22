<?php
$app = app();
$parser = $app->helper("Parser");

// $url = "http://www.bitter.co.il";
$allProductsDir = TMP_DIR . "/allproducts/";
$files = scandir($allProductsDir);

// ========================
array_shift($files);
array_shift($files);

$output = array();
foreach ($files as $file) {
    $product = file_get_contents(TMP_DIR . "/allproducts/{$file}");
    $product = unserialize($product);
    
    $output[] = array(
        "name"  => html_entity_decode($product['name']),
        "md5"  => str_replace(".txt", "", $file)
    );
}

header("Content-type: application/json");
echo json_encode($output);