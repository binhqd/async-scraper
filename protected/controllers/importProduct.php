<?php
error_reporting(E_ALL);
$app = app();
$parser = $app->helper("Parser");

$dir = TMP_DIR;

$md5 = $app->request->get('md5');

$dataDir = TMP_DIR . "/allproducts/";
$dataFile = $dataDir . "{$md5}.txt";

$product = file_get_contents($dataFile);
$product = unserialize($product);

$sql = "select * from submenu where name = :name";
$dataSubmenu = $app->db->query($sql, array(
    ':name' => $product['submenu']
));

$dataSubmenu = current($dataSubmenu);
$product['submenu_id'] = $dataSubmenu['id'];

$sql = "select * from submenu_lv1 where name = :name and submenu_id = :submenu_id";
$dataSubmenu_lv1 = $app->db->query($sql, array(
    ':name' => $product['subMenu_lv1'],
    ':submenu_id' => $dataSubmenu['id']
));


$dataSubmenu_lv1 = current($dataSubmenu_lv1);
$product['submenu_lv1_id'] = $dataSubmenu_lv1['id'];

if (empty($dataSubmenu_lv1)) {
    header("HTTP/1.0 404 Not Found");
    exit();
}
$submenu_lv1_id = $dataSubmenu_lv1['id'];

function importProduct($product)
{
    global $app, $submenu_lv1_id;
    
    $lines = array();
    $lines[] = implode(",", array(html_entity_decode($product['name']), $product['serial'], $product['price']));
    
    // insert product
    $sql = "INSERT into products (name,serial,price,submenu_lv1_id) VALUES (:name,:serial,:price,:submenu_lv1_id)";
    
    $app->db->exec($sql, array(
        ':name' => html_entity_decode($product['name']),
        ':serial' => $product['serial'],
        ':price' => $product['price'],
        ':submenu_lv1_id'   => $product['submenu_lv1_id']
    ));
    
    $productID = $app->db->getConnection()->lastInsertId();
    
    // insert fix attributes
    foreach ($product['fixedAttributes'] as $fixAttribute) {
        $sql = "INSERT into attributes (name,adjustment,is_fix,product_id) VALUES (:name,:adjustment,:is_fix,:product_id)";
        
        $app->db->exec($sql, array(
            ':name' => html_entity_decode($fixAttribute['name']),
            ':adjustment' => $fixAttribute['adjustment'],
            ':is_fix' => 1,
            ':product_id' => $productID
        ));
        
        $lines[] = implode(",", array('', '', '', html_entity_decode($fixAttribute['name']), $fixAttribute['adjustment']));
    }
    
    // insert option attributes
    foreach ($product['selectAttributes'] as $selectAttribute) {
        $sql = "INSERT into attributes (name,adjustment,is_fix,product_id) VALUES (:name,:adjustment,:is_fix,:product_id)";
        
        $app->db->exec($sql, array(
            ':name' => html_entity_decode($selectAttribute['name']),
            ':adjustment' => 0,
            ':is_fix' => 0,
            ':product_id' => $productID
        ));
        
        $attributeID = $app->db->getConnection()->lastInsertId();
        
        foreach ($selectAttribute['options'] as $option) {
            $sql = "INSERT into sub_attributes (name,adjustment,attribute_id) VALUES (:name,:adjustment,:attribute_id)";
            
            $app->db->exec($sql, array(
                ':name' => html_entity_decode($option['name']),
                ':adjustment' => $option['adjustment'],
                ':attribute_id' => $attributeID
            ));
            
            $columns = array(
                '',
                '',
                '',
                html_entity_decode($option['name']),
                $option['adjustment']
            );
            $lines[] = implode(",", $columns);
        }
    }
    
    foreach ($lines as $item) {
        file_put_contents(TMP_DIR . "/output.csv", $item . "\n", FILE_APPEND);
    }
}

importProduct($product);
exit('done');