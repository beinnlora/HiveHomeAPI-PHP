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

