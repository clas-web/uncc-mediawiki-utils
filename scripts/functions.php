<?php



/**
 * 
 */
function check_required_files()
{
	$errors = false;
	$admin_file = dirname(__DIR__).'/config/admin.php';
	$mediawiki_file = dirname(__DIR__).'/config/mediawiki.php';
	
	if( !file_exists($admin_file) )
	{
		echo "ERROR: Unable to find admin config file: '$admin_file'\n";
		echo "Create file by duplicating admin.default.php.\n";
		echo "\n";
		$errors = true;
	}
	
	if( !file_exists($mediawiki_file) )
	{
		echo "ERROR: Unable to find mediawiki config file: '$mediawiki_file'\n";
		echo "Create file by duplicating mediawiki.default.php.\n";
		echo "\n";
		$errors = true;
	}
	
	if( $errors ) exit;
}



/**
 * 
 * @param  $args			array	
 *         
 * @param  $valid_keys		array	
 * @param  $valid_switches	array	
 * @return 					array	
 */
function process_args( $args, $valid_keys, $valid_switches )
{
	$invalid_args = array();
	$processed_args = array();
	
	foreach( $args as $arg )
	{
		$matches = array();
		if( preg_match( "/--([^:]+):(.+)/", $arg, $matches ) )
		{
			if( in_array($matches[1], $valid_keys) )
			{
				$processed_args[] = array(
					'key' => $matches[1],
					'value' => $matches[2],
				);
				continue;
			}
		}
		elseif( preg_match( "/--(.+)/", $arg, $matches ) )
		{
			if( in_array($matches[1], $valid_switches) )
			{
				$processed_args[] = array(
					'key' => $matches[1],
					'value' => null,
				);
				continue;
			}
		}

		$invalid_args[] = $arg;
	}
	
	if( count($invalid_args) > 0 )
	{
		foreach( $invalid_args as $arg )
		{
			echo "Invalid arg: $arg\n";
		}
		echo "\n";
		display_help();
	}
	
	return $processed_args;
}



/**
 * Generates a random string consisting of letters and numbers.
 * @param  $length  int     The length of the random string.
 * @return          string  The random string.
 */
function generate_random_string( $length )
{
	$key = '';

	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	for( $i = 0; $i < $length; $i++ )
	{
		$key .= $characters[rand(0, strlen($characters) - 1)];
	}
	
	return $key;
}



function remove_directory( $folder, $depth = 0 )
{
	if( !is_dir($folder) ) return;
	
	foreach( glob($folder . '/*') as $file )
	{
		if( strrpos($file, '/.') === strlen($file)-2 )  continue;
		if( strrpos($file, '/..') === strlen($file)-3 ) continue;
		
		if( is_dir($file) )
			remove_directory( $file, $depth+1 );
		else
			unlink( $file );
	}

	foreach( glob($folder . '/.*') as $file )
	{
		if( strrpos($file, '/.') === strlen($file)-2 )  continue;
		if( strrpos($file, '/..') === strlen($file)-3 ) continue;
		
		if( is_dir($file) )
			remove_directory( $file, $depth+1 );
		else
			unlink( $file );
	}
	
	rmdir( $folder );
}


function delete_with_wildcard( $filename )
{
	foreach( glob($filename) as $file )
	{
		if( is_dir($file) )
			remove_directory( $file, true );
		else
			unlink( $file );
	}
}


