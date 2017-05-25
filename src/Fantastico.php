<?php
/**
 * Fantastico
 * Fantastico Class for interfacing with there API.  Their API is pretty simple, so this
 * might not be needed, it just simplifies things a little more.
 *
 * @package MyAdmin
 * @author detain
 * @copyright 2017
 * @version $Id$
 * @access public
 */

namespace Detain\Fantastico;

class Fantastico
{

	/**
	 * All fantastico license types
	 */
	const ALL_TYPES = 0;

	/**
	 * Normal/Server license types
	 */
	const NORMAL_TYPES = 1;

	/**
	 * VPS license types
	 */
	const VPS_TYPES = 2;

	/**
	 * the WSDL API file
	 */
	public $wsdl = 'https://netenberg.com/api/netenberg.wsdl';

	/**
	 * the username to use for API access
	 */
	private $api_username;

	/**
	 * the password to use for api access
	 */
	private $api_password;

	/**
	 * this will hold the soap client, which hopefully we can reuse for multiple queries if need be
	 */
	private $soapClient;

	/**
	 * this will hold responses from the API for future caching use
	 */
	private $cache;

	/**
	 * this holds an array of possible license types you can use and there descriptions
	 * note the ALL_TYPES is only for listing, you cant buy one thats all_types
	 */
	public $types = array(
		self::ALL_TYPES => 'All IPs',
		self::NORMAL_TYPES => 'Normal Licenses',
		self::VPS_TYPES => 'VPS Licenses');

	/**
	 * Fantastico::__construct()
	 * Starts an instance of the fantastico license API.
	 * @param string $username username to connect to fantastico api with
	 * @param string $password password to connect to fantastico api with
	 * @return \Fantastico
	 */
	public function __construct($username, $password) {
		$this->cache = array();
		$this->soapClient = null;
		$this->api_username = $username;
		$this->api_password = $password;
	}

	/**
	 * Fantastico::connect()
	 * function called automatically to ensure that we're connected to the API
	 *
	 * @return void
	 */
	public function connect() {
		if (is_null($this->soapClient)) {
			ini_set('soap.wsdl_cache_enabled', '0');
			ini_set('max_execution_time', 1000);
			ini_set('default_socket_timeout', 1000);
			$this->soapClient = new \SoapClient($this->wsdl, array(

				'soap_version' => SOAP_1_1,
				'connection_timeout' => 1000,
				'trace' => 1,
				'exception' => 1));
			//require_once (INCLUDE_ROOT . '/../vendor/detain/nusoap/lib/nusoap.php');
			//$this->soapClient = new \nusoap_client($this->wsdl);
		}
	}

	/**
	 * Fantastico::get_ip_types()
	 * returns an array of the possible license types in the format of
	 *   ID => Description
	 * where ID is the Type ID you need to pass to various functions asking for a license type
	 *
	 * @return array returns an array of possible license types and descriptions of them
	 */
	public function get_ip_types() {
		return $this->types;
	}

	/**
	 * Fantastico::is_type()
	 * a check to make sure the passed type is valid
	 *
	 * @param mixed the license type your trying to validate
	 * @return bool whether or not its a valid fantastico license type
	 */
	public function is_type($type) {
		return array_key_exists($type, $this->types);
	}

	/**
	 * Fantastico::getHash()
	 * returns the login hash
	 *
	 * @return string the login hash
	 */
	private function getHash() {
		return md5($this->api_username . $this->api_password);
	}

	/**
	 * Fantastico::getIpList()
	 * returns a list of all license types.
	 *
	 * Sample Return Output
	 * Array
	 * (
	 *     [0] => 130.253.175.32
	 *     [1] => 140.99.16.206
	 *     [2] => 150.101.195.140
	 * )
	 *
	 * @param mixed $type one of the possible fantastico license types, defaults to {@link self::ALL_TYPES}
	 * @return false|array returns false on error or an array of license details
	 */
	public function getIpList($type = self::ALL_TYPES) {
		if (isset($this->cache['getIpList_' . $type])) {
			return $this->cache['getIpList_' . $type];
		}
		if (!$this->is_type($type)) {
			return false;
		}
		$this->connect();
		$this->cache['getIpList_' . $type] = $this->soapClient->getIpList($this->getHash(), $type);
		return $this->cache['getIpList_' . $type];
	}

