<?php

/*
 * Example file doing an API request without using the cache system on session
 * Each time a user will call the webpage, a new session id will be created,
 * and will be used to execute all the queries in the webpage
 */


// Including SmiteAPIHelper
require_once("Classes/SmiteAPIHelper.php");


//
// SMITEAPIHELPER INITIALIZATION
//


SmiteAPIHelper::setCredentials(<DEV_ID>, <AUTH_KEY>); // TODO Set your credentials here
SmiteAPIHelper::$_format = SmiteAPIHelper::SMITE_API_FORMAT_JSON;


//
// SMITE API CALLS
//


$r = SmiteAPIHelper::getQueueStats("Chorizorro");

?>

<!doctype html>
<html>
	<head>
		<title>SmiteAPIHelper 1.0 - Sample file</title>
	</head>
	<body>
		This file is an example usage of SmiteAPIHelper, without caching the session.
		<hr>
		The result of the request should be a JSON containing the queue stats for the player &laquo;&nbsp;Chorizorro&nbsp;&raquo; on Conquest mode.<br>
		<textarea readonly cols="120" rows="30"><?php echo htmlspecialchars($r); ?></textarea>
	</body>
</html>