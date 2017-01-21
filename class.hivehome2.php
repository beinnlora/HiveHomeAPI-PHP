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

v0.3 January 11 2017
http://www.smartofthehome.com/wp-content/uploads/2016/03/AlertMe-API-v6.1-Documentation.pdf
http://www.smartofthehome.com/2016/05/hive-rest-api-v6/


* 0.1 first draft 
* 0.2 getTemperature
* 0.3 get state of the heater (whether boiler is running or not) - transform into your required value using the calling php
* 0.2.3  Forked from class.hivehome (should really learn how to do that in github)
* this version DOES NOT WORK - go and use class.hivehome until this one is fixed.


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
        }
        return $body["sessions"][0]["sessionId"];
        
        //once we have anthenticated, let's now create an array of HiveHomeNode objects. Primarily for recalling the ID of each unit.
        // we don't want to cache our temperatures really
    }
    
    /**
     * Helper method for making POST requests
     */
    private function curlPOST($url, $body, $sid = "")
    {
        $ch = curl_init($this->client["baseurl"] . $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //curl_setopt($ch, CURLOPT_USERAGENT, $this->client["user-agent"]);
        if (strlen($authentication) > 0) {
            curl_setopt($ch, CURLOPT_USERPWD, $authentication);
        }
        $arrHeaders                   = array();
        $arrHeaders["Content-Length"] = strlen($request);
        foreach ($this->client["headers"] as $key => $value) {
            array_push($arrHeaders, $key . ": " . $value);
        }
        //add sessionId if passed
        if (strlen($sid) > 0) {
            array_push($arrHeaders, "X-Omnia-Access-Token" . ": " . $sid);
        }
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
        }
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
        }
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //curl_setopt($ch, CURLOPT_USERAGENT, $this->client["user-agent"]);
        //    if (strlen($authentication) > 0) {
        //    curl_setopt($ch, CURLOPT_USERPWD, $authentication);  
        //}
        $arrHeaders                   = array();
        $arrHeaders["Content-Length"] = strlen($request);
        foreach ($this->client["headers"] as $key => $value) {
            array_push($arrHeaders, $key . ": " . $value);
        }
        //add sessionId if passed
        if (strlen($sid) > 0) {
            array_push($arrHeaders, "X-Omnia-Access-Token" . ": " . $sid);
        }
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
        }
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
        }
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
        }
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
                //TWO RECEIVERS in a heating/hot water system.
                //one for heating, that will have targetHeatTemperature
                //one for hot water, that will have ["attributes"]"["supportsHotWater"] = 1
                $target = $nodelist["nodes"][$counter]["attributes"]["targetHeatTemperature"];
                
                if (sizeof($target) > 0) {
                    $node_id    = $nodelist["nodes"][$counter]["id"];
                    $actualTemp = $nodelist["nodes"][$counter]["attributes"]["temperature"]["displayValue"];
                }
                $counter++;
            }
        }
        if ($headers["http_code"] == 401) {
            throw new Exception('Your errpr');
        }
        //return $body["sessions"][0]["sessionId"];
        if (sizeof($node_id) > 0) {
            //print "getting current temp"."\n";
            return $actualTemp;
        } else {
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
                }
                $counter++;
            }
        }
        
        if ($headers["http_code"] == 401) {
            throw new Exception('Your errpr');
        }
        
        if (sizeof($node_id) > 0) {
            return $targetTemp;
        } else {
            return "";
        }
    }
    
    /**/
    /* GET Current heating relay status (boiler on or off from receiver */
    /**/
    public function getHeaterState()
    {
        $nodelist   = $this->getNodes();
        $node_id    = "";
        $counter    = 0;
        $heaterState = "";
        foreach ($nodelist as $onenode) {
            foreach ($onenode as $myitem) {
                //print ("Receiver node name:".$nodelist["nodes"][$counter]["name"]."\n");
                $target = $nodelist["nodes"][$counter]["attributes"]["stateHeatingRelay"];
                
                if (sizeof($target) > 0) {
                    $node_id    = $nodelist["nodes"][$counter]["id"];
                    $heaterState = $nodelist["nodes"][$counter]["attributes"]["stateHeatingRelay"]["displayValue"];
                }
                $counter++;
            }
        }
        if ($headers["http_code"] == 401) {
            throw new Exception('Your errpr');
        }
        //return $body["sessions"][0]["sessionId"];
        if (sizeof($node_id) > 0) {
            
            return $heaterState;
        } else {
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
        }
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
        }
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
        }
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
        }
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
    private function createHome($nodes)
    {
		//function to populate a basic HiveHomeBlob from a /GET/nodes request
		
		
		/*class HiveHomeNode
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
class HiveHomeBlob
{
	public $nodeList;		// /GET /nodes response json array
	public $hubNode;		// whole json node of hub (type HiveHomeNode)
	
	public $heatingNode;
	
	public $hotWaterNode;	
	
	*/
		$house = new HiveHomeHouse();
		$house->nodeList										= $nodes; //entire nodelist for the house
		$house->hubNode											= new HiveHomeNode;
		$house->hubNode->nodeid									= ; //parse $nodes to isolate which one is which
		$house->hubNode->nodename								=;
		$house->hubNode->nodeattributes							= new HiveHomeNodeAttributes;
		$house->hubNode->nodeattributes->attributes				= ;//whole array of attributes
		/*	public attributes;				//whole array of all attributes, in case we miss some
	public stateHotWaterRelay;
	public targetHeatTemperature;
	public supportsHotWater;
	public temperature;
	public stateHeatingRelay;
	public stateHotWaterRelay;*/
		$house->hubNode->nodeattributes->stateHotWaterRelay
		$house->hubNode->nodeattributes->targetHeatTemperature
		$house->hubNode->nodeattributes->supportsHotWater
		$house->hubNode->nodeattributes->temperature
		$house->hubNode->nodeattributes->stateHeatingRelay
		$house->hubNode->nodeattributes->stateHotWaterRelay
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
        }
        list($headers, $body) = $this->curlPOST($url, $body, $this->username . ":" . $this->password);
        $this->devices = array();
        for ($x = 0; $x < sizeof($body["content"]); $x++) {
            $device                     = $this->generateDevice($body["content"][$x]);
            $this->devices[$device->ID] = $device;
        }
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

