#SmiteAPIHelper#


_Changelog for SmiteAPIHelper_
_by Chorizorro - 2013/06/15_
_Updated by Kamal Osman - 2016/02/04_

##What is SmiteAPIHelper?##

__SmiteAPIHelper__ is a __PHP file__ developed to facilitate the manipulation of the Smite API for Developers.
This little project is absolutely free of use, provided with no warranty of any kind. You may use it, reproduce it, modify it, do whatever you want with it.

For further information on SmiteAPIHelper, how it works and how to use it in your PHP projects, pleser refer to the README file inside a version folder.

 
##Changelog##

###Version 1.0.2 - 2016/02/04###

New function, setSystem added to allow users access the xbox smite api with the same helper functions. A new sample file was 
also added to represent how this functionality would be used.

###Version 1.0.1 - 2013/06/15###

The use of an interface (JsonSerializable) in the session maganement made the module incompatible with PHP versions < 5.4.
This was fixed to make SmiteAPIHelper compatible with versions from 5.2.0, as intended.


###Version 1.0 - 2013/01/12###

First version!
Including helper functions for each Smite API request:

1.	createsession
2.	getitems
3.	getplayer
4.	getmatchdetails
5.	getmatchhistory
6.	getqueuestats
7.	gettopranked
8.	getdataused
9.	getgods
10.	ping

Automatic session management and caching, and easy-to-set-credentials and preferred response format.


##About the Author##

I'm [Chorizorro](http://account.hirezstudios.com/smitegame/stats.aspx?player=Chorizorro "Chorizorro's player profile"), I've been playing Smite casually for 7 months.

I'm also the creator of [Smitroll](http://smitroll.com "Visit Smitroll"), a site that simply generates random builds (random god, random skills, random items and abilities), to play fun games.
The SmiteAPIHelper is a module I developed for Smitroll, and that I modified to make it a bit more generic and usable by any developer creating a PHP site using access to the Smite API.