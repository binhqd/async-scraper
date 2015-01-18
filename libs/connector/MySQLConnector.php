<?php
/**
 * 
 * @author BinhQD
 *
 */
class MySQLConnector {
	private static $conn;
	private $host;
	private $port;
	private $password;
	private $user;
	private $dbName;
	private $db;
	
	/**
	 * This method is used to construct new object for this class
	 * @param array $config
	 */
	public function __construct($config = array()) {
		if (!empty($config)) {
			$this->host = !empty($config['host']) ? $config['host'] : 'localhost';
			$this->port = !empty($config['port']) ? $config['port'] : '3306';
			$this->user = !empty($config['username']) ? $config['username'] : '';
			$this->password = !empty($config['password']) ? $config['password'] : '';
			$this->dbName = !empty($config['db']) ? $config['db'] : '';
			$this->table = !empty($config['table_name']) ? $config['table_name'] : '';
			
			if (!empty($this->host) && !empty($this->port) && !empty($this->dbName) && !empty($this->user) && !empty($this->password)) {
				$this->connect($this->host, $this->port, $this->dbName, $this->user, $this->password);
			}
		}
	}
	
	/**
	 * Magic method that used to return current connection object
	 * @return PDO
	 */
	public function getConnection() {
		return self::$conn;
	}
	
	/**
	 * This method is used to connect to a database engine using PDO
	 * @param string $host
	 * @param int $port
	 * @param string $dbName
	 * @param string $username
	 * @param string $password
	 */
	public function connect($host, $port, $dbName, $username, $password) {
		self::$conn = new PDO("mysql:host={$host};dbname={$dbName};port:{$port}", $username, $password);
	}
	
	/**
	 * This method is used to execute a statement with given params
	 * @param string $sql
	 * @param array $params
	 */
	public function exec($sql, $params) {
		self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = self::$conn->prepare($sql);
		if (!$stmt->execute($params)) {
			print_r(self::$conn->errorInfo());
		}
	}
	
	/**
	 * Method for query from database
	 * @param string $sql
	 * @param array $params
	 * @return array $records
	 */
	public function query($sql, $params) {
		self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = self::$conn->prepare($sql);
		$stmt->execute($params);
		
		$records = $stmt->fetchAll();
		return $records;
	}
}