class HiveHomeChannel
{
    
}
class HiveHomeHouse	//our specific hive home installation, split into specific nodes
{
	public $nodeList;		// /GET /nodes response json array
	public $hubNode;		// whole json node of hub (type HiveHomeNode)
	public $heatingNode;
	public $hotWaterNode;
	//public static synthetic
	
	//CONSTRUCTOR method
	public function __construct($nodes)
		
		$this->nodeList = $nodes;
		$this->hubNode = new HiveHomeNode($nodes,"hub");
		$this->heatingNode = new HiveHomeNode($nodes,"heating");
		$this->hotWaterNode = new HiveHomeNode($nodes,"water");
		//synthetic?
	//store entire node array into the house
	//$
	
	
}
class HiveHomeNode
{
    public $node;			//whole json array of node
    public $nodeid;			//id of node
    public $nodehref;		
    public $nodelinks;
    public $nodename;		//name of node (Receiver, hub etc)
    public $nodetype;
    public $nodeattributes;	//array of attributes
    
 
 
    //not quite right above - when do we tell it it is a HiveHomeNode
    //CONSTRUCTOR
    public function __construct($nodes,$devicetype) //hub//heating//water
		//parse all info in $nodes and return just the node of the specified device type
	
	$this->node									=; //just the node we want after parsing
	$this->nodeid								=;//extract nodeID and store here
	$this->nodehref
	$this->nodelinks
	$this->nodename
	$this->nodetype
	$this->nodeattributes			= new HiveHomeNodeAttributes($this->node, $devicetype);//pass it the node specific to that device, and also the devicetype
	
    
}
class HiveHomeNodeAttributes
{
	//which attributes do we care about across all possible types of devices
	public attributes;				//whole array of all attributes, in case we miss some
	public stateHotWaterRelay;
	public targetHeatTemperature;
	public supportsHotWater;
	public temperature;
	public stateHeatingRelay;
	public stateHotWaterRelay;
	
	public function __construct($node, $devicetype) //heating//hotwater{
		//parse the node and extract individual items as required depending on the device type
		//
		$this->attributes - ;//parse the single node of ths device and store the attributes as an array
		$this->stateHotWaterRelay = ;
		$this->targetHeatTemperature=;
		$this->supportsHotWater
		$this->stateHeatingRelay
		$this->stateHotWaterRelay
	}
}


