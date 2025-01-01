<?php
/**
 * Fantastico
 * Fantastico Class for interfacing with there API.  Their API is pretty simple, so this
 * might not be needed, it just simplifies things a little more.
 *
 * @package MyAdmin
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2025
 * @version $Id$
 * @access public
 */

namespace Detain\Fantastico;

require_once __DIR__.'/../../../workerman/statistics/Applications/Statistics/Clients/StatisticClient.php';

/**
 * Class Fantastico
 *
 * @package Detain\Fantastico
 */
class Fantastico
{
    /**
     * All fantastico license types
     */
    public const ALL_TYPES = 0;

    /**
     * Normal/Server license types
     */
    public const NORMAL_TYPES = 1;

    /**
     * VPS license types
     */
    public const VPS_TYPES = 2;

    /**
     * the WSDL API file
     */
    public $wsdl = 'https://netenberg.com/api/netenberg.wsdl';

    public $connected = false;

    /**
     * the username to use for API access
     */
    private $apiUsername;

    /**
     * the password to use for api access
     */
    private $apiPassword;

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
    public $types = [
        self::ALL_TYPES => 'All IPs',
        self::NORMAL_TYPES => 'Normal Licenses',
        self::VPS_TYPES => 'VPS Licenses'
    ];

    /**
     * Starts an instance of the fantastico license API.
     *
     * @param string $username username to connect to fantastico api with
     * @param string $password password to connect to fantastico api with
     */
    public function __construct($username, $password)
    {
        $this->cache = [];
        $this->soapClient = null;
        $this->apiUsername = $username;
        $this->apiPassword = $password;
    }

    /**
     * Fantastico::connect()
     * function called automatically to ensure that we're connected to the API
     *
     * @return void
     */
    public function connect()
    {
        $nusoap = false;
        if (null === $this->soapClient) {
            ini_set('soap.wsdl_cache_enabled', '0');
            ini_set('max_execution_time', 1000);
            ini_set('default_socket_timeout', 1000);
            try {
                $this->soapClient = new \SoapClient($this->wsdl, ['soap_version' => SOAP_1_1, 'connection_timeout' => 1000, 'trace' => 1, 'exception' => 1]);
            } catch (\Exception $e) {
                $nusoap = true;
            }
        }
        if (true === $nusoap) {
            require_once INCLUDE_ROOT.'/../vendor/detain/nusoap/lib/nusoap.php';
            $this->soapClient = new \nusoap_client($this->wsdl);
            $this->connected = true;
        }
    }

    /**
     * Fantastico::getIpTypes()
     * returns an array of the possible license types in the format of
     *   ID => Description
     * where ID is the Type ID you need to pass to various functions asking for a license type
     *
     * @return array returns an array of possible license types and descriptions of them
     */
    public function getIpTypes()
    {
        return $this->types;
    }

    /**
     * Fantastico::isType()
     * a check to make sure the passed type is valid
     *
     * @param mixed the license type your trying to validate
     * @param integer $type
     * @return bool whether or not its a valid fantastico license type
     */
    public function isType($type)
    {
        return array_key_exists($type, $this->types);
    }

