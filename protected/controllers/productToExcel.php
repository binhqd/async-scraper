<?php
error_reporting(E_ALL);
$app = app();
$parser = $app->helper("Parser");

$dir = TMP_DIR;

$menu = $app->request->get('menu');
$submenu = $app->request->get('subMenu');
$subMenu_lv1 = $app->request->get('subMenu_lv1');
$productName = $app->request->get('productName');

$dataDir = TMP_DIR . "/" . md5($menu) . "/" . md5($submenu) . "/" . md5($subMenu_lv1) . "/" . md5($productName) . "/";
$dataFile = $dataDir . "product.txt";

$sql = "select * from submenu where name = :name";
$dataSubmenu = $app->db->query($sql, array(
    ':name' => base64_decode($submenu)
));
$dataSubmenu = current($dataSubmenu);

$sql = "select * from submenu_lv1 where name = :name and submenu_id = :submenu_id";
$dataSubmenu_lv1 = $app->db->query($sql, array(
    ':name' => base64_decode($subMenu_lv1),
    ':submenu_id' => $dataSubmenu['id']
));

$dataSubmenu_lv1 = current($dataSubmenu_lv1);

if (empty($dataSubmenu_lv1)) {
    header("HTTP/1.0 404 Not Found");
    exit();
}
$submenu_lv1_id = $dataSubmenu_lv1['id'];

function importProduct($dataFile)
{
    global $app, $submenu_lv1_id;
    
    $rows = array();
    if (! file_exists($dataFile)) {
        header("HTTP/1.0 404 Not Found");
        exit();
    }
    // exit('here');
    $product = file_get_contents($dataFile);
    $product = unserialize($product);
    
    
    
    $fixedAttributes = $product['fixedAttributes'];
    $selectAttributes = $product['selectAttributes'];
    
    unset($product['fixedAttributes']);
    unset($product['selectAttributes']);
    $rows[] = implode(",", $product);
    // insert fix attributes
    foreach ($fixedAttributes as $fixAttribute) {
        $columns = array(
            '',
            '',
            '',
            $fixAttribute['name'],
            $fixAttribute['adjustment']
        );
        $rows[] = implode(",", $columns);
    }
    // insert option attributes
    foreach ($selectAttributes as $selectAttribute) {
        
        $sql = "INSERT into attributes (name,adjustment,is_fix,product_id) VALUES (:name,:adjustment,:is_fix,:product_id)";
        
        $app->db->exec($sql, array(
            ':name' => $selectAttribute['name'],
            ':adjustment' => 0,
            ':is_fix' => 0,
            ':product_id' => $productID
        ));
        
        $attributeID = $app->db->getConnection()->lastInsertId();
        
        foreach ($selectAttribute['options'] as $option) {
            $columns = array(
                '',
                '',
                '',
                $option['name'],
                $option['adjustment']
            );
            $rows[] = implode(",", $columns);
        }
    }
    
    foreach ($rows as $item) {
        file_put_contents(TMP_DIR . "/output.csv", $item . "\n", FILE_APPEND);
    }
}

importProduct($dataFile);
exit('done');