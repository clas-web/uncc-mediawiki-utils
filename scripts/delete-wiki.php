<?php
echo "\n";

/**
 * Displays the script's help description.
 */
function display_help()
{
	echo "php delete-wiki.php --all\n";
	echo "php delete-wiki.php --wiki:WIKI [--wiki:WIKI]\n";
	echo "\n";
	echo " --all\n";
	echo "      Delete all wikis.\n";
	echo "\n";
	echo " --wiki:WIKI\n";
	echo "      Delete specified wiki.\n";
	echo "      May be included multiple times with differnet wikis.\n";
	echo "\n";
	exit;
}



/** MAIN SCRIPT **/

//----------------------------------------------------------------------------------------
// Get functions and admin configuration settings.
//----------------------------------------------------------------------------------------
require_once( dirname(__DIR__).'/scripts/functions.php' );
check_required_files();
require_once( dirname(__DIR__).'/config/admin.php' );


//----------------------------------------------------------------------------------------
// Process arguments
//----------------------------------------------------------------------------------------
$args = process_args( array_slice($argv, 1), array('wiki'), array('all','help') );

$wikis_to_delete = array();
$delete_all = false;
$show_help = false;
$errors = false;

foreach( $args as $arg )
{
	switch( $arg['key'] )
	{
		case 'wiki':
			$wikis_to_update[] = $arg['value'];
			break;
		case 'all':
			$update_all = true;
			break;
		case 'help':
			$show_help = true;
			break;
		default:
			echo 'Invalid argument key: '.$arg['key']."\n";
			$errors = true;
			break;
	}
}

if( $show_help ) { display_help(); }
if( $errors ) { echo "\n"; display_help(); }

if( !$delete_all && count($wikis_to_delete) === 0 )
{
	echo "ERROR: No wikis specified.\n";
	echo "\n";
	display_help();
}


//----------------------------------------------------------------------------------------
// Get list of all wikis.
//----------------------------------------------------------------------------------------
$all_wikis = array();
foreach( glob($html_path.$html_relative_path.'*/LocalSettings.php') as $file )
{
	if( strrpos($file, '/.') === strlen($file)-2 )  continue;
	if( strrpos($file, '/..') === strlen($file)-3 ) continue;
	
	$wiki_path = dirname($file);
	$wiki_name = basename($wiki_path);
	$all_wikis[$wiki_name] = $wiki_path;
}


//----------------------------------------------------------------------------------------
// Validate selected wikis.
//----------------------------------------------------------------------------------------
if( $delete_all )
{
	$wikis_to_delete = array_keys( $all_wikis );
}
else
{
	foreach( $wikis_to_delete as $wiki_name )
	{
		if( !in_array($wiki_name, array_keys($all_wikis)) )
		{
			echo "ERROR: Invalid wiki '$wiki_name'.\n";
			echo "\n";
			$errors = true;
		}
	}
}

if( $errors ) { echo "\n"; display_help(); }


//----------------------------------------------------------------------------------------
// Verify with the user.
//----------------------------------------------------------------------------------------
echo "The following wikis will be deleted:\n";
foreach( $wikis_to_delete as $wiki_name )
{
	echo "    $wiki_name\n";
}
echo "\n";
echo 'Do you want to continue (yes)?';

$handle = fopen( "php://stdin","r" );
$line = fgets($handle);
if( strtolower(trim($line)) !== 'yes' )
{
    echo "\n";
    echo 'Aborting delete.';
    exit;
}

echo "\n";


//----------------------------------------------------------------------------------------
// Connect to database.
//----------------------------------------------------------------------------------------
echo "Connecting to database...";

$db_connection = @mysqli_connect( $dbServer, $dbUser, $dbPassword, $dbName );
if( mysqli_connect_errno() )
{
	echo "error.\n";
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	echo "\n";
	exit;
}

echo "done.\n";


//----------------------------------------------------------------------------------------
// Delete each specified wiki.
//----------------------------------------------------------------------------------------
foreach( $wikis_to_delete as $wiki_name )
{
	$wiki_path = $all_wikis[$wiki_name];

	echo "Deleting wiki '$wiki_name'...";
	
	$local_settings = file_get_contents( $wiki_path."/LocalSettings.php" );
	if( $local_settings === false )
	{
		echo "error.\n";
		echo "Unable read LocalSettings.php file: '$wiki_path/LocalSettings.php'.\n";
		echo "\n";
		exit;
	}
	
	$matches = array();
	if( preg_match( "/\$wgDBprefix = \"\[([^\"]+)\"/", $arg, $matches ) )
	{
		$db_prefix = $matches[1];
	}
	else
	{
		echo "error.\n";
		echo "Unable to parse database prefix.\n";
		echo "\n";
		exit;
	}
	
	// delete tables...
		
	remove_directory( $wiki_path );
	
	echo "done.\n";
}