	/**
	 * Fantastico::getIpListDetailed()
	 * returns a list of all license types and details about each one
	 *
	 * Sample Return Output
	 *
	 *
	 * 	Array
	 * 	(
	 * 	    [0] => {
	 * 	    	Array
	 * 			(
	 * 			    [ipAddress] => 130.253.175.32
	 * 			    [addedOn] => 2010-03-01 00:00:00
	 * 			    [isVPS] => ( Yes || No )
	 * 			    [status] => ( Active || Inactive )
	 * 			)
	 * 		}
	 * 	    [1] => {
	 * 	    	Array
	 * 			(
	 * 			    [ipAddress] => 131.253.175.32
	 * 			    [addedOn] => 2011-03-01 00:00:00
	 * 			    [isVPS] => ( Yes || No )
	 * 			    [status] => ( Active || Inactive )
	 * 			)
	 * 	    }
	 * 	    [2] => {
	 * 	    	Array
	 * 			(
	 * 			    [ipAddress] => 132.253.175.32
	 * 			    [addedOn] => 2012-03-01 00:00:00
	 * 			    [isVPS] => ( Yes || No )
	 * 			    [status] => ( Active || Inactive )
	 * 			)
	 * 	    }
	 * 	)
	 *
	 * @param mixed $type one of the possible fantastico license types, defaults to {@link self::ALL_TYPES}
	 * @return false|array returns false on error or an array of license details
	 */
	public function getIpListDetailed($type = self::ALL_TYPES) {
		if (!$this->is_type($type)) {
			return false;
		}
		if (isset($this->cache['getIpListDetailed_' . $type])) {
			return $this->cache['getIpListDetailed_' . $type];
		}
		$this->connect();
		//try {
		$response = json_decode($this->soapClient->__soapCall('getIpListDetailed', array($this->getHash(), $type)), true);
		//echo '<pre>';echo print_r($response, true);echo '</pre>';
		//$this->cache['getIpListDetailed_' . $type] = $this->cache['getIpListDetailed_' . $type]->Licenses;
		$this->cache['getIpListDetailed_' . $type] = array();
		$this->cache['getIpList_' . $type] = array();
		foreach ($response['Licenses'] as $idx => $data) {
			$tdata = array(
				'ipAddress' => $data[0],
				'addedOn' => $data[1],
				'isVPS' => $data[2],
				'status' => $data[3]);
			$this->cache['getIpListDetailed_' . $type][] = $tdata;
			$this->cache['getIpList_' . $type][] = $tdata['ipAddress'];
			$this->cache['getIpDetails_' . $tdata['ipAddress']] = $tdata;
		}
		//} catch (SoapFault $fault) {
		//var_dump($fault);
		//var_dump($this->soapClient->__getLastRequest());
		//var_dump($this->soapClient->__getLastResponse());
		//}

		//foreach ($this->cache['getIpListDetailed_' . $type] as $idx => $data)
		//{
		//$this->cache['getIpList_' . $type][] = $data['ipAddress'];
		//$this->cache['getIpDetails_' . $data['ipAddress']] = $data;
		//}
		//echo '<pre>';print_r($this->cache);echo '</pre>';
		return $this->cache['getIpListDetailed_' . $type];
	}

	/**
	 * Fantastico::valid_ip()
	 * validates the IP address
	 *
	 * @param string $ip IP Address to validate
	 * @return bool whether or not the ip was validated
	 */
	public function valid_ip($ip) {
		return ip2long($ip) !== false;
	}

	/**
	 * Fantastico::getIpDetails()
	 * get details about a license
	 *
	 * Output Success
	 * Array
	 * (
	 *     [ipAddress] => 130.253.175.32
	 *     [addedOn] => 2010-03-01 00:00:00
	 *     [isVPS] => ( Yes || No )
	 *     [status] => ( Active || Inactive )
	 * )
	 *
	 *
	 * Output Error
	 *
	 * Array
	 * (
	 *     [faultcode] => 1801
	 *     [fault ]=> "The IP Address that you have specified does not exist."
	 * )
	 *
	 * @param string $ip ip address to get details for
	 * @return mixed returns false on invalid IP, or an array of the details.
	 */
	public function getIpDetails($ip) {
		if (!$this->valid_ip($ip)) {
			return array('faultcode' => 1, 'fault ' => 'Invalid IP Address ' . $ip);
		}
		if (isset($this->cache['getIpDetails_' . $ip])) {
			return $this->cache['getIpDetails_' . $ip];
		}
		$this->connect();
		$this->cache['getIpDetails_' . $ip] = $this->soapClient->getIpDetails($this->getHash(), $ip);
		return $this->cache['getIpDetails_' . $ip];
	}

	/**
	 * Fantastico::editIp()
	 * changes the IP address of a license
	 *
	 *
	 * Output Success
	 *
	 * Array
	 * (
	 *     ["ip"]=>"130.253.175.32"
	 *     ["new_ip"]=>"130.253.175.33"
	 *
	 * )
	 *
	 *
	 * Output Error
	 *
	 * Array
	 * (
	 *     [faultcode] => 1704
	 *     [fault ]=> "The new IP Address that you have specified is not a valid cPanel IP Address."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1705
	 *     [fault ]=> "The new IP Address that you have specified is not a valid cPanel IP Address."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1703
	 *     [fault ]=> "The IP Address that you have specified is not a valid VPS IP Address."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1804
	 *     [fault ]=> "The IP Address that you have specified already exists."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1801
	 *     [fault ]=> "The IP Address that you have specified does not exist."
	 * )
	 *
	 *
	 *
	 * Array
	 * (
	 *     [faultcode] => 1401
	 *     [fault ]=> "You are trying to access the API from a server whose IP Address is not authorized."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1302
	 *     [fault ]=> "You have specified an invalid hash."
	 * )
	 *
	 *
	 * @param mixed $ip old ip address currently licensed
	 * @param mixed $newip new ip address to change i tot
	 * @return array returns an array of ip and newip or a fault and faultcode
	 */
	public function editIp($ip, $newip) {
		if (!$this->valid_ip($ip)) {
			return array('faultcode' => 1, 'fault ' => 'Invalid IP Address ' . $ip);
		}
		if (!$this->valid_ip($newip)) {
			return array('faultcode' => 2, 'fault ' => 'Invalid IP Address ' . $newip);
		}
		$this->connect();
		$response = $this->soapClient->editIp($this->getHash(), $ip, $newip);
		$this->cache = array();
		return $response;
	}

