<?php
error_reporting(E_ALL);
$app = app();
$parser = $app->helper("Parser");

$dir = TMP_DIR;

if (! is_dir($dir)) {
    throw new Exception("Folder /{$dir}/ doesn't exist");
}

$dataFile = $dir . "/index.txt";

$categories = file_get_contents($dataFile);
$categories = unserialize($categories);

$cnt = 0;
// hiarachize
$hiarachy = array();
foreach ($categories as $category) {
    $menu = base64_decode($category['menu']['name']);
    $subMenu = base64_decode($category['subMenu']['name']);
    $subMenu_lv1 = base64_decode($category['subMenu_lv1']['name']);
    
    if (!isset($hiarachy[$menu])) {
        $hiarachy[$menu] = [];
    }
    
    
    if (!isset($hiarachy[$menu][$subMenu])) {
        $hiarachy[$menu][$subMenu] = [];
    }
    
    $hiarachy[$menu][$subMenu][] = $subMenu_lv1;
}


foreach ($hiarachy as $menu => $submenus) {
    $sql = "INSERT into menu (name) VALUES (:menu)";
    
//     echo $sql . "<br/>";
    $app->db->exec($sql, array(
        ':menu'    => $menu
    ));
    
    $id = $app->db->getConnection()->lastInsertId();
    
    foreach ($submenus as $submenu => $subMenu_lv1) {
        $sql = "INSERT into submenu (name,menu_id) VALUES (:name,:menu_id)";
    
        $app->db->exec($sql, array(
            ':name'         => $submenu,
            ':menu_id'      => $id,
        ));
        
        $submenuID = $app->db->getConnection()->lastInsertId();
        
        foreach ($subMenu_lv1 as $item) {
            $sql = "INSERT into submenu_lv1 (name,submenu_id) VALUES (:name,:submenu_id)";
            $app->db->exec($sql, array(
                ':name'         => $item,
                ':submenu_id'   => $submenuID
            ));
        }
    }
}

exit('done');

echo 'Done';