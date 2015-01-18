<?php
require_once ("components/CommonComponent.php");

class Round extends CommonComponent
{

    public $inputs = array();

    private $_client;

    /**
     * This protected field is used to list all available magic methods
     *
     * @var array $_f
     */
    protected $_f = array(
        'baseUrl' => '',
        'postVars' => array(),
        'method' => 'GET',
        'uri' => ''
    );

    /**
     * This method is used to construct Round class
     *
     * @param unknown $options            
     */
    public function __construct($options = array())
    {
        $_defaultOptions = array();
        $options = array_merge($_defaultOptions, $options);
        
        $this->baseUrl = $options['baseUrl'];
        
        $this->client = new GuzzleHttp\Client([
            'base_url' => $this->baseUrl
        ]);
    }

    public function getClient()
    {
        return $this->_client;
    }

    public function setClient($client)
    {
        $this->_client = $client;
    }

    public function parseInputs()
    {}

    /**
     * This method is used to send request to server
     */
    public function request($options)
    {
        $_defaultOptions = array(
            'url' => '',
            'type' => 'GET',
            'data' => array(),
            'success' => function ()
            {}
        );
        $options = array_merge($_defaultOptions, $options);
        
        $method = strtoupper($options['type']);
        
        // If method is POST
        if ($method == 'POST') {
            $response = $this->client->post($options['url'], array(
                'body' => $options['data']
            ));
        } else {
            $response = $this->client->get($options['url']);
        }
        
        $options['success']($response, $this);
    }
}