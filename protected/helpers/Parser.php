<?php

class Parser extends CommonComponent
{

    public function parseDropdown($str)
    {
        $pattern = "/<option value=\"([a-zA-Z 0-9]+)\">([\w \(\)\'\.\-]+)<\/option>/";
        
        if (preg_match_all($pattern, $str, $matches)) {
            $ret = array();
            for ($i = 0; $i < count($matches[1]); $i ++) {
                $item = array(
                    "value" => $matches[1][$i],
                    "text" => trim(rtrim(ltrim($matches[2][$i])))
                );
                
                $ret[] = $item;
            }
            
            return $ret;
        } else {
            throw new Exception("Non match");
        }
    }

    public static function sanitize($str)
    {
        $str = str_replace("'", "", $str);
        return $str;
    }

    public static function slug($str)
    {}

    public function parseStates($str)
    {
        // <option value="1">Ghazni</option>
        $pattern = "/<option value=\"([a-zA-Z 0-9]+)\">([\w \(\)\'\.\-]+)<\/option>/";
        
        if (preg_match_all($pattern, $str, $matches)) {
            $ret = array();
            for ($i = 0; $i < count($matches[1]); $i ++) {
                $item = array(
                    "value" => $matches[1][$i],
                    "text" => trim(rtrim(ltrim($matches[2][$i])))
                );
                
                $ret[] = $item;
            }
            
            return $ret;
        } else {
            return array();
        }
    }
    
    public function parseCities($str)
    {
        // <option value="1">Ghazni</option>
        $pattern = "/<option value=\"([a-zA-Z 0-9]+)\">([\w \(\)\'\.\-]+)<\/option>/";
        
        if (preg_match_all($pattern, $str, $matches)) {
            $ret = array();
            for ($i = 0; $i < count($matches[1]); $i ++) {
                $item = array(
                    "value" => $matches[1][$i],
                    "text" => trim(rtrim(ltrim($matches[2][$i])))
                );
                
                $ret[] = $item;
            }
            
            return $ret;
        } else {
            return array();
        }
    }
}