<?php
require_once ("CommonComponent.php");

class PRequest extends CommonComponent
{

    /**
     * This method is used to return value of GET param
     *
     * @param string $getParam            
     * @param string $default            
     * @return Ambigous <string, unknown>
     */
    public function get($getParam, $default = "")
    {
        $val = $default;
        if (isset($_GET[$getParam])) {
            $val = urldecode($_GET[$getParam]);
        }
        return $val;
    }

    /**
     * This method is used to return value of POST param
     *
     * @param string $getParam            
     * @param string $default            
     * @return Ambigous <string, unknown>
     */
    public function post($getParam, $default = "")
    {
        $val = $default;
        if (isset($_POST[$getParam])) {
            $val = $_POST[$getParam];
        }
        return $val;
    }
}
