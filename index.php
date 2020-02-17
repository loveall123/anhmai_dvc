
    <?php
        define('NV_ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
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
        require NV_ROOTDIR . '/assets/vi.php';
        
        //phien ban
        $app_type = 'dvc';
        $version = $link_zip = '';
        $app_ver = $app_int = $update_action = 0;
        $version_get = file_get_contents( NV_ROOTDIR . '/version.json' );
        $version_array = (array)json_decode( $version_get );
        
        if( $version_array['app'] == $app_type )
        {
            $version = 'Phiên bản: ' . $version_array['version'];
            $app = explode( '.', $version_array['version'] );
            
            $app_ver = $app[0];
            if( ! empty( $app[1] ) ) $app_int = $app[1];
        }
        
        //kiem tra phien ban moi
        $version_new_get = @file_get_contents( 'http://anhmai.org/update/version.json' );
        
        if( $version_new_get != false )
        {
            $version_new_get = (array)json_decode( $version_new_get );
            $version_new = array();
            
            foreach( $version_new_get as $new )
            {
                $new = (array)$new;
                if( $new['app'] == $app_type )
                {
                    $version_new = $new;
                    break;
                }
            } 
            if( ! empty( $version_new ) )
            {
                $app_new = explode( '.', $version_new['version'] );
                
                if( $app_ver == $app_new[0] AND ! empty( $app_new[1] ) AND $app_int != 0 AND $app_int < $app_new[1] )
                {
                    $update_action = true;
                    
                    if( ! empty( $version_new['link_zip'] ) )
                    {
                        $link_zip = $version_new['link_zip'];
                    }
                    
                    $version .= '<a href="javascript:void(0)" class="margin-left" id="a_auto_update" onclick="nv_auto_update();">' . $lang_module['update'] . '</a>';
                }
            }
        }
        
        if( ! empty( $_POST['auto_update'] ) AND $update_action )
        {
            $result = array(
                'error' => 1,
                'message' => $lang_module['update_err']
            );
            if( ! empty( $link_zip ) )
            {
                $outFileName = NV_ROOTDIR . '/temp_update.zip';
                
                $options = array(
                  CURLOPT_FILE    => fopen($outFileName, 'w'),
                  CURLOPT_TIMEOUT =>  28800, // set this to 8 hours so we dont timeout on big files
                  CURLOPT_URL     => $link_zip
                );
        
                $ch = curl_init();
                curl_setopt_array($ch, $options);
                curl_exec($ch);
                curl_close($ch);
        
                $path = pathinfo(realpath( $outFileName ), PATHINFO_DIRNAME);

                $zip = new ZipArchive;
                $res = $zip->open( $outFileName );
                
                if ($res === TRUE) {
                  // extract it to the path we determined above
                  $zip->extractTo($path);
                  $zip->close();
                  
                  $result['error'] = 0;
                  @unlink( $outFileName );
                }
            }
            
            die( json_encode( $result ) );
        }
    ?>
<!DOCTYPE html>
<html lang="vi" xmlns="http://www.w3.org/1999/xhtml" prefix="og: http://ogp.me/ns#">

<head>
    <title><?php echo $lang_module['title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="<?php echo $lang_module['description']; ?>"/>
    <meta name="author" content="<?php echo $lang_module['title']; ?>"/>
    <meta name="copyright" content="<?php echo $lang_module['title']; ?> [votuong.tq@gmail.com]"/>
    <meta name="robots" content="index, archive, follow, noodp"/>
    <meta name="googlebot" content="index,archive,follow,noodp"/>
    <meta name="msnbot" content="all,index,follow"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" href="<?php echo NV_BASE_SITEURL; ?>favicon.ico"/>
    <link rel="StyleSheet" href="<?php echo NV_BASE_SITEURL; ?>css/font-awesome.min.css"/>
    <link rel="StyleSheet" href="<?php echo NV_BASE_SITEURL; ?>css/bootstrap.min.css"/>
    <link rel="StyleSheet" href="<?php echo NV_BASE_SITEURL; ?>css/style.css"/>
    <link rel="StyleSheet" href="<?php echo NV_BASE_SITEURL; ?>css/style.responsive.css"/>
    <link rel="StyleSheet" href="<?php echo NV_BASE_SITEURL; ?>css/tool.css"/>
</head>
<body>
    <div class="body-bg">
        <div class="wraper">
            <header>
                <div class="container">
                    <div id="header" class="row">
                        <div class="logo col-xs-24">
                            <a title="<?php echo $lang_module['title']; ?>" href="<?php echo NV_BASE_SITEURL; ?>"><img src="images/logo.png" width="100" height="70" alt="<?php echo $lang_module['title']; ?>" />
                            </a>
                            <span class="site_description"><?php echo $lang_module['description']; ?></span>
                        </div>
                    </div>
                </div>
            </header>
            <section id="body">
                <div class="container">
                    <div class="row">
                    </div>
                    <div class="row">
                        <div class="col-md-24">
                            <div class="text-center text-white" id="upload_result">
                                <div class="col-xs-24 col-sm-1"></div>
                                <div class="col-xs-24 col-sm-9">
                                    <div class="form-group"><a class="btn btn-primary width100" id="autoload_file" onclick="nv_autoload_file();"><?php echo $lang_module['capnhattudong']; ?></a>
                                    </div>
                                </div>
                                <div class="col-xs-24 col-sm-4">
                                    <div class="form-group">
                                        <div style="line-height: 3.5em;"><?php echo $lang_module['hoac']; ?></div>
                                    </div>
                                </div>
                                <div class="col-xs-24 col-sm-9">
                                    <div class="form-group"><a class="btn btn-success width100" id="add_file" onclick="nv_add_file();"><?php echo $lang_module['selectexcel']; ?></a>
                                    </div>
                                </div>
                                <div class="col-xs-24 col-sm-1"></div>
                                <div class="clearfix"></div>
                            </div>
                            <form id="excelUploadForm" class="form-inline excelUploadForm" action="<?php echo NV_BASE_SITEURL; ?>main.php" method="post" enctype="multipart/form-data" style="display: none;">
                                <input name="excel" type="file" id="excelUploadInput" />
                                <input type="hidden" name="capnhatexcel" value="1" />
                            </form>
                        </div>
                    </div>
                    <div class="row">
                    </div>
                </div>
            </section>
        </div>
    </div>
    <div class="thongtincapnhat">
        <div class="fl"><?php echo $version ?> <span id="updating"></span></div>
        <div class="fr">Hổ trợ: 0977.47.47.55</div>
    </div>
    <script src="<?php echo NV_BASE_SITEURL; ?>js/jquery.min.js"></script>
    <script src="<?php echo NV_BASE_SITEURL; ?>js/tool.js"></script>
    <script type="text/javascript">
        function nv_autoload_file() {
            $("#upload_result").html('<i style="margin-top: 5px" class="fa fa-spinner fa-spin fa-3x fa-fw"></i>');
            $.post('<?php echo NV_BASE_SITEURL; ?>' + 'detail.php', '&autoload_file=1', function(xhr) {
                var data = JSON.parse(xhr);
                console.log(data);
                if (data.error == 0) {
                    $("#upload_result").html('<div class="form-group">' + data.message + '</div><a class="btn btn-default width100" onclick="location.reload();;">Tiếp tục chuyển đổi</a>');
                } else if (data.error == 1) {
                    alert(data.message);
                    location.reload();
                }
            });
        }
        function nv_auto_update() {
            $("#updating").html('<i class="fa fa-spinner fa-spin fa-fw"></i>');
            $("#a_auto_update").hide();
            $.post('<?php echo NV_BASE_SITEURL; ?>', '&auto_update=1', function(xhr) {
                var data = JSON.parse(xhr);
                if (data.error == 0) {
                    location.reload(true);
                } else if (data.error == 1) {
                    alert(data.message);
                    location.reload(true);
                }
            });
        }

        function nv_add_file() {
            $("#excelUploadInput").click();
        }
        $(document).ready(function() {
            $("#excelUploadInput").change(function() {
                $('#excelUploadForm').submit();
                document.getElementById("add_file").disabled = true;
                $("#upload_result").html('<i style="margin-top: 5px" class="fa fa-spinner fa-spin fa-3x fa-fw"></i>');
            });
            $('#excelUploadForm').ajaxForm({
                beforeSend: function() {},
                complete: function(xhr) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.error == 0) {
                        $("#upload_result").html('<div class="form-group">' + data.message + '</div><a class="btn btn-default width100" onclick="location.reload();;">Tiếp tục chuyển đổi</a>');
                    } else if (data.error == 1) {
                        alert(data.message);
                        location.reload();
                    }
                }
            });
        });
    </script>
</body>

</html>