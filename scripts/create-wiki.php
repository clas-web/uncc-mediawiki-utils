<?php
echo "\n";

/**
 * Displays the script's help description.
 */
function display_help()
{
	echo "php create-wiki.php --name:NAME --title:TITLE\n";
	echo "\n";
	echo " --name:NAME\n";
	echo "      The folder name for the wiki site.\n";
	echo "\n";
	echo " --title:TITLE\n";
	echo "      The title for the wiki site.\n";
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
$args = process_args( array_slice($argv, 1), array('name','title'), array('help','force') );

$wiki_name = '';
$wiki_title = '';
$show_help = false;
$force_create = false;
$errors = false;

foreach( $args as $arg )
{
	switch( $arg['key'] )
	{
		case 'name':
			$wiki_name = $arg['value'];
			break;
		case 'title':
			$wiki_title = $arg['value'];
			break;
		case 'help':
			$show_help = true;
			break;
		case 'force':
			$force_create = true;
			break;
		default:
			echo 'Invalid argument key: '.$arg['key']."\n";
			$errors = true;
			break;
	}
}

if( $show_help ) { display_help(); }
if( $errors ) { echo "\n"; display_help(); }

if( !$wiki_name || !$wiki_title )
{
	echo "ERROR: Missing arguments.\n";
	echo "\n";
	display_help();
}


//----------------------------------------------------------------------------------------
// Create values based on args.
//----------------------------------------------------------------------------------------
$site_path = $html_path.$html_relative_path.$wiki_name;
$table_prefix = strtolower(generate_random_string(5)).'_';


//----------------------------------------------------------------------------------------
// Verify with the user.
//----------------------------------------------------------------------------------------
echo "Name: $wiki_name\n";
echo "Title: $wiki_title\n";
echo "\n";
echo "Installing '$wiki_title' into folder '$site_path'.\n";
echo "\n";
echo 'Do you want to continue (yes)?';

$handle = fopen( "php://stdin","r" );
$line = fgets($handle);
if( strtolower(trim($line)) !== 'yes' )
{
    echo "\n";
    echo 'Aborting wiki creation.';
    exit;
}

echo "\n";


//----------------------------------------------------------------------------------------
// Create site folder.
//----------------------------------------------------------------------------------------
echo "Creating site folder...";

if( @is_dir($site_path) )
{
	echo "error.\n";
	echo "Site folder already exists.\n";
	echo "   $site_path\n";
	echo "\n";
	exit;
}

if( !@mkdir($site_path) )
{
	echo "error.\n";
	echo "Unable to create site folder.\n";
	echo "   $site_path\n";
	echo "\n";
	exit;
}

echo "done.\n";


//----------------------------------------------------------------------------------------
// Create site database tables.
//----------------------------------------------------------------------------------------
echo "Create database tables...";

$tables_sql = @file_get_contents( "$master_path/maintenance/tables.sql" );

if( $tables_sql === false )
{
	echo "error.\n";
	echo "Unable to read tables.sql file.\n";
	echo "   $master_path/maintenance/tables.sql\n";
	echo "\n";
	exit;
}

$tables_sql = str_replace( "/*_*/", $table_prefix, $tables_sql );

if( @file_put_contents( "$utils_path/temp/$folder_name.sql", $tables_sql ) === false )
{
	echo "error.\n";
	echo "Unable to write tables.sql file.\n";
	echo "   $utils_path/temp/$wiki_name.sql\n";
	echo "\n";
	exit;
}

$db_connection = @mysqli_connect( $dbServer, $dbUser, $dbPassword, $dbName );
if( mysqli_connect_errno() )
{
	echo "error.\n";
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	echo "\n";
	exit;
}

if( !@mysqli_multi_query($db_connection, $tables_sql) )
{
	echo "error.\n";
	echo "Failed to execute tables SQL: " . mysqli_connect_error();
	echo "\n";
	exit;
}

echo "done.\n";


//----------------------------------------------------------------------------------------
// Create LocalSettings.php.
//----------------------------------------------------------------------------------------
echo "Creating LocalSettings.php...";

$template = file_get_contents( "$utils_path/LocalSettings.template.php" ); 
if( $template === false )
{
	echo "error.\n";
	echo "Unable to read LocalSetting template file.\n";
	echo "   $utils_path/LocalSettings.template.php\n";
	echo "\n";
	exit;
}

$template = str_replace( '[FOLDER-NAME]', $wiki_name, $template );
$template = str_replace( '[SITE-TITLE]', $wiki_title, $template );
$template = str_replace( '[SITE-NAME]', str_replace(' ', '_', $wiki_title), $template );
$template = str_replace( '[PREFIX]', $table_prefix, $template );
$template = str_replace( '[SECRET-KEY]', generate_random_string(64), $template );
$template = str_replace( '[MEDIAWIKI-FILES]', $utils_path, $template );
$template = str_replace( '[SERVER-NAME]', $server_name, $template );

file_put_contents( "$site_path/LocalSettings.php", $template );

echo "done.\n";


//----------------------------------------------------------------------------------------
// Copy MediaWiki files.
//----------------------------------------------------------------------------------------
echo "Copying MediaWiki files...";

exec( "rsync --quiet --delete --recursive --force --exclude=.source.master '$master_path' '$wiki_path'" );

echo "done.\n";


//----------------------------------------------------------------------------------------
// Create admin account.
//----------------------------------------------------------------------------------------
echo "Creating admin account...";

exec( "php $site_path/maintenance/createAndPromote.php --quick --force --bureaucrat --sysop '--conf=$site_path/LocalSettings.php' '$dbName' '$dbPassword'" );

echo "done.\n";

//----------------------------------------------------------------------------------------
// Run maintenance update script.
//----------------------------------------------------------------------------------------
echo "Running update script...";

exec( "php $site_path/maintenance/update.php --quick '--dbuser=$dbName' '--dbpass=$dbPassword' '--conf=$site_path/LocalSettings.php'" );

echo "done.\n";


/** END MAIN SCRIPT **/

