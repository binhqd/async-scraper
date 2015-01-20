<?php
$app = app();


// $url = "http://www.bitter.co.il";
$url = $app->request->get('url');

$round = new Round(array(
    "baseUrl" => 'http://www.bitter.co.il'
));

$round->request(array(
    'url' => $url,
    'type' => 'GET',
    'data' => array(),
    "success" => function ($response, &$round)
    {
        global $dir, $dataDir, $dataFile, $products, $url;
        
        $parser = app()->helper("Parser");
        
        $content = $response->getBody()->getContents();
        
        //$content = preg_replace('/[a-zA-Z0-9\/\r\n+]{128,}/', "", $content);
//         $content = strip_tags($content, '<p><a><h1><span><div><table><tr><td><html><body><head><li><label><font><select><option>');
        
        // $content = iconv("UTF-8","ISO-8859-1//IGNORE",$content);
        // $content = iconv("ISO-8859-1","UTF-8",$content);
        // $content = iconv('UTF-8', 'ASCII//TRANSLIT', $content);
        
        // file_put_contents(TMP_DIR . "/tmp.html", $content);
//         header("Content-type: text/html");
//         echo $content;
//         exit;
        
        $html = str_get_html($content);
        
        $product = array();
        
        $name = $parser->getTextBetweenTags($content, "h1");
        
        $serial = $html->find('.product-page .part2 .catalog', 0);
        preg_match("/ ([a-zA-Z0-9]+)/", $serial->plaintext, $matches);
        $serial = $matches[1];
        
        $price = $html->find('.product-page .part3 .controls .prices .price', 0);
        $price = $price->plaintext;
        
        $selectAttributes = array();
        foreach ($html->find('.product-page .part3 .controls label span') as $span) {
            $selectAttribute = $span->parent();
            $attributeName = $selectAttribute->find('span', 0)->plaintext;
            // get option list
            $options = array();
            foreach ($selectAttribute->find('select option') as $option) {
                $adjustment = $option->getAttribute('data-adjustment');
                
                // Ignore default option
                $value = $option->getAttribute('value');
                if (empty($value))
                    continue;
                
                $optionName = $option->plaintext;
                $options[] = array(
                    "adjustment" => $adjustment,
                    "name" => $optionName
                );
            }
            
            $selectAttributes[] = array(
                "name" => $attributeName,
                "options" => $options
            );
        }
        
        $fixedAttribues = array();
        
        foreach ($html->find('.product-page .part3 .controls span.attribute-list span') as $attribute) {
            $adjustment = $attribute->getAttribute('data-adjustment');
            
            $optionName = $attribute->plaintext;
            $fixedAttribues[] = array(
                "adjustment" => $adjustment,
                "name" => $optionName
            );
        }
        
        $app = app();
        $menu = $app->request->get('menu');
        $submenu = $app->request->get('subMenu');
        $subMenu_lv1 = $app->request->get('subMenu_lv1');
        $productName = $app->request->get('productName');

        $product = array(
            "name" => $name,
            "serial" => $serial,
            "price" => $price,
            "selectAttributes"  => $selectAttributes,
            "fixedAttributes"   => $fixedAttribues,
            "href"              => $url,
            // category info
            'menu'              => $menu,
            'submenu'           => $submenu,
            'subMenu_lv1'       => $subMenu_lv1

        );
        
        // $dir = TMP_DIR . "/{$menu}/{$submenu}/{$subMenu_lv1}/{$productName}";
        // @mkdir($dir);
        // @chmod($dir, 0777);
        
        // save product information
        
        
        $dataDir = TMP_DIR . "/".md5($menu)."/".md5($submenu)."/".md5($subMenu_lv1)."/" . md5($productName) . "/";
        
        $dataFile = $dataDir . "product.txt";
        
        file_put_contents($dataFile, serialize($product));

        file_put_contents(TMP_DIR . "/allproducts/" . md5(uniqid()) . ".txt", serialize($product))
        
        header("Content-type: application/json");
        echo json_encode($product);
    }
));
// ========================