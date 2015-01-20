<?php
require_once (LIBS_PATH . "/components/CommonComponent.php");
require_once (LIBS_PATH . "/components/PRequest.php");
require_once (LIBS_PATH . "Round.php");
require_once (LIBS_PATH . "connector/MySQLConnector.php");
$config = require("protected/config/app.php");
require_once (LIBS_PATH . "simple_html_dom.php");

class PApplication extends CommonComponent
{

    public static $app;

    private $_requestObject;

    private $_helpers = array();
    private $_db;
    
    public function __construct()
    {}

    /**
     * This method is used to return Request Object
     *
     * @return PRequest
     */
    public function getRequest()
    {
        if (! isset($this->_requestObject)) {
            $this->_requestObject = new PRequest();
        }
        
        return $this->_requestObject;
    }

    /**
     * This method is used to import model
     *
     * @param string $modelName            
     * @throws Exception
     */
    public function importModel($modelName)
    {
        $modelFile = MODEL_PATH . "{$modelName}.php";
        if (file_exists($modelFile)) {
            require_once ($modelFile);
        } else {
            throw new Exception("Model {$modelName} doesn't exist");
        }
    }

    /**
     * This method is used to get a helper
     *
     * @param string $helperName            
     */
    public function helper($helperName)
    {
        if (! isset($this->_helpers[$helperName])) {
            $helperFile = APP_PATH . "helpers/{$helperName}.php";
            if (file_exists($helperFile)) {
                require_once ($helperFile);
                
                $this->_helpers[$helperName] = new $helperName();
            } else {
                throw new Exception("Helper {$helperName} doesn't exist");
            }
        }
        
        return $this->_helpers[$helperName];
    }
    
    /**
     * This method is used to prepare directory
     * @param unknown $dir
     */
    public function prepareDir($dir) {
        $dir = TMP_DIR . "/" . str_replace("'", "", $dir);
        
        @mkdir($dir);
        @chmod($dir, 0777);
    }
    
    public function getDb() {
        if (!isset($this->_db)) {
            $config = require("protected/config/app.php");
            
            $this->_db = new MySQLConnector($config['db']);
        }
        
        return $this->_db;
    }
}

// Initialize application
PApplication::$app = new PApplication();