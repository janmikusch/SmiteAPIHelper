<?php

/*
 * SmiteAPIHelper
 * by Chorizorro
 * v1.0
 * 2013-01-12
 */
abstract class SmiteAPIHelper {
	
	
	//
	// CONSTANTS
	//
	
	
	// Defining const strings for requests preferred format
	const SMITE_API_FORMAT_XML = "xml";
	const SMITE_API_FORMAT_JSON = "json";
	
	
	//
	// MEMBERS
	//
	
	
	// Response format preference
	public static $_format = SmiteAPIHelper::SMITE_API_FORMAT_XML;
	// Smite API access session id
	private static $_sessionId = null;
	// Timeout for the recorded session ID
	private static $_sessionTimeout = null;
	// Smite API DevId provided by Hi-Rez
	private static $_devId = 0;
	// Smite API AuthKey provided by Hi-Rez
	private static $_authKey = "";
	
	
	//
	// METHODS
	//
	
	
	// Defining a getter for private members
	public static function get($key) {
		switch($key) {
			case "sessionId":
				return SmiteAPIHelper::$_sessionId;
			case "sessionTimeout":
				return SmiteAPIHelper::$_sessionTimeout;
			default:
				trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Unkown key \"".  htmlspecialchars($key)."\" in getter", E_USER_WARNING);
				return null;
		}
	}
	
	/*
	 * Sets the credentials to use the API
	 * 
	 * $devId: integer or string containing an integer, corresponding to your devId
	 * $authKey: string containing hexadecimal figures only, corresopnding to your authKey
	 * 
	 * Returns a boolean indicating if the credentials were successfully set
	 */
	public static function setCredentials($devId, $authKey) {
		// Checking parameters
		$errors = 0;
		$devIdType = gettype($devId);
		$authKeyType = gettype($authKey);
		// Checking devId
		if($devIdType !== "integer") {
			if($devIdType !== "string") {
				trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$devId must be a strict integer or an integer in a string ($devIdType given)", E_USER_ERROR);
				$errors++;
			}
			else if(($devId = intval($devId)) === 0) {
				trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Invalid \$devId string given \"".htmlspecialchars($devId)."\"", E_USER_ERROR);
				$errors++;
			}
		}
		// Checking authkey
		if($authKeyType !== "string") {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$authKey must be a string ($authKeyType given)", E_USER_ERROR);
			$errors++;
		}
		// FIXME Not sure if authKey is always supposed to be 32 chars long
