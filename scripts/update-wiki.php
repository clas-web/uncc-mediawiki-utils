<?php
echo "\n";

/**
 * Displays the script's help description.
 */
function display_help()
{
	echo "php update-wiki.php --all\n";
	echo "php update-wiki.php --wiki:WIKI [--wiki:WIKI]\n";
	echo "\n";
	echo " --all\n";
	echo "      Update all wikis.\n";
	echo "\n";
	echo " --wiki:WIKI\n";
	echo "      Update specified wiki.\n";
	echo "      May be included multiple times with differnet wikis.\n";
	echo "\n";
	echo " --skipsync\n";
	echo "      Skips syncing the source code and just runs the\n";
	echo "      maintenance script for each wiki.\n";
	echo "\n";
	echo " --skipscript\n";
	echo "      Skips running the maintenance update script.\n";
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
$args = process_args( array_slice($argv, 1), array('wiki'), array('all','skipsync','skipscript','help') );

$wikis_to_update = array();
$update_all = false;
$skip_sync = false;
$skip_script = false;
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
		case 'skipsync':
			$skip_sync = true;
			break;
		case 'skipscript':
			$skip_script = true;
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

if( !$update_all && count($wikis_to_update) === 0 )
{
	echo "ERROR: No wikis specified.\n";
	echo "\n";
	display_help();
}

if( $skip_sync && $skip_script )
{
	echo "ERROR: Both --skipsync and --skipscript are specified.\n";
	echo "The update will not continue.\n";
	echo "\n";
	exit;
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
if( $update_all )
{
	$wikis_to_update = array_keys( $all_wikis );
}
else
{
	foreach( $wikis_to_update as $wiki_name )
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
echo "The following wikis will be updated:\n";
foreach( $wikis_to_update as $wiki_name )
{
	echo "    $wiki_name\n";
}
echo "\n";

if( $skip_sync )
	echo "The wiki source code will NOT be updated.\n";
if( $skip_script )
	echo "The maintenance update script will NOT be run.\n";

echo "\n";
echo 'Do you want to continue (yes)?';

$handle = fopen( "php://stdin","r" );
$line = fgets($handle);
if( strtolower(trim($line)) !== 'yes' )
{
    echo "\n";
    echo 'Aborting update.';
    exit;
}

echo "\n";


//----------------------------------------------------------------------------------------
// Sync each wiki's source code and run the maintenance update script.
//----------------------------------------------------------------------------------------
foreach( $wikis_to_update as $wiki_name )
{
	$wiki_path = $all_wikis[$wiki_name];

	if( !$skip_sync )
	{
		echo "Updating wiki '$wiki_name' source...";
		exec( "rsync --quiet --delete --recursive --force --exclude=.source.master --exclude=LocalSettings.php --exclude=images --exclude=config '$master_path' '$wiki_path'" );	
		echo "done.\n";
	}
	
	if( !$skip_script )
	{
		echo "Running wiki '$wiki_name' update script...";
		exec( "php $wiki_path/maintenance/update.php --quick '--dbuser=$dbName' '--dbpass=$dbPassword' '--conf=$wiki_path/LocalSettings.php'" );
		echo "done.\n";
	}
}

