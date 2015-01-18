<?php
/**
 * IDHelper - Helper chứa các phương thức phục vụ việc sử dụng UUID
 * 
 * @ingroup utils
 * @author huytbt
 * @version 1.0
 */
class IDHelper
{
	/**
	 * IDHelper::uuidToHex() - Phương thức dùng để chuyển đổi chuỗi uuid sang chuỗi hexa
	 *
	 * @param mixed $uuid
	 * @return
	 */
	public static function uuidToHex($uuid)
	{
		if (!is_array($uuid)) {
			$uuid = str_replace("-", "", $uuid);
			return "0x{$uuid}"; 
		} else {
			return array_map(
				array("self", "uuidToHex"),
				$uuid
			);
		}
	}
	
	/**
	 * IDHelper::binToHex() - Phương thức dùng để chuyển đổi chuỗi uuid sang chuỗi hexa
	 *
	 * @param mixed $uuid
	 * @return
	 */
	/*public static function binToHex($value)
	{
		$uuid = str_replace("-", "", $uuid);
		return "0x{$uuid}";
	}*/
	
	/**
	 * IDHelper::uuidToBinary() - Phương thức dùng để chuyển đổi chuỗi uuid sang binary
	 * 
	 * @param mixed $uuid
	 * @return
	 */
	public static function uuidToBinary($uuid)
	{
		if (!is_array($uuid)) {
			$uuid = str_replace("-", "", $uuid);
			$id = @pack('H*', $uuid);
			return $id;
		} else {
			return array_map(
				array("self", "uuidToBinary"),
				$uuid
			);
		}
	}
	
	/**
	 * IDHelper::uuidFromBinary() - Phương thức dùng để chuyển đổi uuid từ binary sang string
	 * 
	 * @param mixed $value
	 * @return
	 */
	public static function uuidFromBinary($value, $noDash = false)
	{
		if (!is_array($value)) {
			if ($value !== null) {
				$value= unpack('H*', $value);
				$uuid = array_shift($value);
				
				$pattern = "/([0-9a-fA-F]{8})([0-9a-fA-F]{4})([0-9a-fA-F]{4})([0-9a-fA-F]{4})([0-9a-fA-F]{12})/";
				
				if (!$noDash)
					$uuid = preg_replace($pattern, "$1-$2-$3-$4-$5", $uuid);
				
				/**
				* HOT FIX: Binary ID sang String ID thua 2 ky tu 'cb' o cuoi
				*
				* @author huytbt
				* @date 2012-03-06 2:59:00 PM
				*/
				if (strlen($uuid) > 36) $uuid = substr($uuid, 0, 36);
				
				return $uuid;
			}
		} else {
			return array_map(
				array("self", "uuidFromBinary"),
				$value
			);
		}
	}
	
	/**
	 * IDHelper::binaryToStringField() - Phương thức dùng để chuyển giá trị các trường fields từ binary sang string
	 *
	 * @param array $model
	 * @param array $arrFields
	 * @return array 
	 */
	public static function binaryToStringFields($model, $arrFields)
	{
		$arr = $model;
		foreach ($arr as $item) {
			foreach ($arrFields as $field) {
				$item->$field = self::uuidFromBinary($item->$field);
			}
		}
		return $arr;
	}
	
