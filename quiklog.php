<?php

// The advanced version

class QuikLog
{
	public $loggers;
	public $config;
	
	function __construct( $loggers, $config ){ $this->loggers = $loggers; $this->config = $config; }
	
	static function fromIniFile( $file ){ return QuikLog::fromIniParsed( parse_ini_file( $file, true ) ); }
	static function fromIniString( $str ){ return QuikLog::fromIniParsed( parse_ini_string( $str, true ) ); }
	static function fromIniParsed( $data )
	{
		$loggers = array();
		var_dump( $data );
		
		if( !isset( $data[ 'quiklog' ] ) )
			return 'No entry section \'quiklog\' found';
		
		if( !isset( $data[ 'quiklog' ][ 'loggers' ] ) )
			return 'No loggers found';
		
		foreach( $data[ 'quiklog' ][ 'loggers' ] as $logger )
		{
			if( !isset( $data[ $logger ] ) )
				return "No logger info found for logger '{$logger}'";
			$info = $data[ $logger ];
			if( !isset( $info[ 'type' ] ) )
				return "'type' parameter not found in logger '{$logger}'";
			if( !function_exists( 'quiklog_output_'.$info[ 'type' ] ) )
				return "Invalid type value '{$info['type']}' for logger '{$logger}'";
			
			$loggers[] = (object) array( 'name' => $logger, 'output' => $info[ 'type' ], 'filter' => '' );
		}
		
		return new QuikLog( $loggers, $data );
	}
	
	function logInfo( $what, $params = array() ){ $this->log( $what, 'info', $params ); }
	function logWarning( $what, $params = array() ){ $this->log( $what, 'warning', $params ); }
	function logError( $what, $params = array() ){ $this->log( $what, 'error', $params ); }
	
	function log( $what, $type, $params = array() )
	{
		foreach( $this->loggers as $logger )
		{
			if( $this->evalFilter( $logger->filter ) )
			{
				$config = $this->getConfig( 'output.'.$logger->output );
				$lcfg = $this->getConfig( $logger->name );
				if( $lcfg ) $config = array_merge( $config, $lcfg );
				call_user_func( 'quiklog_output_' . $logger->output, $what, $type, $params, $config, $this );
			}
		}
	}
	
	// PRIVATE-ish
	function evalFilter( $filter )
	{
		return true;
	}
	
	function getConfig( $key )
	{
		$config = array();
		$parts = explode( '.', $key );
		$ckey = '';
		foreach( $parts as $part )
		{
			if( $ckey ) $ckey .= '.';
			$ckey .= $part;
			if( isset( $this->config[ $ckey ] ) )
				$config = array_merge( $config, $this->config[ $ckey ] );
		}
		return $config;
	}
	
	function format( $what, $type, $params, $config )
	{
		$format = isset( $config[ 'format' ] ) ? $config[ 'format' ] : 'simple';
		if( $format === 'raw' )
			return $what;
		return call_user_func( 'quiklog_format_'.$format, $what, $type, $params, $this->getConfig( 'format.'.$format ) );
	}
}


// The simple version

function qlogconf( $file )
{
	global $_quiklog;
	$_quiklog = QuikLog::fromIniFile( $file );
	if( is_string( $_quiklog ) )
		trigger_error( 'Failed to initialize QuikLog - '.$_quiklog, E_USER_ERROR );
}

function qlog( $what, $type )
{
	global $_quiklog;
	if( !isset( $_quiklog ) )
		trigger_error( 'Need to call qlogconf before qlog/qlog_*', E_USER_ERROR );
	$_quiklog->log( $what, $type );
}

function qlog_info( $what ){ qlog( $what, 'info' ); }
function qlog_warning( $what ){ qlog( $what, 'warning' ); }
function qlog_error( $what ){ qlog( $what, 'error' ); }


// Filters / outputs / formats

function quiklog_output_php( $what, $type, $params, $config, $quiklog )
{
	switch( strtolower( $type ) )
	{
	case 'info': $type = E_USER_NOTICE; break;
	case 'warning': $type = E_USER_WARNING; break;
	case 'error': default: $type = E_USER_ERROR; break;
	}
	trigger_error( $what, $type );
}

function quiklog_output_file( $what, $type, $params, $config, $quiklog )
{
	$what = $quiklog->format( $what, $type, $params, $config );
	$f = fopen( $config[ 'file' ], 'a' );
	if( !$f )
		trigger_error( 'quiklog_output_file: Could not open file for writing', E_USER_ERROR );
	fwrite( $f, $what . PHP_EOL );
	fclose( $f );
}


function quiklog_format_simple( $what, $type, $params, $config )
{
	return
		strtoupper( $type ) . PHP_EOL
		. 'Time: ' . date( 'Y-m-d H:i:s' ) . PHP_EOL
		. 'Text: ' . $what . PHP_EOL
	;
}
