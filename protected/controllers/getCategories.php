<?php
$app = app();
$parser = $app->helper("Parser");

$dir = TMP_DIR;

if (! is_dir($dir)) {
    throw new Exception("Folder /{$dir}/ doesn't exist");
}

$dataFile = $dir . "/index.txt";

$url = "http://www.bitter.co.il";
$round = new Round(array(
    "baseUrl" => $url
));

$categories = array();

if (! empty($refresh) || ! file_exists($dataFile)) {
    $round->request(array(
        'url' => "/",
        'type' => 'GET',
        'data' => array()
        // "id" => $stateID
        ,
        "success" => function ($response, &$round)
        {
            global $dir, $dataFile, $categories;
            
            $content = $response->getBody()->getContents();
            
            // save data
            $html = str_get_html($content);
            
            $mainMenu = array();
            
            $categories = array();
            
            foreach ($html->find('#big-menu li') as $menu) {
                $class = $menu->getAttribute('class');
                
                if (! preg_match("/big\-menu\-([\d]+)/", $class, $matches)) {
                    continue;
                }
                $menuID = $matches[1];
                
                $menuName = $menu->plaintext;
                // TODO: Check menu name
                $menuName = base64_encode($menuName);
                
                // Create menu folder
                // TODO: Need to check
                $menuDir = TMP_DIR . "/" . md5($menuName);
                mkdir($menuDir);
                chmod($menuDir, 0777);
                
                $submenu_lv0 = array();
                $i = 0;
                
                foreach ($html->find("#big-sub-menu-{$menuID} .level0 ul li a") as $subMenu) {
                    $subMenuName = $subMenu->plaintext;
                    $subMenuName = base64_encode($subMenuName);
                    
                    $subMenuDir = $menuDir . "/" . md5($subMenuName);
                    mkdir($subMenuDir);
                    chmod($subMenuDir, 0777);
                    
                    // get submenu level 1
                    $subMenu_lv1_container = $html->find("#big-sub-menu-{$menuID} .level1 ul ul", $i);
                    
                    foreach ($subMenu_lv1_container->find("li a") as $subMenu_lv1) {
                        $subMenu_lv1_name = $subMenu_lv1->plaintext;
                        $subMenu_lv1_name = base64_encode($subMenu_lv1_name);
                        
                        $href = $subMenu_lv1->getAttribute('href');
                        
                        $subMenu_lv1_dir = $subMenuDir . "/" . md5($subMenu_lv1_name);
                        mkdir($subMenu_lv1_dir);
                        chmod($subMenu_lv1_dir, 0777);
                        
                        $categories[] = [
                            "menu" => [
                                "name" => $menuName,
                                "index" => $menuID
                            ],
                            "subMenu" => [
                                "name" => $subMenuName,
                                "link" => $href
                            ],
                            "subMenu_lv1" => [
                                "name" => $subMenu_lv1_name,
                                "href" => $href
                            ]
                        ];
                    }
                    
                    $i ++;
                }
            }
            
            file_put_contents($dataFile, serialize($categories));
        }
    ));
} else {
    $categories = file_get_contents($dataFile);
    $categories = unserialize($categories);
}

header("Content-type: application/json");
echo json_encode($categories);