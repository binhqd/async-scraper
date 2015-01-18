<?php
/**
 * This is a simple component that at 3 basic magic functions: __get, __set, __isset
 * @author BinhQD
 *
 */
class CommonComponent {
	protected $_f = array();
	protected $_serialized_f = array();
	/**
	 * implement __get method to enable some magic functions
	 * @param string $name
	 * @throws Exception
	 * @return mixed|multitype:
	 */
	public function __get($name)
	{
		$getter='get'.$name;
		if(method_exists($this,$getter))
			return $this->$getter();
		elseif (isset($this->_serialized_f[$name])) {
			return unserialize($this->_serialized_f[$name]);
		} elseif (isset($this->_f[$name])) {
			return $this->_f[$name];
		}
		throw new Exception("Property \"".get_class($this).".".$name."\" is not defined.");
	}
	
	/**
	 * implement __set method to enable some magic functions
	 * @param string $name
	 * @param unknow $value
	 * @throws Exception
	 * @return boolean
	 */
	public function __set($name,$value)
	{
		$setter='set'.$name;
		if(method_exists($this,$setter))
			return $this->$setter($value);
		elseif (isset($this->_serialized_f[$name])) {
			$this->_serialized_f[$name] = serialize($value);
			return true;
		} elseif (isset($this->_f[$name])) {
			$this->_f[$name] = $value;
			return true;
		}
		
		if(method_exists($this,'get'.$name))
			throw new Exception("Property \"".get_class($this).".".$name."\" is read only.");
		else
			throw new Exception("Property \"".get_class($this).".".$name."\" is not defined.");
	}
	
	/**
	 * implement __isset method to enable some magic functions
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		$getter='get'.$name;
		if(method_exists($this,$getter))
			return $this->$getter()!==null;
		
		
		return false;
	}
	
	/**
	 * implement __unset method to enable some magic functions
	 * @param string $name
	 * @throws Exception
	 */
	public function __unset($name)
	{
		$setter='set'.$name;
		if(method_exists($this,$setter))
			$this->$setter(null);
		elseif(method_exists($this,'get'.$name))
			throw new Exception("Property \"".get_class($this).".".$name."\" is read only.");
	}
}