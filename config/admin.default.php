<?php

/** Server name (URL address) **/

$server_name = 'http://wiki.uncc.edu';

/** Path to the necessary folders **/

$utils_path = dirname(__DIR__);
$source_path = $utils_path.'/source';
$master_path = $source_path.'/master';
$repos_path = $source_path.'/repos';

/** Path to HTML source **/

$html_path = dirname($utils_path).'/public_html';
$html_relative_path = '/'; // must begin and end with a forward slash [/].

/** MediaWiki database config settings **/
/** User should have FULL access. **/

$dbType = 'mysql';
$dbServer = 'localhost';
$dbName = 'name';
$dbUser = 'username';
$dbPassword = 'password';


/** Wiki's admin account default username and password. **/

$wikiUsername = 'username';
$wikiPassword = 'password';

