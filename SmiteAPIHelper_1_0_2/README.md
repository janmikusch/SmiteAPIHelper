#SmiteAPIHelper#


_Documentation for SmiteAPIHelper version 1.0.2_
_by Chorizorro - 2013/06/15_

_Updated by Kamal Osman - 2016/02/04_


##What is SmiteAPIHelper?##

__SmiteAPIHelper__ is a __PHP file__ developed to facilitate the manipulation of the Smite API for Developers.
To work properly, this PHP module requires:
- PHP __5.2.0 or higher__
- The PHP extension __cUrl__
And that's all!

This little project is absolutely free of use, provided with no warranty of any kind. You may use it, reproduce it, modify it, do whatever you want with it.

 
##Why should I use it?##

Because managing sessions to access the Smite API can sometimes be a pain in the *ss.
Because formatting requests URL is boring.

__SmiteAPIHelper automatically manages session creation__, and regeneration when they are timed out. Moreover, it allows you to cache a session in a file (as a JSON formatted string), so that you can create and use only one session every 15 minutes.
It also provides __automatic signature generation__, based on your credentials and the opened session.
Finally, it makes calling Smite API web-services a lot simpler by providing one function for each available web-service.


##How do I use it?##

To be able to use SmiteAPIHelper, you need to include it into your PHP project, at any place you want.
You will have to include the file wherever you need it in your PHP code using the _require_once_ function:

    require_once("<PATH_TO_FILE>/SmiteAPIHelper.php");
  
###Module Initialization###
	
The module needs to be __initialized with your Smite API Credentials__. To do so, call the _setCredentials_ function providing your devId as the first parameter, and your authKey as the second one:

	SmiteAPIHelper::setCredentials(<DEV_ID>, <AUTH_KEY>);
	
By default, you will be sending requests for XML files. You can change this setting at any time by fixing the public property $_format to one of the following values:
	
	// XML (default)
	SmiteAPIHelper::$_format = SmiteAPIHelper::SMITE_API_FORMAT_XML;
	// equivalent to
	SmiteAPIHelper::$_format = "xml";
	
	// JSON
	SmiteAPIHelper::$_format = SmiteAPIHelper::SMITE_API_FORMAT_JSON;
	// equivalent to
	SmiteAPIHelper::$_format = "json";

You will also by default be accessing the PC smite API. This can be changed by calling the setSystem method with "XBOX", it can also be set back by calling it with "PC". This function will return a boolean of true if the system is set to xbox and false if it is set to PC.
	
	//Xbox
	$xbox = SmiteAPIHelper::setSystem("XBOX"); 
	
	//Set Back to PC
	$xbox = SmiteAPIHelper::setSystem("PC"); 
	
Then, you can start using the provided functions to make requests to the Smite API.

###Session managing and caching###

To __optimize session use__, the API comes with a cache system on sessions. The cache can be used in _SmiteAPISession_ objetcts.
It includes two basic functions: _loadFromCache_ and _saveToCache_, taking one optional parameter which is the path to the cache file.

Here is how you can load a cached session:

	require_once("SmiteAPISession.php"); // We need this to manipulate Sessions
	
	$session = new SmiteAPISession();
	// Loading a session from session_cache.txt
	$session->loadFromCache("session_cache.txt");
	SmiteAPIHelper::setSession($session);

You may want to set the session only if it's still valid (not outdated). Change the two last lines by:

	// The loading function returns a SESSION_STATE integer, allowing you to check the validity of the session
	if($session->loadFromCache("session_cache.txt") === SmiteAPISession::SESSION_STATE_VALID)
		SmiteAPIHelper::setSession($session);

The same principle applies for session saving, as you can see in the following piece of code

	SmiteAPIHelper::getSession()->saveToCache($cacheFile);

The _saveToCache_ function returns a boolean indicating if the session was successfully cached, allowing you to handle error cases.

__A Note on Multiple System Sessions:__ The Smite API requires you have seperate sessions for Xbox and PC, so if you will be switching access between the two systems you should consider storing the sessions in separate files. This will stop sessions from apearing invalid early and being replaced and will reduce the creation of new sessions. Here is an Example:

	// Cache seperate sessions for each system
	if ($xbox) {
		$cacheFile = "session_cache_xbox.txt";
	} else {
		$cacheFile = "session_cache.txt";
	}

_Additional Note_: your cache file should be only readable by the server (chmod 0400) for security purposes.



This whole system was made to simplify the sessions management, and should be all what you need in order to send requests without being bothered by sessions again.
Anyway, if you still want to manage sessions by yourself, you can still call the function _createSession_ that will execute a createsession request to the Smite API without automatically setting up the session.
	
	// Only make a createsession request without managing session, and returns the JSON or XML returned by the server
	SmiteAPIHelper::createSession();

###Take a look at the samples...###

The folder "Sample" contains two example files:

1.	Cache.php provides an example of use __with cached sessions__
2.	NoCache.php provides an example of use __witout session caching__

Don't forget to set your credentials on the following lines:

	SmiteAPIHelper::setCredentials(<DEV_ID>, <AUTH_KEY>); // TODO Set your credentials here


##Detailed API##

The module is composed of two classes:

1.	__SmiteAPIHelper__, an abstract class defining a set of __static methods and properties__.
2.	__SmiteAPISession__, a class for __session management__ and load/save into a cache

###SmiteAPIHelper###
	
####Class constants####

	const SMITE_API_FORMAT_XML = "xml";
	const SMITE_API_FORMAT_JSON = "json";

####Public properties####

	static $_format = "xml";
	
####Public methods####
	
	// Session getter and setter (using SmiteAPISession objects only)
	static function getSession();
	static function setSession($session);
	
	// Setter for credentials. Must be called to initialize the module
	static function setCredentials($devId, $authKey);
	
	// Calls the createSession Smite API request for manual session managing
	static function createSession();
	
	// Smite API requests with parameters depending on each request
	static function getItems($lang = 1); // getitems Smite API request
	static function getPlayer($lang); // getplayer Smite API request
	static function getMatchDetails($mapId); // getmatchdetails Smite API request
	static function getMatchHistory($playerName); // getmatchhistory Smite API request
	static function getQueueStats($playerName, $queue = 426); // getqueuestats Smite API request
	static function getTopRanked(); // gettopranked Smite API request
	static function getDataUsed(); // getdataused Smite API request
	static function getGods($lang = 1); // getitems Smite API request
	static function ping(); // ping Smite API request

###SmiteAPISession###
	
####Definition####

	implements JsonSerializable
	
####Class constants####

	const SESSION_STATE_UNSET = 0;
	const SESSION_STATE_VALID = 1;
	const SESSION_STATE_TIMEDOUT = 2;

####Public properties####

	$_cacheFile = null; // If you want to associated a session with a precise file

####Private properties accessible through getter####
	
	$state = SmiteAPISession::SESSION_STATE_UNSET;
	$id = null;
	$timeout = null;
	
####Public methods####

	// Base constructor
	function __construct($id = null, $timeout = null);
	
	// Getter for private properties
	function __get($name);

	// Cache management functions
	function loadFromCache($filename = null);
	function saveToCache($filename = null);

	// JsonSerializable method to return custom object when the object is passed through json_encode
	function jsonSerialize();

	
##Finally...##

I hope this simple (and stupid) module will help PHP developers in dealing with the Smite API requests.