	/**
	 * IDHelper::uuid() - Phương thức dùng để tạo ra mã uuid
	 * 
	 * @return string Mã uuid
	 */
	public static function uuid($hasDash = true, $removeBinChars = true) {
		$node = IDHelper::env('SERVER_ADDR');
		$pid = null;
		
		if (strpos($node, ':') !== false) {
			if (substr_count($node, '::')) {
				$node = str_replace('::', str_repeat(':0000', 8 - substr_count($node, ':')) . ':', $node);
			}
			$node = explode(':', $node) ;
			$ipv6 = '' ;
			
			foreach ($node as $id) {
				$ipv6 .= str_pad(base_convert($id, 16, 2), 16, 0, STR_PAD_LEFT);
			}
			$node =  base_convert($ipv6, 2, 10);
			
			if (strlen($node) < 38) {
				$node = null;
			} else {
				$node = crc32($node);
			}
		} elseif (empty($node)) {
			$host = IDHelper::env('HOSTNAME');
			
			if (empty($host)) {
				$host = IDHelper::env('HOST');
			}
			
			if (!empty($host)) {
				$ip = gethostbyname($host);
				
				if ($ip === $host) {
					$node = crc32($host);
				} else {
					$node = ip2long($ip);
				}
			}
		} elseif ($node !== '127.0.0.1') {
			$node = ip2long($node);
		} else {
			$node = null;
		}
		
		if (empty($node)) {
			$node = crc32('DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi'); //TODO: config from config/jlu_mainconfig.php
		}
		
		if (function_exists('zend_thread_id')) {
			$pid = zend_thread_id();
		} else {
			$pid = getmypid();
		}
		
		if (!$pid || $pid > 65535) {
			$pid = mt_rand(0, 0xfff) | 0x4000;
		}
		
		list($timeMid, $timeLow) = explode(' ', microtime());
		
		if ($hasDash)
			$uuid = sprintf("%08x-%04x-%04x-%02x%02x-%04x%08x", (int)$timeLow, (int)substr($timeMid, 2) & 0xffff,
					mt_rand(0, 0xfff) | 0x4000, mt_rand(0, 0x3f) | 0x80, mt_rand(0, 0xff), $pid, $node);
		else
			$uuid = sprintf("%08x%04x%04x%02x%02x%04x%08x", (int)$timeLow, (int)substr($timeMid, 2) & 0xffff,
					mt_rand(0, 0xfff) | 0x4000, mt_rand(0, 0x3f) | 0x80, mt_rand(0, 0xff), $pid, $node);
		
		if ($removeBinChars) {
			$test = self::uuidToBinary($uuid);
			
			$pattern = "/(\\0|\\r|\\n|\\\\|\\(|\\)|<|>|\\'|\\\"|=)/";
			// Perform remove null charactor
			$uuid = self::uuidToBinary($uuid);
			$uuid = preg_replace($pattern, "x", $uuid);
			
			$uuid = self::uuidFromBinary($uuid, !$hasDash);
		}
		return $uuid;
	}
	
	/**
	 * IDHelper::env() - Phương thức dùng để lấy giá trị các biến môi trường
	 * 
	 * @param string $key
	 * @return giá trị các biến môi trường
	 */
	static function env($key) {
		if ($key == 'HTTPS') {
			if (isset($_SERVER['HTTPS'])) {
				return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
			}
			return (strpos(IDHelper::env('SCRIPT_URI'), 'https://') === 0);
		}

		if ($key == 'SCRIPT_NAME') {
			if (IDHelper::env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
				$key = 'SCRIPT_URL';
			}
		}

		$val = null;
		if (isset($_SERVER[$key])) {
			$val = $_SERVER[$key];
		} elseif (isset($_ENV[$key])) {
			$val = $_ENV[$key];
		} elseif (getenv($key) !== false) {
			$val = getenv($key);
		}

		if ($key === 'REMOTE_ADDR' && $val === env('SERVER_ADDR')) {
			$addr = IDHelper::env('HTTP_PC_REMOTE_ADDR');
			if ($addr !== null) {
				$val = $addr;
			}
		}

		if ($val !== null) {
			return $val;
		}

		switch ($key) {
			case 'SCRIPT_FILENAME':
				if (defined('SERVER_IIS') && SERVER_IIS === true) {
					return str_replace('\\\\', '\\', env('PATH_TRANSLATED'));
				}
				break;
			case 'DOCUMENT_ROOT':
				$name = env('SCRIPT_NAME');
				$filename = env('SCRIPT_FILENAME');
				$offset = 0;
				if (!strpos($name, '.php')) {
					$offset = 4;
				}
				return substr($filename, 0, strlen($filename) - (strlen($name) + $offset));
				break;
			case 'PHP_SELF':
				return str_replace(IDHelper::env('DOCUMENT_ROOT'), '', IDHelper::env('SCRIPT_FILENAME'));
				break;
			case 'CGI_MODE':
				return (PHP_SAPI === 'cgi');
				break;
			case 'HTTP_BASE':
				$host = IDHelper::env('HTTP_HOST');
				$parts = explode('.', $host);
				$count = count($parts);

				if ($count === 1) {
					return '.' . $host;
				} elseif ($count === 2) {
					return '.' . $host;
				} elseif ($count === 3) {
					$gTLD = array('aero', 'asia', 'biz', 'cat', 'com', 'coop', 'edu', 'gov', 'info', 'int', 'jobs', 'mil', 'mobi', 'museum', 'name', 'net', 'org', 'pro', 'tel', 'travel', 'xxx');
					if (in_array($parts[1], $gTLD)) {
						return '.' . $host;
					}
				}
				array_shift($parts);
				return '.' . implode('.', $parts);
				break;
		}
		return null;
	}
}
