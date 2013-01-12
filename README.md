#SmiteAPIHelper#


_Documentation for SmiteAPIHelper version 1.0_
_by Chorizorro - 2013/01/12_


##What is SmiteAPIHelper?##

__SmiteAPIHelper__ is a __PHP file__ developed to facilitate the manipulation of the Smite API for Developers.
To work properly, this PHP module requires:
- PHP __5.2.0 or higher__
- The PHP extension __cUrl__
And that's all!

This little project is absolutely free of use, provided with no warranty of any kind. You may use it, reproduce it, modify it, do whatever you want with it.

 
##Why should I use it?##

Because managing sessions to access the Smite API can sometimes be a pain in the *ss.

__SmiteAPIHelper automatically manages session creation__, and regeneration when they are timed out. You don't have to bother anymore about checking it by yourself.
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
	SmiteAPIHelper::$_format = "xml";
	
	// JSON
	SmiteAPIHelper::$_format = SmiteAPIHelper::SMITE_API_FORMAT_JSON;
	SmiteAPIHelper::$_format = "json";
	
Then, you can start using the provided functions to make requests to the Smite API.

###Creating, saving and loading sessions###

The module has a basic session management, creating a new session per call to the server (i.e. every time a user loads a page...), which is not really great (and may cause problems with the Hi-Rez team).
To __optimize session use__, and not use a different session everytime, you will probably want to be able to handle the session a bit by yourself. How do you do that?

First, use the _createSession_ function (you must have set your credentials before calling that function!). It will return you the JSON or XML return string from the Smite API request, which you can save where you want.
Then, you can call _setSession_ passing the saved session_id as the first parameter, and the saved timestamp + 15 minutes in a __DateTime PHP Object__ as the second parameter.
Your code will look like this:

	SmiteAPIHelper::setCredentials(<DEV_ID>, <AUTH_KEY>); // Setting credentials before anything else!
	SmiteAPIHelper::$_format = "json"; // I will use JSON for this example
	// Retrieve the saved session ID and timestamp if they exist
	// $sessionId; $sessionTimestamp;
	$sessionSet = false;
	if(isset($sessionId) && isset($sessionTimestamp)) {
		/*
		 * Setting the session with:
		 * - the session ID
		 * - the session timeout computed from the session timestamp parsed to create a DateTime object in UTC TimeZone with up to 15 more minutes
		 */
		$sessionSet = SmiteAPIHelper::setSession($sessionId, DateTime::createFromFormat("n/j/Y g:i:s A", $sessionTimestamp, new DateTimeZone("UTC"))->modify('+14 minute'));
	}
	// If we hadn't retrieved a session or if the setSession method failed, we generate a new session
	if(!$sessionSet) {
		// Creating a session, retrieving and saving the result for the next time
		$r = SmiteAPIHelper::createSession(); // Execute a request to the Smite API createsession URL
		$json = json_decode($r, true); // Parsing the response string in an associative PHP Array
		if(!(array_key_exists("ret_msg", json) && $json["ret_msg"] === "Approved")) {
			// Woops! The session creation failed. Handle that case
		}
		else {
			// Retrieving the session ID and timestamp
			$sessionId = $json["session_id"];
			$sessionTimestamp = $json["timestamp"];
			// Save it wherever you want (a file, a database, ...)
		}
	}
	
As shown by the example above, __you don't have to bother about invalid sessions or outdated timestamp__: the session will not be saved and false will be returned, allowing you to generate a new sessions.
Please note that the _createSession_ method, unlike all the other requests methods and the _setSession_ method, doesn't automatically set the $_sessionId and $_sessionTimeout properties.

Finally, if you're lazy about using the _createSession_ and _setSession_ methods, you can call the _get_ method to __retrieve session ID and timeout after any request__ (excepted the _createSession_ one), like following:

	$sessionid = SmiteAPIHelper::get("sessionId");
	$sessionTimeout = SmiteAPIHelper::get("sessionTimeout");
	
Then, just save these variables wherever you want!


##Detailed API##

The SmiteAPIHelper is an __abstract class__ defining a set of __static methods and properties__.

###Class constants###

The two class constants are used to initialize the $_format property.

	const SMITE_API_FORMAT_XML = "xml";
	const SMITE_API_FORMAT_JSON = "json";

###Public properties###

	static $_format = "xml";
	
###Public methods###
	
	// Getter for sessionId and sessionTimeout properties
	static function get($key);
	
	// Setter for credentials. Must be called to initialize the module
	static function setCredentials($devId, $authKey);
	
	// Setter for a loaded Smite API session
	static function setSession($sessionId, $timeout);
	
	// Calls the getitems Smite API request
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

	
##Finally...##

I hope this simple (and stupid) module will help PHP developers in dealing with the Smite API requests.