//		else if (!preg_match("/^[A-Z0-9]{32}$/i", $authKey)) {
//			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$authKey must contain 32 hexadecimal figures (\"".htmlspecialchars($authKey)."\" given)", E_USER_ERROR);
//			$errors++;
//		}
		else if (!preg_match("/^[A-Z0-9]+$/i", $authKey)) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$authKey must contain only hexadecimal figures (\"".htmlspecialchars($authKey)."\" given)", E_USER_ERROR);
			$errors++;
		}
		// Exit the function if some parameters are invalid
		if($errors) return false;
		unset($devIdType, $authKeyType, $errors);
		// Set the credentials
		SmiteAPIHelper::$_devId = $devId;
		SmiteAPIHelper::$_authKey = $authKey;
		return true;
	}
	
	/*
	 * Loads a session id and timeout
	 * 
	 * $sessionId: string containing hexadecimal figures only, corresponding to a session ID
	 * $timeout: DateTime object on UTC timezone, corresponding to the time where the given sessionId will become invalid
	 * 
	 * Returns a boolean indicating if the session was successfully set
	 */
	public static function setSession($sessionId, $timeout) {
		// Checking parameters
		$errors = 0;
		$sessionIdType = gettype($sessionId);
		// Checking sessionId
		if($sessionIdType !== "string") {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$authKey must be a string ($sessionIdType given)", E_USER_ERROR);
			$errors++;
		}
		// FIXME Not sure if sessionId is always supposed to be 32 chars long
//		else if (!preg_match("/^[A-Z0-9]{32}$/i", $sessionId)) {
//			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$sessionId must contain 32 hexadecimal figures (\"".htmlspecialchars($authKey)."\" given)", E_USER_ERROR);
//			$errors++;
//		}
		else if (!preg_match("/^[A-Z0-9]+$/i", $sessionId)) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$sessionId must contain only hexadecimal figures (\"".htmlspecialchars($sessionId)."\" given)", E_USER_ERROR);
			$errors++;
		}
		// Checking sessionId
		if(!(isset($timeout) && $timeout instanceof DateTime)) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$timeout must contain a valid unix timestamp (\"".htmlspecialchars($timeout)."\" given)", E_USER_ERROR);
			$errors++;
		}
		else if($timeout <= new DateTime("now", new DateTimeZone("UTC"))) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") The session is timed out", E_USER_WARNING);
			$errors++;
		}
		// Exit the function if some parameters are invalid
		if($errors) return false;
		unset($sessionIdType, $errors);
		// Set the session
		SmiteAPIHelper::$_sessionId = $sessionId;
		SmiteAPIHelper::$_sessionTimeout = $timeout;
		return true;
	}
	
	// Getter 
	
	// Checking the credentials
	public static function createSession() {
		if(!(SmiteAPIHelper::$_devId && SmiteAPIHelper::$_authKey)) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Smite API Credentials must be set before any request", E_USER_ERROR);
			return null;
		}
		if(($signature = SmiteAPIHelper::getSignature("createsession")) === null) return false;
		$url = "createsession".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis"));
		return SmiteAPIHelper::executeRequest($url);
	}
	
	/*
	 * Send a getitems request
	 * 
	 * $lang (optional): integer (1 for English, 3 for French)
	 * 
	 * Returns the web-service response or null if an error occurred
	 */
	public static function getItems($lang = 1) {
		// Checking parameter
		if(!in_array($lang, Array(1, 3))) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$lang must be an integer (1 for English or 3 for French) (\"".htmlspecialchars($lang)."\" given). Assuming 1 by default", E_USER_WARNING);
			$lang = 1;
		}
		// Creating or retrieving session
		if(!SmiteAPIHelper::createSessionIfNecessary()) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Session creation failed.", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("getitems")) === null) return false;
		$url = "getitems".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".SmiteAPIHelper::$_sessionId."/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis"))."/".$lang;
		return SmiteAPIHelper::executeRequest($url);
	}
	
	/*
	 * Send a getplayer request
	 * 
	 * $playerName: string containing a player name
	 * 
	 * Returns the web-service response or null if an error occurred
	 */
	public static function getPlayer($playerName) {
		// Checking parameter
		$playerNameType = gettype($playerName);
		if($playerNameType !== "string" || empty($playerName)) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$playerName must be a non-empty string (\"$playerNameType\" given)", E_USER_ERROR);
			return null;
		}
		unset($playerNameType);
		// Creating or retrieving session
		if(!SmiteAPIHelper::createSessionIfNecessary()) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Session creation failed.", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("getplayer")) === null) return false;
		$url = "getplayer".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".SmiteAPIHelper::$_sessionId."/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis"))."/".$playerName;
		return SmiteAPIHelper::executeRequest($url);
	}
	
	/*
	 * Send a getmatchdetails request
	 * 
	 * $mapId: integer or string formatted integer corresponding to the match id (retrieved via getmatchhistory)
	 * 
	 * Returns the web-service response or null if an error occurred
	 */
	public static function getMatchDetails($mapId) {
		// Checking parameter
		$mapIdType = gettype($mapId);
		if($mapIdType !== "integer") {
			if($mapIdType !== "string") {
				trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$mapId must be a strict integer or an integer in a string ($mapIdType given)", E_USER_ERROR);
				return null;
			}
			if(($mapId = intval($mapId)) === 0) {
				trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Invalid \$mapId string given \"".htmlspecialchars($mapId)."\"", E_USER_ERROR);
				return null;
			}
		}
		unset($mapIdType);
		// Creating or retrieving session
		if(!SmiteAPIHelper::createSessionIfNecessary()) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Session creation failed.", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("getmatchdetails")) === null) return false;
		$url = "getmatchdetails".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".SmiteAPIHelper::$_sessionId."/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis"))."/".$mapId;
		return SmiteAPIHelper::executeRequest($url);
	}
	
	/*
	 * Send a getmatchhistory request
	 * 
	 * $playerName: string containing a player name
	 * 
	 * Returns the web-service response or null if an error occurred
	 */
	public static function getMatchHistory($playerName) {
		// Checking parameter
		$playerNameType = gettype($playerName);
		if($playerNameType !== "string" || empty($playerName)) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$playerName must be a non-empty string (\"$playerNameType\" given)", E_USER_ERROR);
			return null;
		}
		unset($playerNameType);
		// Creating or retrieving session
		if(!SmiteAPIHelper::createSessionIfNecessary()) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Session creation failed.", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("getmatchhistory")) === null) return false;
		$url = "getmatchhistory".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".SmiteAPIHelper::$_sessionId."/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis"))."/".$playerName;
		return SmiteAPIHelper::executeRequest($url);
	}
	
	/*
	 * Send a getqueuestats request
	 * 
	 * $playerName: string containing a player name
	 * $queue: integer containing a valid queue Id
	 * 
	 * Returns the web-service response or null if an error occurred
	 */
	public static function getQueueStats($playerName, $queue = 426) {
		// Checking parameter
		$errors = 0;
		$playerNameType = gettype($playerName);
		// Checking playerName
		if($playerNameType !== "string" || empty($playerName)) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$playerName must be a non-empty string (\"$playerNameType\" given)", E_USER_ERROR);
			$errors++;
		}
		// Checking queue
		if(!in_array($queue, Array(423, 424, 426, 427, 429, 430, 431, 433, 435, 438, 439))) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$queue must be a valid queueId (423, 424, 426, 427, 429, 430, 431, 433, 435, 438, 439) (\"".htmlspecialchars($queue)."\" given). Read Official Smite API Developer Guide for more information", E_USER_ERROR);
			$errors++;
		}
		// Exit the function if some parameters are invalid
		if($errors) return false;
		unset($playerNameType);
		// Creating or retrieving session
		if(!SmiteAPIHelper::createSessionIfNecessary()) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Session creation failed.", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("getqueuestats")) === null) return false;
		return SmiteAPIHelper::executeRequest("getqueuestats".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".SmiteAPIHelper::$_sessionId."/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis"))."/".$playerName."/".$queue);
	}
	
	/*
	 * Send a gettopranked request
	 * 
	 * Returns the web-service response or null if an error occurred
	 */
	public static function getTopRanked() {
		// Creating or retrieving session
		if(!SmiteAPIHelper::createSessionIfNecessary()) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Session creation failed.", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("gettopranked")) === null) return false;
		return SmiteAPIHelper::executeRequest("gettopranked".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".SmiteAPIHelper::$_sessionId."/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis")));
	}
	
	/*
	 * Send a getdataused request
	 * 
	 * Returns the web-service response or null if an error occurred
	 */
	public static function getDataUsed() {
		// Creating or retrieving session
		if(!SmiteAPIHelper::createSessionIfNecessary()) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Session creation failed.", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("gettopranked")) === null) return false;
		return SmiteAPIHelper::executeRequest("gettopranked".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".SmiteAPIHelper::$_sessionId."/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis")));
	}
	
	public static function getGods($lang = 1) {
		// Checking parameter
		if(!in_array($lang, Array(1, 3))) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") \$lang must be an integer (1 for English or 3 for French) (\"".htmlspecialchars($lang)."\" given). Assuming 1 by default", E_USER_WARNING);
			$lang = 1;
		}
		// Creating or retrieving session
		if(!SmiteAPIHelper::createSessionIfNecessary()) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Session creation failed.", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("getgods")) === null) return false;
		return SmiteAPIHelper::executeRequest("getgods".SmiteAPIHelper::$_format."/".SmiteAPIHelper::$_devId."/$signature/".SmiteAPIHelper::$_sessionId."/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis"))."/".$lang);
	}
	
	/*
	 * Send a ping request
	 * 
	 * Returns the web-service response or null if an error occurred
	 */
	public static function ping() {
		return SmiteAPIHelper::executeRequest("ping".SmiteAPIHelper::$_format);
	}
	
	/*
	 * Executes a request using cUrl library
	 * 
	 * $url: the Smite API URL to call (without http://api.smitegame.com/smiteapi.svc/)
	 * 
	 * Returns the result of the cUrl execution (JSON or XML string)
	 */
	private static function executeRequest($url) {
		$ch = curl_init("http://api.smitegame.com/smiteapi.svc/".$url);
		curl_setopt_array($ch, Array(
			CURLOPT_TIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HEADER => 0,
			CURLOPT_HTTPHEADER => array('Content-type: '.(SmiteAPIHelper::$_format === SmiteAPIHelper::SMITE_API_FORMAT_JSON ? 'application/json' : 'application/xml'))
		));
		ob_start();
		return curl_exec ($ch); // execute the curl command
		ob_end_clean();
		curl_close ($ch);
		unset($ch);
	}
	
	/*
	 * Creates a signature for a query
	 * 
	 * $methodName: string containing the name of the method that will be called for the query
	 * 
	 * Returns the signature as a string, or null if the signature couldn't be computed
	 */
	private static function getSignature($methodName) {
		// Checking devId, authKey and methodName
		if(!(SmiteAPIHelper::$_devId && SmiteAPIHelper::$_authKey && is_string($methodName)))
			return null;
		// Returning the signature
		return md5(SmiteAPIHelper::$_devId.$methodName.SmiteAPIHelper::$_authKey.((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis")));
	}
	
	/*
	 * Creates a session using the devId and the authKey set
	 * and sets the sessionId retrieved if the session was successfully created
	 * 
	 * $force (optional): Force the regeneration of the session even if a valid session already exists
	 * 
	 * Returns a boolean indicating whether a valid session is now active
	 */
	private static function createSessionIfNecessary($force = false) {
		// Checking if a valid session already exists
		if(!$force && SmiteAPIHelper::isSessionValid()) return true;
		// Checking the credentials
		if(!(SmiteAPIHelper::$_devId && SmiteAPIHelper::$_authKey)) {
			trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") Smite API Credentials must be set before any request", E_USER_ERROR);
			return null;
		}
		// Checking if a signature could be created
		if(($signature = SmiteAPIHelper::getSignature("createsession")) === null) return false;
		try {
			// Forcing JSON for internal purpose
			$url = "createsessionjson/".SmiteAPIHelper::$_devId."/$signature/".((new DateTime("now", new DateTimeZone("UTC")))->format("YmdHis"));
			$response = SmiteAPIHelper::executeRequest($url);
			// Retrieve JSON data (JSON is GREAT)
			// {"ret_msg":"[string]","session_id":"[hex number]","timestamp":"[bad-formatted timesatmp]"}
//			if(SmiteAPIHelper::$_format === SmiteAPIHelper::SMITE_API_FORMAT_JSON) {
			if(true) { // Don't mind that
				$result = json_decode($response, true);
				if(array_key_exists("ret_msg", $result))
				{
					if($result["ret_msg"] === "Approved" && array_key_exists("session_id", $result) && !empty($result["session_id"]) && array_key_exists("timestamp", $result) && !empty($result["timestamp"])) {
						SmiteAPIHelper::$_sessionId = $result["session_id"];
						SmiteAPIHelper::$_sessionTimeout = SmiteAPIHelper::generateTimeoutFromOddTimestamp($result["timestamp"]);
						$status = true;
					}
				}
				else
					trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") createsession web-service didn't return \"ret_msg\" attribute");
			}
			// Retrieve XML data (XML is EVIL, quit using that sh*t!)
			// <Session><ret_msg>[string]</ret_msg><session_id>[hex number]</session_id><timestamp>[bad-formatted timestamp]</timestamp></Session>
//			else {
//				$indexes = Array();
//				if(!(SmiteAPIHelper::parseXMLkthxbye($response, $result, $indexes)))
//					trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") createsession web-service didn't return a valid XML file");
//				else if(array_key_exists("ret_msg", $indexes))
//				{
//					$test = [
//						$result[$indexes["ret_msg"][0]],
//						$result[$indexes["session_id"][0]],
//						$result[$indexes["timestamp"][0]]
//					];
//					if(
//						($result[$indexes["ret_msg"][0]]["type"] === "complete" && $result[$indexes["ret_msg"][0]]["value"] === "Approved")
//						&& ($result[$indexes["session_id"][0]]["type"] === "complete" && $result[$indexes["session_id"][0]]["value"])
//						&& ($result[$indexes["timestamp"][0]]["type"] === "complete" && $result[$indexes["timestamp"][0]]["value"])
//					) {
//						SmiteAPIHelper::$_sessionId = $result[$indexes["session_id"][0]]["value"];
//						SmiteAPIHelper::$_sessionTimeout = SmiteAPIHelper::generateTimeoutFromOddTimestamp($result[$indexes["timestamp"][0]]["value"]);
//						$status = true;
//					}
//				}
//				else
//					trigger_error (__CLASS__."::".__FUNCTION__." (".__LINE__.") createsession web-service didn't return \"ret_msg\" attribute");
//			}
		}
		catch(Exception $e) {
			trigger_error ($e->getFile()." (".$e->getLine().") Call to createsession web-service threw an Exception: (#".$e->getCode().") ".$e->getMessage());
		}
		return isset($status) && $status === true;
	}
	
	/*
	 * XMLParser Helper 'cause I hate copy/pasting sh*tloads of code
	 * Useless as long as createSessionIfNecessary forces JSON request
	 * 
	 * $xml: string containing XML data
	 * 
	 * Returns the data formatted as an array
	 */
//	private static function parseXMLkthxbye($xml, &$result, &$indexes = Array()) {
//		$xmlParser = xml_parser_create("UTF-8");
//		xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "UTF-8");
//		xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, 0);
//		xml_parser_set_option($xmlParser, XML_OPTION_SKIP_WHITE, 1);
//		$ok = xml_parse_into_struct($xmlParser, trim($xml), $result, $indexes) === 1;
//		ob_start();
//		return $ok;
//		ob_end_clean();
//		xml_parser_free($xmlParser);
//		unset($xmlParser);
//	}
	
	/*
	 * Timestamp converter Helper 'cause the timestamp returned by the web-services
	 * is just... odd
	 * 
	 * $str: string containing the timestamp
	 * 
	 * Returns a UNIX timestamp
	 */
	private static function generateTimeoutFromOddTimestamp($str) {
		return DateTime::createFromFormat("n/j/Y g:i:s A", $str, new DateTimeZone("UTC"))->modify('+14 minute');
	}
	
	/*
	 * Checks whether the active session is valid, by checking the id
	 * and the validity of the timeout
	 * 
	 * Returns a boolean indicating whether the session is valid or not
	 */
	private static function isSessionValid() {
		return SmiteAPIHelper::$_sessionId && SmiteAPIHelper::$_sessionTimeout && SmiteAPIHelper::$_sessionTimeout > new DateTime("now", new DateTimeZone("UTC"));
	}
}

?>
