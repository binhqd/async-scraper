<?php
$app = app();
$parser = $app->helper("Parser");

// $url = "http://www.bitter.co.il";
$url = $app->request->get('url');
$menu = $app->request->get('menu');
$submenu = $app->request->get('subMenu');
$subMenu_lv1 = $app->request->get('subMenu_lv1');

// ========================
$dataDir = TMP_DIR . "/".md5($menu)."/".md5($submenu)."/".md5($subMenu_lv1)."/";
$dataFile = $dataDir . "index.txt";

$round = new Round(array(
    "baseUrl" => 'http://www.bitter.co.il'
));

$products = array();

if (! empty($refresh) || ! file_exists($dataFile)) {
    $round->request(array(
        'url' => $url,
        'type' => 'GET',
        'data' => array(),
        "success" => function ($response, &$round)
        {
            global $dir, $dataDir, $dataFile, $products;
            
            $content = $response->getBody()->getContents();
            
            $html = str_get_html($content);
            
            $mainMenu = array();
            
            foreach ($html->find('.product-box') as $product) {
                $info = $product->find('a', 0);
                
                $productName = base64_encode($info->plaintext);
                $href = $info->getAttribute('href');
                
                // $img = $product->find('a img', 0)->getAttribute('src');
                
                $products[] = [
                    "name" => $productName,
                    "href" => $href
                ]
                // "image" => $img
                ;
                
                $dir = $dataDir . "/" . md5($productName);
                @mkdir($dir);
                @chmod($dir, 0777);
            }
            
            file_put_contents($dataFile, serialize($products));
        }
    ));
} else {
    $products = file_get_contents($dataFile);
    $products = unserialize($products);
}
// ========================

header("Content-type: application/json");
echo json_encode($products);