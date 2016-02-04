<?php

/*
 * Example file doing an API request using the cache system on session
 * A cache file named "session_cache.txt" will be created and used
 * to load a Smite API session.
 * This will allow you to generate strictly one session for every page visited
 * during 15 minutes
 */


// Including SmiteAPIHelper and Session
require_once("Classes/SmiteAPISession.php");
require_once("Classes/SmiteAPIHelper.php");


//
// SMITEAPIHELPER INITIALIZATION
//


SmiteAPIHelper::setCredentials(<DEV_ID>, <AUTH_KEY>); //Personal developer credentials
SmiteAPIHelper::$_format = SmiteAPIHelper::SMITE_API_FORMAT_JSON;


//
// SET SESSION SYSTEM (XBOX/PC)
//

$xbox = SmiteAPIHelper::setSystem("XBOX"); 

//
// SESSION MANAGEMENT WITH CACHING SYSTEM
//


$session = new SmiteAPISession();
$sessionLoadState = $sessionSaveState = false;

// Cache seperate sessions for each system
if ($xbox) {
	$cacheFile = "session_cache_xbox.txt";
} else {
	$cacheFile = "session_cache.txt";
}

// Loading a session from session_cache.txt ONLY if it's valid
if($session->loadFromCache($cacheFile) === SmiteAPISession::SESSION_STATE_VALID) {
	$sessionLoadState = true;
	SmiteAPIHelper::setSession ($session);
}


//
// SMITE API CALLS
//

		
$r = SmiteAPIHelper::getQueueStats("An%20Ethnic%20Miner");


//
// SAVING SESSION INTO CACHE FOR NEXT CALLS
//


/*
 * Doing this even if you loaded a session frome the cache file ensures
 * that the last used session will be recorded
 */
$sessionSaveState = SmiteAPIHelper::getSession()->saveToCache($cacheFile);

?>

<!doctype html>
<html>
	<head>
		<title>SmiteAPIHelper 1.0 - Sample file</title>
	</head>
	<body>
		This file is an example usage of SmiteAPIHelper, including a caching example.<br>
		The very first time you use it, the cache is empty and then can't be loaded. All the following times, the cache should be successfully loaded as long as the cached session is not timed out.
		<hr>
		Cache loading: <?php echo $sessionLoadState ? "<span style=\"color: green;\">Cached session successfully loaded</span>" : "<span style=\"color: red;\">Cached session couldn't be loaded</span>"; ?><br>
		Cache saving: <?php echo $sessionSaveState ? "<span style=\"color: green;\">Session successfully saved into cache</span>" : "<span style=\"color: red;\">Session couldn't be saved into cache</span>"; ?><br>
		<hr>
		The result of the request should be a JSON containing the queue stats for the player &laquo;&nbsp;An Ethnic Miner&nbsp;&raquo; on Conquest mode.<br>
		<textarea readonly cols="120" rows="30"><?php echo htmlspecialchars($r); ?></textarea>
	</body>
</html>