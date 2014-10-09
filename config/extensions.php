<?php


/** Detect if currently using command line **/

if( php_sapi_name() == 'cli' ):

	$GLOBALS['wgCommandLineMode'] = true;

endif;


/** LDAP Authentication **/

if( getenv("DISABLE_LDAP") !== '1' ):

require_once "$IP/extensions/LdapAuthentication/LdapAuthentication.php";

$wgLDAPDebug = 1;
$wgDebugLogGroups['ldap'] = __DIR__."/logs/ldap-$wgSiteKey.log";

$wgLDAPDomainNames = array( 'UNC Charlotte' );
$wgLDAPServerNames = array( 'UNC Charlotte' => 'its.uncc.edu' );
$wgLDAPUseLocal = true;
$wgLDAPEncryptionType = array( 'UNC Charlotte' => 'ssl' );
$wgLDAPPort = array( 'UNC Charlotte' => 636 );
$wgLDAPOptions = array( 'UNC Charlotte' => array( 'LDAP_OPT_DEREF' => LDAP_DEREF_NEVER ) );
$wgLDAPProxyAgent = array( 'UNC Charlotte' => $wgLDAPusername );
$wgLDAPProxyAgentPassword = array( 'UNC Charlotte' => $wgLDAPpassword );
$wgLDAPSearchAttributes = array( 'UNC Charlotte' => 'sAMAccountName' );
$wgLDAPBaseDNs = array( 'UNC Charlotte' => 'OU=People,OU=unccharlotte,DC=its,DC=uncc,DC=edu' );
$wgLDAPGroupUseFullDN = array( 'UNC Charlotte' => true );
$wgLDAPGroupObjectclass = array( 'UNC Charlotte' => 'group' );
$wgLDAPGroupAttribute = array( 'UNC Charlotte' => 'memberOf' );
$wgLDAPGroupSearchNestedGroups = array( 'UNC Charlotte' => true );
$wgLDAPGroupNameAttribute = array( 'UNC Charlotte' => 'cn' );
$wgLDAPActiveDirectory = array( 'UNC Charlotte' => true );
$wgLDAPSearchStrings = array( 'UNC Charlotte' => 'USER-NAME@uncc.edu' );
$wgLDAPPreferences = array( 'UNC Charlotte' => array( 'email' => 'mail') );

$wgAuth = new LdapAuthenticationPlugin();

endif;


/** UNC Charlotte skin **/

require_once( "$IP/skins/unc-charlotte/unc-charlotte.php" );


/** Disable skin selection **/

$wgHiddenPrefs[] = 'skin';


/** Debugging **/

// $wgShowExceptionDetails = true;
// error_reporting( -1 );
// ini_set( 'display_errors', 1 );
// $wgShowSQLErrors = true;
// $wgDebugDumpSql  = true;
// $wgShowDBErrorBacktrace = true;
// $wgShowDebug = true;
// $wgDebugLogFile = dirname(__DIR__)."/logs/debug-$wgSiteKey.log";