	/**
	 * Fantastico::addIp()
	 * adds a new license into the system
	 *
	 * Output Success
	 *
	 * Array
	 * (
	 *     ["ip"]=>"130.253.175.32"
	 *     ["id"]=>"112461"	 *
	 * )
	 *
	 *
	 * Output Error
	 *
	 * Array
	 * (
	 *     [faultcode] => 1704
	 *     [fault ]=> "The new IP Address that you have specified is not a valid cPanel IP Address."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1705
	 *     [fault ]=> "The new IP Address that you have specified is not a valid cPanel IP Address."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1703
	 *     [fault ]=> "The IP Address that you have specified is not a valid VPS IP Address."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1804
	 *     [fault ]=> "The IP Address that you have specified already exists."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1603
	 *     [fault ]=> "You are not allowed to add any more IP Addresses because you have reached your IP Address quota."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1401
	 *     [fault ]=> "You are trying to access the API from a server whose IP Address is not authorized."
	 * )
	 *
	 * Array
	 * (
	 *     [faultcode] => 1302
	 *     [fault ]=> "You have specified an invalid hash."
	 * )
	 *
	 *
	 * @param string $ip ip address
	 * @param integer $type license type
	 * @return array response array containing a faultcode and fault, or ip and id on success
	 */
	public function addIp($ip, $type) {
		if (!$this->valid_ip($ip)) {
			return array('faultcode' => 1, 'fault ' => 'Invalid IP Address ' . $ip);
		}
		$this->connect();
		$response = $this->soapClient->addIp($this->getHash(), $ip, $type);
		$this->cache = array();
		return $response;
	}

	/**
	 * deactivateIp()
	 * Deactivates a Fantastico IP License
	 * Output Success
	 * Array
	 * (
	 *     [ipAddress] => 130.253.175.32
	 *     [addedOn] => 2010-03-01 00:00:00
	 *     [isVPS] => ( Yes || No )
	 *     [status] => ( Active || Inactive )
	 * )
	 *
	 * Output Error
	 * Array
	 * (
	 *     [faultcode] => 1801
	 *     [fault ]=> "The IP Address that you have specified does not exist."
	 * )
	 *
	 * @param mixed $ip
	 * @return void
	 */
	public function deactivateIp($ip) {
		if (!$this->valid_ip($ip)) {
			return array('faultcode' => 1, 'fault ' => 'Invalid IP Address ' . $ip);
		}
		$this->connect();
		$response = $this->soapClient->deactivateIp($this->getHash(), $ip);
		$this->cache = array();
		return $response;

	}

	/**
	 * reactivateIp()
	 * Deactivates a Fantastico IP License
	 * Output Success
	 * Array
	 * (
	 *     [ipAddress] => 130.253.175.32
	 *     [addedOn] => 2010-03-01 00:00:00
	 *     [isVPS] => ( Yes || No )
	 *     [status] => ( Active || Inactive )
	 * )
	 *
	 * Output Error
	 * Array
	 * (
	 *     [faultcode] => 1801
	 *     [fault ]=> "The IP Address that you have specified does not exist."
	 * )
	 *
	 * @param mixed $ip
	 * @return void
	 */
	public function reactivateIp($ip) {
		if (!$this->valid_ip($ip)) {
			return array('faultcode' => 1, 'fault ' => 'Invalid IP Address ' . $ip);
		}
		$this->connect();
		$response = $this->soapClient->reactivateIp($this->getHash(), $ip);
		$this->cache = array();
		return $response;

	}

	/**
	 * deleteIp()
	 * Deletes a Fantastico IP License
	 *
	 * Output Success
	 *
	 * Array
	 * (
	 *     [ip] => 130.253.175.32
	 *     [deleted] => "Yes"
	 * )
	 *
	 * Output Error
	 *
	 * Array
	 * (
	 *     [faultcode] => 1801
	 *     [fault ]=> "The IP Address that you have specified does not exist."
	 * )
	 *
	 * @param mixed $ip
	 * @return void
	 */
	public function deleteIp($ip) {
		if (!$this->valid_ip($ip)) {
			return array('faultcode' => 1, 'fault ' => 'Invalid IP Address ' . $ip);
		}
		$this->connect();
		$response = $this->soapClient->deleteIp($this->getHash(), $ip);
		$this->cache = array();
		return $response;
	}

}
