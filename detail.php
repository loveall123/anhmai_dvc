<?php

define('NV_ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));

require NV_ROOTDIR . '/assets/xtemplate.class.php';
require NV_ROOTDIR . '/assets/pclzip.lib.php';
require NV_ROOTDIR . '/assets/vi.php';

$filepath_root = 'C://dvc';
$keyword = 'anhmai_';

function nv_curl( $url, $data = array() )
{
	$result = array();

	if( ! empty( $url ) )
	{
		$userAgents = array(
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.91 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.104 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			);
		srand( ( float )microtime() * 10000000 );
		$rand = array_rand( $userAgents );
		$agent = $userAgents[$rand];

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		$safe_mode = ( ini_get( 'safe_mode' ) == '1' || strtolower( ini_get( 'safe_mode' ) ) == 'on' ) ? 1 : 0;
		$open_basedir = @ini_get( 'open_basedir' ) ? true : false;
		if( ! $safe_mode and ! $open_basedir )
		{
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		}

		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERAGENT, $agent );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$result = curl_exec( $ch );
		$curl_error = trim( curl_error( $ch ) );
		curl_close( $ch );
	}
	return $result;
}

$base_siteurl = pathinfo( $_SERVER['PHP_SELF'], PATHINFO_DIRNAME );
        
if ( $base_siteurl == DIRECTORY_SEPARATOR )
{
    $base_siteurl = '';
}
if ( ! empty( $base_siteurl ) )
{
    $base_siteurl = str_replace( DIRECTORY_SEPARATOR, '/', $base_siteurl );
}
if ( ! empty( $base_siteurl ) )
{
    $base_siteurl = preg_replace( '/[\/]+$/', '', $base_siteurl );
}
if ( ! empty( $base_siteurl ) )
{
    $base_siteurl = preg_replace( '/^[\/]*(.*)$/', '/\\1', $base_siteurl );
}
if ( defined( 'NV_WYSIWYG' ) and ! defined( 'NV_ADMIN' ) )
{
    $base_siteurl = preg_replace( '#/' . NV_EDITORSDIR . '(.*)$#', '', $base_siteurl );
}
elseif ( defined( 'NV_IS_UPDATE' ) )
{
    // Update se bao gom ca admin nen update phai dat truoc
    $base_siteurl = preg_replace( '#/install(.*)$#', '', $base_siteurl );
}
elseif ( defined( 'NV_ADMIN' ) )
{
    $base_siteurl = preg_replace( '#/' . NV_ADMINDIR . '(.*)$#i', '', $base_siteurl );
}
elseif ( ! empty( $base_siteurl ) )
{
    $base_siteurl = preg_replace( '#/index\.php(.*)$#', '', $base_siteurl );
}
define( 'NV_BASE_SITEURL', $base_siteurl . '/' );

$server_name = trim( ( isset( $_SERVER['HTTP_HOST'] ) and ! empty( $_SERVER['HTTP_HOST'] ) ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] );
$server_name = preg_replace( '/^[a-z]+\:\/\//i', '', $server_name );
$server_name = preg_replace( '/(\:[0-9]+)$/', '', $server_name );
$server_protocol = strtolower( preg_replace( '/^([^\/]+)\/*(.*)$/', '\\1', $_SERVER['SERVER_PROTOCOL'] ) ) . ( ( ! empty( $_SERVER['HTTPS'] ) AND $_SERVER['HTTPS'] == 'on' ) ? 's' : '' );
$server_port = ( $_SERVER['SERVER_PORT'] == '80' or $_SERVER['SERVER_PORT'] == '443' ) ? '' : ( ':' . $_SERVER['SERVER_PORT'] );

if ( filter_var( $server_name, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) === false )
{
    $my_current_domain = $server_protocol . '://' . $server_name . $server_port;
}
else
{
    $my_current_domain = $server_protocol . '://[' . $server_name . ']' . $server_port;
}
define( 'NV_MY_DOMAIN', $my_current_domain );

if ( $_POST['autoload_file'] == 1 )
{
    $filepath = $filepath_root;

    $all_files = array_diff( scandir( $filepath ), array( '..', '.' ) );
    
    $all_files_xls = array();

    if ( ! empty( $all_files ) )
    {
        $output = array(
            'error' => 0,
            'message' => '',
        );
        foreach ( $all_files as $value )
        {
            $basename = basename( $value );

            $FileType = pathinfo( $basename, PATHINFO_EXTENSION );

            if ( ( $FileType == 'xls' || $FileType == 'xlsx' ) and substr( $basename, 0, 7 ) == $keyword )
            {
                $filename = $basename;
                $filedata = $filepath . '/' . $value;
                $filesize = filesize( $filedata );

                if ( $filesize > 0 )
                {
                    $postfields = array( "filedata" => $filedata, "autoload" => 1, 'capnhatexcel' => 1, 'filepath_c' => $filepath );
                    $url = NV_MY_DOMAIN . NV_BASE_SITEURL . "main.php";
                    
                    $result = nv_curl( $url, $postfields );
                    
                    $data = (array)json_decode( $result );
                    
                    if( ! empty( $data ) )
                    {
                        if( $data['error'] == 0 )
                        {
                            $paths = explode( '/', $data['filepath'] );
                            $zipfile = array_pop( $paths );
                            
                            //chuyen file sang thu muc co dinh
                            rename( $data['filepath'], $filepath . '/' . $zipfile);
                            
                            //xoa xls
                            unlink( $filedata );
                            
                            $mes = sprintf( $lang_module['auto_complete'], strtoupper( $data['filetype'] ) );
                            
                            $output['message'] .= '<div class="form-group"><div class="btn btn-info width100">' . $mes . ' ' . $lang_module['auto_complete2'] . ' ' . $filepath . '</div></div>';
                        }
                    }
                }
            }

        }
        
        if( $output['message'] != '' )
        {
            die( json_encode( $output ) );
        }
        else
        {
            $data = array(
                'error' => 1, //bi loi
                'message' => $lang_module['empty_file_auto'],
                );
    
            die( json_encode( $data ) );
        }
    }
    else
    {
        $data = array(
            'error' => 1, //bi loi
            'message' => $lang_module['empty_file_auto'],
            );

        die( json_encode( $data ) );
    }

    die;
}

exit;