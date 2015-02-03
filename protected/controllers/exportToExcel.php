<?php
error_reporting(E_ALL);
$app = app();
$parser = $app->helper("Parser");

$dir = TMP_DIR;

$products = array();
$sql = "select * from menu order by id";
$dataMenu = $app->db->query($sql, array());

foreach ($dataMenu as $menu) {
    $row = array(
        "menu" => $menu['name']
    );
    
    $sql = "select * from submenu where menu_id = :menu_id order by id";
    $dataSubmenu = $app->db->query($sql, array(
        ':menu_id' => $menu['id']
    ));
    
    $row['submenu'] = $menu['name'];
    
    foreach ($dataSubmenu as $subMenu) {
        $row['submenu'] = $subMenu['name'];
        
        $sql = "select * from submenu_lv1 where submenu_id = :submenu_id order by id";
        $dataSubmenu_lv1 = $app->db->query($sql, array(
            ':submenu_id' => $subMenu['id']
        ));
        
        foreach ($dataSubmenu_lv1 as $submenu_lv1) {
            $row['submenu_lv1'] = $submenu_lv1['name'];
            
            $row['products'] = array();
            
            // get products of current category
            $sql = "select * from products where submenu_lv1_id = :submenu_lv1_id order by name";
            $dataProducts = $app->db->query($sql, array(
                ':submenu_lv1_id' => $submenu_lv1['id']
            ));
            
            foreach ($dataProducts as $dataProduct) {
                $productItem = array(
                    'name' => $dataProduct['name'],
                    'attributes' => array()
                );
                
                $item = implode(",", array(
                    html_entity_decode($dataProduct['name']),
                    $dataProduct['serial'],
                    $dataProduct['price']
                ));
                file_put_contents(TMP_DIR . "/output.csv", $item . "\n", FILE_APPEND);
                
                $sql = "select * from attributes where product_id = :product_id order by is_fix desc";
                $dataAttributes = $app->db->query($sql, array(
                    ':product_id' => $dataProduct['id']
                ));
                
                foreach ($dataAttributes as $dataAttribute) {
                    if ($dataAttribute['is_fix'] == 1) {
                        $productItem['attributes'][] = array(
                            'name' => $dataAttribute['name'],
                            'adjustment' => $dataAttribute['adjustment']
                        );
                        $item = implode(",", array(
                            '',
                            '',
                            '',
                            html_entity_decode($dataAttribute['name']),
                            $dataAttribute['adjustment']
                        ));
                        file_put_contents(TMP_DIR . "/output.csv", $item . "\n", FILE_APPEND);
                    }
                    
                    if ($dataAttribute['is_fix'] == 0) {
                        $sql = "select * from sub_attributes where attribute_id = :attribute_id order by name";
                        $dataSubAttributes = $app->db->query($sql, array(
                            ':attribute_id' => $dataAttribute['id']
                        ));
                        
                        foreach ($dataSubAttributes as $dataSubAttribute) {
                            $productItem['attributes'][] = array(
                                'name' => $dataSubAttribute['name'],
                                'adjustment' => $dataSubAttribute['adjustment']
                            );
                            
                            $item = implode(",", array(
                                '',
                                '',
                                '',
                                html_entity_decode($dataSubAttribute['name']),
                                $dataSubAttribute['adjustment']
                            ));
                            file_put_contents(TMP_DIR . "/output.csv", $item . "\n", FILE_APPEND);
                        }
                    }
                }
                
                $row['product'] = $productItem;
                // $row['products'][] = $productItem;
                
                $products[] = $row;
            }
            
            // $products[] = $row;
        }
    }
}

dump(array(
    $products,
    count($products)
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
    $lines[] = implode(",", array(
        html_entity_decode($product['name']),
        $product['serial'],
        $product['price']
    ));
    
    // insert product
    $sql = "INSERT into products (name,serial,price,submenu_lv1_id) VALUES (:name,:serial,:price,:submenu_lv1_id)";
    
    $app->db->exec($sql, array(
        ':name' => html_entity_decode($product['name']),
        ':serial' => $product['serial'],
        ':price' => $product['price'],
        ':submenu_lv1_id' => $product['submenu_lv1_id']
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
        
        $lines[] = implode(",", array(
            '',
            '',
            '',
            html_entity_decode($fixAttribute['name']),
            $fixAttribute['adjustment']
        ));
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
    
    // foreach ($lines as $item) {
    // file_put_contents(TMP_DIR . "/output.csv", $item . "\n", FILE_APPEND);
    // }
}

importProduct($product);
exit('done');