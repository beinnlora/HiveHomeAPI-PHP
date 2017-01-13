<?PHP
/*LOCAL
Copyright (C) Stephen Wilson (steve@stevewilson.it).

Licensed under the Apache License, Version 2.0 (the "License");

you may not use this file except in compliance with the License.
You may obtain a copy of the License at
http://www.apache.org/licenses/LICENSE-2.0
Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

v0.1 January 11 2017
http://www.smartofthehome.com/wp-content/uploads/2016/03/AlertMe-API-v6.1-Documentation.pdf
http://www.smartofthehome.com/2016/05/hive-rest-api-v6/

0.1 first draft 
0.2 working getCurrentTemperature and GetTargetTemperature. 
* Inefficient at the moment as discovery of receiverID is not recycled

*/


class HiveHome
{
	private $client = array( /*"app-version" => "1.0",
	//"user-agent" => "HiveHome/001 Network/001 Network/1.0.0",*/ "baseurl" => "https://api-prod.bgchprod.info:443/omnia", "headers" => array("Content-Type" => "application/vnd.alertme.zoo-6.1+json", "Accept" => "application/vnd.alertme.zoo-6.1+json", "X-Omnia-Client" => "Hive Web Dashboard"));
	private $debug;
	private $username;
	private $password;
	private $sessionId; //returned from the login call
	//public $devices = array();
	/**
	 * This is where you initialize HiveHome with your Hive credentials
	 * Example: $hive = new HiveHome("you@example.com", "MyPassWord123");
	 *
	 * @param username    HiveHome username
	 * @param password    HiveHome password
	 * @param debug        (Optional) Set to TRUE and all the API requests and responses will be printed out
	 * @return          HiveHome object instance 
	 */
	public function __construct($username, $password, $debug = false)
	{
		$this->username  = $username;
		$this->password  = $password;
		$this->debug     = $debug;
		if (!$debug) {error_reporting(0);}
		$this->sessionId = $this->authenticate();
	}
	/**
	 *  This is where the users credentials are authenticated.
	 *  The sessionId is saved and used in subsequent calls
	 */
	private function authenticate()
	{
		$url      = "/auth/sessions";
		//build body
		$auth     = array(
			"username" => $this->username,
			"password" => $this->password
		);
		$authbody = json_encode(array(
			"sessions" => array(
				$auth
			)
		));
		list($headers, $body) = $this->curlPOST($url, $authbody, "");
		//$this->username.":".$this->password
		//$res = print_r(json_decode($body, true));
		//$this->$sessionId = ($body["sessions"][0]["sessionId"]);
		if ($headers["http_code"] == 401) {
			throw new Exception('Your iCloud username and/or password are invalid');
		} //$headers["http_code"] == 401
		return $body["sessions"][0]["sessionId"];
	}
	/**
	 * Helper method for making POST requests
	 */
	private function curlPOST($url, $body, $sid = "")
	{
		$ch = curl_init($this->client["baseurl"] . $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//curl_setopt($ch, CURLOPT_USERAGENT, $this->client["user-agent"]);
		if (strlen($authentication) > 0) {
			curl_setopt($ch, CURLOPT_USERPWD, $authentication);
		} //strlen($authentication) > 0
		$arrHeaders                   = array();
		$arrHeaders["Content-Length"] = strlen($request);
		foreach ($this->client["headers"] as $key => $value) {
			array_push($arrHeaders, $key . ": " . $value);
		} //$this->client["headers"] as $key => $value
		//add sessionId if passed
		if (strlen($sid) > 0) {
			array_push($arrHeaders, "X-Omnia-Access-Token" . ": " . $sid);
		} //strlen($sid) > 0
		curl_setopt($ch, CURLOPT_HTTPHEADER, $arrHeaders);
		$response     = curl_exec($ch);
		$info         = curl_getinfo($ch);
		$header_size  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$responseBody = substr($response, $header_size);
		$headers      = array();
		foreach (explode("\r\n", substr($response, 0, $header_size)) as $i => $line) {
			if ($i === 0)
				$headers['http_code'] = $info["http_code"];
			else {
				list($key, $value) = explode(': ', $line);
				if (strlen($key) > 0)
					$headers[$key] = $value;
			}
		} //explode("\r\n", substr($response, 0, $header_size)) as $i => $line
		if ($this->debug) {
			$debugURL          = htmlentities($url);
			$debugRequestBody  = htmlentities(print_r(json_decode($body, true), true));
			$debugHeaders      = htmlentities(print_r($headers, true));
			$debugResponseBody = htmlentities(print_r(json_decode($responseBody, true), true));
			print <<<HTML
               <PRE>
                <TABLE BORDER="1" CELLPADDING="3">
                    <TR>
                        <TD VALIGN="top"><B>URL</B></TD>
                        <TD VALIGN="top">$debugURL</TD>
                    </TR>
                    <TR>
                        <TD VALIGN="top"><B>Request Body</B></TD>
                        <TD VALIGN="top"><PRE>$debugRequestBody</PRE></TD>
                    </TR>
                    <TR>
                        <TD VALIGN="top"><B>Response Headers</B></TD>
                        <TD VALIGN="top"><PRE>$debugHeaders</PRE></TD>
                    </TR>
                    <TR>
                        <TD VALIGN="top"><B>Response Body</B></TD>
                        <TD VALIGN="top"><PRE>$debugResponseBody</PRE></TD>
                    </TR>
                </TABLE>
                </PRE>
HTML;
		} //$this->debug
		return array(
			$headers,
			json_decode($responseBody, true)
		);
	}
	/*** GET helper **/
	private function curlGET($url, $sid = "")
	{
		$ch = curl_init($this->client["baseurl"] . $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $body);                                                                  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//curl_setopt($ch, CURLOPT_USERAGENT, $this->client["user-agent"]);
		//    if (strlen($authentication) > 0) {
		//    curl_setopt($ch, CURLOPT_USERPWD, $authentication);  
		//}
		$arrHeaders                   = array();
		$arrHeaders["Content-Length"] = strlen($request);
		foreach ($this->client["headers"] as $key => $value) {
			array_push($arrHeaders, $key . ": " . $value);
		} //$this->client["headers"] as $key => $value
		//add sessionId if passed
		if (strlen($sid) > 0) {
			array_push($arrHeaders, "X-Omnia-Access-Token" . ": " . $sid);
		} //strlen($sid) > 0
		curl_setopt($ch, CURLOPT_HTTPHEADER, $arrHeaders);
		$response     = curl_exec($ch);
		$info         = curl_getinfo($ch);
		$header_size  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$responseBody = substr($response, $header_size);
		$headers      = array();
		foreach (explode("\r\n", substr($response, 0, $header_size)) as $i => $line) {
			if ($i === 0)
				$headers['http_code'] = $info["http_code"];
			else {
				list($key, $value) = explode(': ', $line);
				if (strlen($key) > 0)
					$headers[$key] = $value;
			}
		} //explode("\r\n", substr($response, 0, $header_size)) as $i => $line
		if ($this->debug) {
			$debugURL          = htmlentities($url);
			//$debugRequestBody = htmlentities(print_r(json_decode($body, true), true));
			$debugHeaders      = htmlentities(print_r($headers, true));
			$debugResponseBody = htmlentities(print_r(json_decode($responseBody, true), true));
			print <<<HTML
               <PRE>
                <TABLE BORDER="1" CELLPADDING="3">
                    <TR>
                        <TD VALIGN="top"><B>URL</B></TD>
                        <TD VALIGN="top">$debugURL</TD>
                    </TR>
                    
                    <TR>
                        <TD VALIGN="top"><B>Response Headers</B></TD>
                        <TD VALIGN="top"><PRE>$debugHeaders</PRE></TD>
                    </TR>
                    <TR>
                        <TD VALIGN="top"><B>Response Body</B></TD>
                        <TD VALIGN="top"><PRE>$debugResponseBody</PRE></TD>
                    </TR>
                </TABLE>
                </PRE>
HTML;
		} //$this->debug
		return array(
			$headers,
			json_decode($responseBody, true)
		);
	}
	/*** **/
	/* this method just lists devices (nodes)
	 * GET /nodes
	 * */
	public function getNodes()
	{
		//print "********";
		//print "getNodes "."\n";;
		$url = "/nodes";
		//no body to send, but we receive a body
		list($headers, $body) = $this->curlGET($url, $this->sessionId);
		//$this->username.":".$this->password
		//print $body;
		//$this->$sessionId = ($body["sessions"][0]["sessionId"]);
		if ($headers["http_code"] == 401) {
			throw new Exception('Your errpr');
		} //$headers["http_code"] == 401
		//return $body["sessions"][0]["sessionId"];
		return $body;
	}
	/**/
	/* GET Current Temperature from receiver */
	/**/
	public function getCurrentTemperature()
	{
		$nodelist   = $this->getNodes();
		$node_id    = "";
		$counter    = 0;
		$actualTemp = "";
		foreach ($nodelist as $onenode) {
			foreach ($onenode as $myitem) {
				//print ("Receiver node name:".$nodelist["nodes"][$counter]["name"]."\n");
				$target = $nodelist["nodes"][$counter]["attributes"]["targetHeatTemperature"];
				if (sizeof($target) > 0) {
					$node_id    = $nodelist["nodes"][$counter]["id"];
					$actualTemp = $nodelist["nodes"][$counter]["attributes"]["temperature"]["displayValue"];
				} //sizeof($target) > 0
				$counter++;
			} //$onenode as $myitem
		} //$nodelist as $onenode
		if ($headers["http_code"] == 401) {
			throw new Exception('Your errpr');
		} //$headers["http_code"] == 401
		//return $body["sessions"][0]["sessionId"];
		if (sizeof($node_id) > 0) {
			//print "getting current temp"."\n";
			return $actualTemp;
		} //sizeof($node_id) > 0
		else {
			return "";
		}
	}
	/**/
	/* GET Target Temperature from receiver */
	/**/
	public function getTargetTemperature()
	{
		$nodelist   = $this->getNodes();
		$node_id    = "";
		$counter    = 0;
		$actualTemp = "";
		foreach ($nodelist as $onenode) {
			foreach ($onenode as $myitem) {
				$target = $nodelist["nodes"][$counter]["attributes"]["temperature"];
				if (sizeof($target) > 0) {
					$node_id    = $nodelist["nodes"][$counter]["id"];
					//print ("Receiver node name:".$nodelist["nodes"][$counter]["name"]."\n");
					//print ("Receiver node id: ".$node_id."\n");
					//print ("Target: ".$nodelist["nodes"][$counter]["attributes"]["targetHeatTemperature"]["displayValue"]."\n");
					//print ("Actual: ".$nodelist["nodes"][$counter]["attributes"]["temperature"]["displayValue"]."\n");
					$targetTemp = $nodelist["nodes"][$counter]["attributes"]["targetHeatTemperature"]["displayValue"];
				} //sizeof($target) > 0
				$counter++;
			} //$onenode as $myitem
		} //$nodelist as $onenode
		if ($headers["http_code"] == 401) {
			throw new Exception('Your errpr');
		} //$headers["http_code"] == 401
		if (sizeof($node_id) > 0) {
			return $targetTemp;
		} //sizeof($node_id) > 0
		else {
			return "";
		}
	}
	/* get HiveHome channel list */
	/* incomplete*/
	public function getChannels()
	{
		print "********";
		print "getChannels   \r\n";
		//$url = "/nodes";
		$url = "/channels";
		//no body to send, but we receive a body
		list($headers, $body) = $this->curlGET($url, $this->sessionId);
		//$this->username.":".$this->password
		//print $body;
		//cycle through node array, looking for RECEIVER
		$currentTemp = "";
		$counter     = 0;
		/*    foreach ($body  as $obj) {
		
		foreach ($obj  as $layer) {
		print $layer;
		foreach ($layer  as $layer2) {
		print $layer2;
		}
		}
		}*/
		//$this->$sessionId = ($body["sessions"][0]["sessionId"]);
		if ($headers["http_code"] == 401) {
			throw new Exception('Your errpr');
		} //$headers["http_code"] == 401
		//return $body["sessions"][0]["sessionId"];
	}
	/**
	 * This method attempts to get the most current location of a device
	 * Example: $fmi->locate("dCsaBcqBOdnNop4wvy2VfIk8+HlQ/DRuqrmiwpsLdLTuiCORQDJ9eHYVQSUzmWV", 30);
	 *
	 * @param deviceID    ID of the device you want to locate
	 * @param timeout    (Optional) Maximum number of seconds to spend trying to locate the device
	 * @return          FindMyiPhoneLocation object 
	 */
	public function locate($deviceID, $timeout = 60)
	{
		$startTime        = time();
		$initialTimestamp = $this->devices[$deviceID]->location->timestamp;
		while ($initialTimestamp == $this->devices[$deviceID]->location->timestamp) {
			if ((time() - $startTime) > $timeout)
				break;
			$this->refreshDevices($deviceID);
			sleep(5);
		} //$initialTimestamp == $this->devices[$deviceID]->location->timestamp
		return $this->devices[$deviceID]->location;
	}
	/**
	 * Play a sound and display a message on a device
	 * Example: $fmi->playSound("dCsaBcqBOdnNop4wvy2VfIk8+HlQ/DRuqrmiwpsLdLTuiCORQDJ9eHYVQSUzmWV", "Whats up?");
	 *
	 * @param deviceID    ID of the device you want to play a sound
	 * @param message    Message you want displayed on the device
	 */
	public function playSound($deviceID, $message)
	{
		$url  = "https://fmipmobile.icloud.com/fmipservice/device/" . $this->username . "/playSound";
		$body = json_encode(array(
			"device" => $deviceID,
			"subject" => $message
		));
		list($headers, $body) = $this->curlPOST($url, $body, $this->username . ":" . $this->password);
	}
	/**
	 * Put a device into lost mode. The device will immediately lock until the user enters the correct passcode
	 * Example: $fmi->lostMode("dCsaBcqBOdnNop4wvy2VfIk8+HlQ/DRuqrmiwpsLdLTuiCORQDJ9eHYVQSUzmWV", "You got locked out", "555-555-5555");
	 *
	 * @param deviceID        ID of the device you want to lock
	 * @param message        Message you want displayed on the device
	 * @param phoneNumber    (Optional) Phone number you want displayed on the lock screen
	 */
	public function lostMode($deviceID, $message, $phoneNumber = "")
	{
		$url  = "https://fmipmobile.icloud.com/fmipservice/device/" . $this->username . "/lostDevice";
		$body = json_encode(array(
			"device" => $deviceID,
			"ownerNbr" => $phoneNumber,
			"text" => $message,
			"lostModeEnabled" => true
		));
		list($headers, $body) = $this->curlPOST($url, $body, $this->username . ":" . $this->password);
	}
	/**
	 * Print all the available information for every device on the users account.
	 * This is really useful when you want to get the ID for a device.
	 * Example: $fmi->printDevices();
	 */
	public function printDevices()
	{
		if (sizeof($this->devices) == 0)
			$this->getDevices();
		print <<<TABLEHEADER
               <PRE>
                <TABLE BORDER="1" CELLPADDING="3">
                    <TR>
                        <TD VALIGN="top"><B>ID</B></TD>
                        <TD VALIGN="top"><B>name</B></TD>
                        <TD VALIGN="top"><B>displayName</B></TD>
                        <TD VALIGN="top"><B>location</B></TD>
                        <TD VALIGN="top"><B>class</B></TD>
                        <TD VALIGN="top"><B>model</B></TD>
                        <TD VALIGN="top"><B>modelDisplayName</B></TD>
                        <TD VALIGN="top"><B>batteryLevel</B></TD>
                        <TD VALIGN="top"><B>batteryStatus</B></TD>
                    </TR>
TABLEHEADER;
		foreach ($this->devices as $device) {
			$location = <<<LOCATION
           <TABLE BORDER="1">
                <TR>
                    <TD VALIGN="top">timestamp</TD>
                    <TD VALIGN="top">{$device->location->timestamp}</TD>
                </TR>
                <TR>
                    <TD VALIGN="top">horizontalAccuracy</TD>
                    <TD VALIGN="top">{$device->location->horizontalAccuracy}</TD>
                </TR>
                <TR>
                    <TD VALIGN="top">positionType</TD>
                    <TD VALIGN="top">{$device->location->positionType}</TD>
                </TR>
                <TR>
                    <TD VALIGN="top">longitude</TD>
                    <TD VALIGN="top">{$device->location->longitude}</TD>
                </TR>
                <TR>
                    <TD VALIGN="top">latitude</TD>
                    <TD VALIGN="top">{$device->location->latitude}</TD>
                </TR>
            </TABLE>
LOCATION;
			print <<<DEVICE
                   <TR>
                        <TD VALIGN="top">{$device->ID}</TD>
                        <TD VALIGN="top">{$device->name}</TD>
                        <TD VALIGN="top">{$device->displayName}</TD>
                        <TD VALIGN="top">$location</TD>
                        <TD VALIGN="top">{$device->class}</TD>
                        <TD VALIGN="top">{$device->model}</TD>
                        <TD VALIGN="top">{$device->modelDisplayName}</TD>
                        <TD VALIGN="top">{$device->batteryLevel}</TD>
                        <TD VALIGN="top">{$device->batteryStatus}</TD>
                    </TR>
DEVICE;
		} //$this->devices as $device
		print <<<TABLEFOOTER
               </TABLE>
                </PRE>
TABLEFOOTER;
	}
	/**
	 * This is where all the devices are downloaded and processed
	 * Example: print_r($fmi->devices)
	 */
	private function getDevices()
	{
		$url = "https://fmipmobile.icloud.com/fmipservice/device/" . $this->username . "/initClient";
		list($headers, $body) = $this->curlPOST($url, "", $this->username . ":" . $this->password);
		$this->devices = array();
		for ($x = 0; $x < sizeof($body["content"]); $x++) {
			$device                     = $this->generateDevice($body["content"][$x]);
			$this->devices[$device->ID] = $device;
		} //$x = 0; $x < sizeof($body["content"]); $x++
	}
	/**
	 * This method takes the raw device details from the API and converts it to a FindMyiPhoneDevice object
	 */
	private function generateDevice($deviceDetails)
	{
		$device                               = new FindMyiPhoneDevice();
		$device->API                          = $deviceDetails;
		$device->ID                           = $device->API["id"];
		$device->batteryLevel                 = $device->API["batteryLevel"];
		$device->batteryStatus                = $device->API["batteryStatus"];
		$device->class                        = $device->API["deviceClass"];
		$device->displayName                  = $device->API["deviceDisplayName"];
		$device->location                     = new FindMyiPhoneLocation();
		$device->location->timestamp          = $device->API["location"]["timeStamp"];
		$device->location->horizontalAccuracy = $device->API["location"]["horizontalAccuracy"];
		$device->location->positionType       = $device->API["location"]["positionType"];
		$device->location->longitude          = $device->API["location"]["longitude"];
		$device->location->latitude           = $device->API["location"]["latitude"];
		$device->model                        = $device->API["rawDeviceModel"];
		$device->modelDisplayName             = $device->API["modelDisplayName"];
		$device->name                         = $device->API["name"];
		return $device;
	}
	/**
	 * This method refreshes the list of devices on the users iCloud account
	 */
	private function refreshDevices($deviceID = "")
	{
		$url = "https://fmipmobile.icloud.com/fmipservice/device/" . $this->username . "/refreshClient";
		if (strlen($deviceID) > 0) {
			$body = json_encode(array(
				"clientContext" => array(
					"appVersion" => $this->client["app-version"],
					"shouldLocate" => true,
					"selectedDevice" => $deviceID,
					"fmly" => true
				)
			));
		} //strlen($deviceID) > 0
		list($headers, $body) = $this->curlPOST($url, $body, $this->username . ":" . $this->password);
		$this->devices = array();
		for ($x = 0; $x < sizeof($body["content"]); $x++) {
			$device                     = $this->generateDevice($body["content"][$x]);
			$this->devices[$device->ID] = $device;
		} //$x = 0; $x < sizeof($body["content"]); $x++
	}
}
/*
class FindMyiPhoneDevice {
public $ID;
public $batteryLevel;
public $batteryStatus;
public $class;
public $displayName;
public $location;
public $model;
public $modelDisplayName;
public $name;
public $API;
}


class FindMyiPhoneLocation
{
public $timestamp;
public $horizontalAccuracy;
public $positionType;
public $longitude;
public $latitude;
}
*/
class HiveHomeInstance
{
	public $responsemeta;
	public $responselinks;
	public $responselinked;
	public $responsenodes;
}
class HiveHomeNode
{
	public $nodeid;
	public $nodehref;
	public $nodelinks;
	public $nodename;
	public $nodetype;
	public $nodeattributes;
}
class HiveHomeChannel
{
}