    /**
     * Fantastico::getHash()
     * returns the login hash
     *
     * @return string the login hash
     */
    private function getHash()
    {
        return md5($this->apiUsername.$this->apiPassword);
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
     * @param integer $type one of the possible fantastico license types, defaults to {@link self::ALL_TYPES}
     * @return FALSE|array returns FALSE on error or an array of license details
     */
    public function getIpList($type = self::ALL_TYPES)
    {
        if (isset($this->cache['getIpList_'.$type])) {
            return $this->cache['getIpList_'.$type];
        }
        if (!$this->isType($type)) {
            return false;
        }
        $this->connect();
        \StatisticClient::tick('Fantastico', 'getIpList');
        $this->cache['getIpList_'.$type] = json_decode($this->soapClient->getIpList($this->getHash(), $type), true);
        if ($this->cache['getIpList_'.$type] === false) {
            \StatisticClient::report('Fantastico', 'getIpList', false, 1, 'Soap Client Error', STATISTICS_SERVER);
        } else {
            \StatisticClient::report('Fantastico', 'getIpList', true, 0, '', STATISTICS_SERVER);
        }
        myadmin_log('fantastico', 'debug', json_encode($this->cache['getIpList_'.$type]), __LINE__, __FILE__);
        return $this->cache['getIpList_'.$type];
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
     * @param integer $type one of the possible fantastico license types, defaults to {@link self::ALL_TYPES}
     * @return FALSE|array returns FALSE on error or an array of license details
     */
    public function getIpListDetailed($type = self::ALL_TYPES)
    {
        if (!$this->isType($type)) {
            return false;
        }
        if (isset($this->cache['getIpListDetailed_'.$type])) {
            return $this->cache['getIpListDetailed_'.$type];
        }
        $this->connect();
        //try {
        \StatisticClient::tick('Fantastico', 'getIpListDetailed');
        $response = json_decode($this->soapClient->__soapCall('getIpListDetailed', [$this->getHash(), $type]), true);
        if ($response === false) {
            \StatisticClient::report('Fantastico', 'getIpListDetailed', false, 1, 'Soap Client Error', STATISTICS_SERVER);
        } else {
            \StatisticClient::report('Fantastico', 'getIpListDetailed', true, 0, '', STATISTICS_SERVER);
        }
        myadmin_log('fantastico', 'debug', json_encode($response), __LINE__, __FILE__);
        //echo '<pre>';echo print_r($response, TRUE);echo '</pre>';
        //$this->cache['getIpListDetailed_'.$type] = $this->cache['getIpListDetailed_'.$type]->Licenses;
        $this->cache['getIpListDetailed_'.$type] = [];
        $this->cache['getIpList_'.$type] = [];
        $responseValues = array_values($response['Licenses']);
        foreach ($responseValues as $data) {
            $tdata = [
                'ipAddress' => $data[0],
                'addedOn' => $data[1],
                'isVPS' => $data[2],
                'status' => $data[3]
            ];
            $this->cache['getIpListDetailed_'.$type][] = $tdata;
            $this->cache['getIpList_'.$type][] = $tdata['ipAddress'];
            $this->cache['getIpDetails_'.$tdata['ipAddress']] = $tdata;
        }
        //} catch (SoapFault $fault) {
        //var_dump($fault);
        //var_dump($this->soapClient->__getLastRequest());
        //var_dump($this->soapClient->__getLastResponse());
        //}

        //foreach ($this->cache['getIpListDetailed_'.$type] as $idx => $data)
        //{
        //$this->cache['getIpList_'.$type][] = $data['ipAddress'];
        //$this->cache['getIpDetails_'.$data['ipAddress']] = $data;
        //}
        //echo '<pre>';print_r($this->cache);echo '</pre>';
        return $this->cache['getIpListDetailed_'.$type];
    }

    /**
     * Fantastico::validIp()
     * validates the IP address
     *
     * @param string $ipAddress IP Address to validate
     * @return bool whether or not the ip was validated
     */
    public function validIp($ipAddress)
    {
        return ip2long($ipAddress) !== false;
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
     * @param string $ipAddress ip address to get details for
     * @return mixed returns FALSE on invalid IP, or an array of the details.
     */
    public function getIpDetails($ipAddress)
    {
        if (!$this->validIp($ipAddress)) {
            return ['faultcode' => 1, 'fault ' => 'Invalid IP Address '.$ipAddress];
        }
        if (isset($this->cache['getIpDetails_'.$ipAddress])) {
            return $this->cache['getIpDetails_'.$ipAddress];
        }
        $this->connect();
        \StatisticClient::tick('Fantastico', 'getIpDetails');
        $this->cache['getIpDetails_'.$ipAddress] = json_decode($this->soapClient->getIpDetails($this->getHash(), $ipAddress), true);
        if ($this->cache['getIpDetails_'.$ipAddress] === false) {
            \StatisticClient::report('Fantastico', 'getIpDetails', false, 1, 'Soap Client Error', STATISTICS_SERVER);
        } else {
            \StatisticClient::report('Fantastico', 'getIpDetails', true, 0, '', STATISTICS_SERVER);
        }
        myadmin_log('fantastico', 'debug', json_encode($this->cache['getIpDetails_'.$ipAddress]), __LINE__, __FILE__);
        return $this->cache['getIpDetails_'.$ipAddress];
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
     * @param mixed $ipAddress old ip address currently licensed
     * @param mixed $newip new ip address to change i tot
     * @return array returns an array of ip and newip or a fault and faultcode
     */
    public function editIp($ipAddress, $newip)
    {
        if (!$this->validIp($ipAddress)) {
            $response = ['faultcode' => 1, 'fault' => 'Invalid IP Address '.$ipAddress];
        } elseif (!$this->validIp($newip)) {
            $response = ['faultcode' => 2, 'fault' => 'Invalid IP Address '.$newip];
        } else {
            $this->connect();
            \StatisticClient::tick('Fantastico', 'editIp');
            $response = json_decode($this->soapClient->editIp($this->getHash(), $ipAddress, $newip), true);
            if ($response === false) {
                \StatisticClient::report('Fantastico', 'editIp', false, 1, 'Soap Client Error', STATISTICS_SERVER);
            } else {
                \StatisticClient::report('Fantastico', 'editIp', true, 0, '', STATISTICS_SERVER);
            }
            myadmin_log('fantastico', 'debug', json_encode($response), __LINE__, __FILE__);
            if (isset($response['fault '])) {
                $response['fault'] = $response['fault '];
                unset($response['fault ']);
            }
        }
        $this->cache = [];
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
     * @param string $ipAddress ip address
     * @param integer $type license type
     * @return array response array containing a faultcode and fault, or ip and id on success
     */
    public function addIp($ipAddress, $type)
    {
        if (!$this->validIp($ipAddress)) {
            $response = ['faultcode' => 1, 'fault' => 'Invalid IP Address '.$ipAddress];
        } else {
            $this->connect();
            \StatisticClient::tick('Fantastico', 'addIp');
            $response = json_decode($this->soapClient->addIp($this->getHash(), $ipAddress, $type), true);
            if ($response === false) {
                \StatisticClient::report('Fantastico', 'addIp', false, 1, 'Soap Client Error', STATISTICS_SERVER);
            } else {
                \StatisticClient::report('Fantastico', 'addIp', true, 0, '', STATISTICS_SERVER);
            }
            myadmin_log('fantastico', 'debug', json_encode($response), __LINE__, __FILE__);
            if (isset($response['fault '])) {
                $response['fault'] = $response['fault '];
                unset($response['fault ']);
            }
        }
        $this->cache = [];
        return $response;
    }

    /**
     * @param $function
     * @param $ipAddress
     * @return array|mixed
     */
    private function soapIpFunction($function, $ipAddress)
    {
        if (!$this->validIp($ipAddress)) {
            return ['faultcode' => 1, 'fault ' => 'Invalid IP Address '.$ipAddress];
        }
        $this->connect();
        \StatisticClient::tick('Fantastico', $function);
        $response = json_decode($this->soapClient->$function($this->getHash(), $ipAddress), true);
        if ($response === false) {
            \StatisticClient::report('Fantastico', $function, false, 1, 'Soap Client Error', STATISTICS_SERVER);
        } else {
            \StatisticClient::report('Fantastico', $function, true, 0, '', STATISTICS_SERVER);
        }
        myadmin_log('fantastico', 'debug', json_encode($response), __LINE__, __FILE__);
        $this->cache = [];
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
     * @param mixed $ipAddress
     * @return void
     */
    public function deactivateIp($ipAddress)
    {
        return $this->soapIpFunction('deactivateIp', $ipAddress);
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
     * @param mixed $ipAddress
     * @return void
     */
    public function reactivateIp($ipAddress)
    {
        return $this->soapIpFunction('reactivateIp', $ipAddress);
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
     * @param mixed $ipAddress
     * @return void
     */
    public function deleteIp($ipAddress)
    {
        return $this->soapIpFunction('deleteIp', $ipAddress);
    }
}
