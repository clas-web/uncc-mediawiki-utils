<?php
echo "\n";

/**
 * Displays the script's help description.
 */
function display_help()
{
	echo "php init-repo.php --all\n";
	echo "php init-repo.php --repo:REPO [--repo:REPO]\n";
	echo "\n";
	echo " --all\n";
	echo "      Initialize all repos.\n";
	echo "\n";
	echo " --repo:REPO\n";
	echo "      Initialize specified repo.\n";
	echo "      May be included multiple times with differnet names.\n";
	echo "\n";
	echo " --skipclone\n";
	echo "      Skips cloning the repositories and just recreates the\n";
	echo "      master source folder.\n";
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
require_once( dirname(__DIR__).'/config/repos.php' );


//----------------------------------------------------------------------------------------
// Process arguments
//----------------------------------------------------------------------------------------
$args = process_args( array_slice($argv, 1), array('repo'), array('all','skipclone','help') );

$repos_to_init = array();
$init_all = false;
$skip_clone = false;
$show_help = false;
$errors = false;

foreach( $args as $arg )
{
	switch( $arg['key'] )
	{
		case 'repo':
			$repos_to_init[] = $arg['value'];
			break;
		case 'all':
			$init_all = true;
			break;
		case 'skipclone':
			$skip_clone = true;
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

if( !$skip_clone && !$init_all && count($repos_to_init) === 0 )
{
	echo "ERROR: No repos specified.\n";
	echo "\n";
	display_help();
}


//----------------------------------------------------------------------------------------
// Get repos configuration settings.
//----------------------------------------------------------------------------------------
$all_repos = array_merge( array( 'mediawiki' ), array_keys($repos) );
if( $init_all ) $repos_to_init = $all_repos;
if( $skip_clone ) $repos_to_init = array();


//----------------------------------------------------------------------------------------
// Check for invalid repos.
//----------------------------------------------------------------------------------------
foreach( $repos_to_init as $repo_name )
{
	if( $repo_name == 'mediawiki' ) continue;
	
	if( !in_array($repo_name, array_keys($repos)) )
	{
		echo "ERROR: Invalid repo '$repo_name'\n";
		$errors = true;
	}
}

if( $errors ) { echo "\n"; exit; }


//----------------------------------------------------------------------------------------
// Verify with the user.
//----------------------------------------------------------------------------------------
if( !$skip_clone )
{
	echo "The following repositories will be initialized:\n";
	foreach( $repos_to_init as $repo_name )
	{
		echo "    $repo_name\n";
	}
}
else
{
	echo "No repositories will be initialized.\n";
	echo "The master source folder will be recreated.\n";
}
echo "\n";
echo 'Do you want to continue (yes)? ';

$handle = fopen( "php://stdin","r" );
$line = fgets($handle);
if( strtolower(trim($line)) !== 'yes' )
{
    echo "\n";
    echo "Aborting initialization.\n";
    echo "\n";
    exit;
}

echo "\n";


//----------------------------------------------------------------------------------------
// Create fresh copies of selected repositories.
//----------------------------------------------------------------------------------------
foreach( $repos_to_init as $repo_name )
{
	echo "Initializing repo '$repo_name' to repos source folder...";
	
	if( $repo_name == 'mediawiki' )
		extract( $mediawiki_repo );
	else
		extract( $repos[$repo_name] );
		
	$folder = $repos_path.'/'.$folder;
	
	// delete repository's old folder.
	remove_directory( $folder );
	if( is_dir($folder) )
	{
		echo "error.\n";
		echo "Unable to delete folder.\n";
		echo "   $folder\n";
		echo "\n";
		exit;
	}
	
	if( !@mkdir($folder) )
	{
		echo "error.\n";
		echo "Unable to create repo folder.\n";
		echo "   $folder\n";
		echo "\n";
		exit;
	}
	
	// clone repository then delete git files and folders.
	exec( "git clone --quiet --depth 1 $git '$folder'" );
	if( $branch !== 'master' )
	{
		exec( "cd $folder; git checkout $branch; cd $utils_path/scripts" );
	}
	delete_with_wildcard( "$folder/.git*" );

	echo "done\n";
}


//----------------------------------------------------------------------------------------
// Create master source folder.
//----------------------------------------------------------------------------------------
echo 'Clearing out master source folder...';

remove_directory( $master_path );
if( is_dir($master_path) )
{
	echo "error.\n";
	echo "Unable to delete folder.\n";
	echo "   $master_path\n";
	echo "\n";
	exit;
}

if( !@mkdir($master_path) )
{
	echo "error.\n";
	echo "Unable to create master folder.\n";
	echo "   $master_path\n";
	echo "\n";
	exit;
}

echo "done.\n";


foreach( $all_repos as $repo_name )
{
	echo "Copying repo '$repo_name' to master source folder...";
	
	if( $repo_name == 'mediawiki' )
		extract( $mediawiki_repo );
	else
		extract( $repos[$repo_name] );
		
	$folder = $repos_path.'/'.$folder;
	$location = $master_path.'/'.$location;
	
	// delete repository's old folder.
	if( !is_dir($folder) )
	{
		echo "error.\n";
		echo "Unable to find folder.\n";
		echo "   $folder\n";
		echo "Try re-running script with --wiki:$repo_name argument.\n";
		echo "\n";
		exit;
	}
	
	if( !@is_dir($location) )
	{
		if( !mkdir($location) || !is_dir($location) )
		{
			echo "error.\n";
			echo "Unable to create location folder.\n";
			echo "   $location\n";
			continue;
		}
	}
	
	exec( "cp -rf $folder/* '$location'" );

	echo "done\n";
}


touch( $master_path.'/.source.master' );


