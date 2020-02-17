<?php

session_start();
error_reporting(0);

define('NV_CHECK_SESSION', session_id() );
define('NV_ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
$ip = $_SERVER['HTTP_CLIENT_IP']?$_SERVER['HTTP_CLIENT_IP']:($_SERVER['HTTP_X_FORWARDED_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
$client_info['ip'] = $ip;

require NV_ROOTDIR . '/assets/xtemplate.class.php';
require NV_ROOTDIR . '/assets/pclzip.lib.php';
require NV_ROOTDIR . '/assets/vi.php';

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
elseif ( ! empty( $base_siteurl ) )
{
    $base_siteurl = preg_replace( '#/index\.php(.*)$#', '', $base_siteurl );
}

define( 'NV_BASE_SITEURL', $base_siteurl . '/' );

function nv_date($format, $time = 0)
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    
    if (! $time) {
        $time = time();
    }
    $format = str_replace("r", "D, d M Y H:i:s O", $format);
    $format = str_replace(array( "D", "M" ), array( "[D]", "[M]" ), $format);
    $return = date($format, $time);
    
    return $return;
}

function nv_xml_content( $filetype, $main_data, $item_data )
{
    global $module_info, $module_file;
    
    $xtpl = new XTemplate( $filetype . '.tpl', NV_ROOTDIR . '/tpl/' );
    $xtpl->assign( 'MAIN', $main_data );
    
    if( ! empty( $item_data ) )
    {
        foreach( $item_data as $item )
        {
            $xtpl->assign( 'ITEM', $item );
            
            $xtpl->parse( 'main.item' );
        }
    }
    
    $xtpl->parse( 'main' );
    $content = $xtpl->text( 'main' );
    
    return $content;
}

function nv_pathinfo_filename($file)
{
    if (defined('PATHINFO_FILENAME')) {
        return pathinfo($file, PATHINFO_FILENAME);
    }
    if (strstr($file, '.')) {
        return substr($file, 0, strrpos($file, '.'));
    }
}

function del_blank( $text )
{
    $return = preg_replace('/[\s]+/mu', ' ', $text);

    return $return;
}

function tcvn2uni( $text )
{
    $vietU = '|á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ|é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|ó|ò|ỏ|õ|ọ|ơ|ớ|ờ|ở|ỡ|ợ|ô|ố|ồ|ổ|ỗ|ộ|ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|í|ì|ỉ|ĩ|ị|ý|ỳ|ỷ|ỹ|ỵ|đ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ|Ó|Ò|Ỏ|Õ|Ọ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự|Í|Ì|Ỉ|Ĩ|Ị|Ý|Ỳ|Ỷ|Ỹ|Ỵ|Đ';
    $vietT = '|¸|µ|¶|·|¹|¨|¾|»|¼|½|Æ|©|Ê|Ç|È|É|Ë|Ð|Ì|Î|Ï|Ñ|ª|Õ|Ò|Ó|Ô|Ö|ã|ß|á|â|ä|¬|í|ê|ë|ì|î|«|è|å|æ|ç|é|ó|ï|ñ|ò|ô|­|ø|õ|ö|÷|ù|Ý|×|Ø|Ü|Þ|ý|ú|û|ü|þ|®|¸|µ|¶|·|¹|¡|¾|»|¼|½|Æ|¢|Ê|Ç|È|É|Ë|Ð|Ì|Î|Ï|Ñ|£|Õ|Ò|Ó|Ô|Ö|ã|ß|á|â|ä|¥|í|ê|ë|ì|î|¤|è|å|æ|ç|é|ó|ï|ñ|ò|ô|¦|ø|õ|ö|÷|ù|Ý|×|Ø|Ü|Þ|ý|ú|û|ü|þ|§';
    $UNI = explode( "|", $vietU );
    $TCVN3 = explode( "|", $vietT );
    $arr1 = mb_split( $text, 'UTF-8' );
    $arr2 = array();
    $len = mb_strlen( $text, 'UTF-8' );
    
    for ( $i = 0; $i <= $len; $i++ )
    {
        $char = mb_substr( $text, $i, 1, 'UTF-8' );
        $result[] = $char;
        $key = array_search( $char, $TCVN3 );
    
        if ( $key != '' )
        {
            $arr2[$i] = $UNI[$key];
        }
        else
        {
            $arr2[$i] = $char;
        }
    }
    $return = implode( "", $arr2 );

    return $return;
}

if ( $_POST['capnhatexcel'] == 1 )
{
    $autoload = $_POST['autoload'];
    $filepath_c = $_POST['filepath_c'];
    
    if( $autoload == 1 )
    {
        $filename = $_POST['filedata'];
        $FileType = pathinfo( $filename, PATHINFO_EXTENSION );
    }
    else
    {
        $fileupload = $_FILES['excel'];
        $FileType = pathinfo( $fileupload['name'], PATHINFO_EXTENSION );
        $filename = $fileupload['tmp_name'];
    }
    
    if ( ! empty( $filename ) and ( $FileType == 'xls' || $FileType == 'xlsx' ) )
    {
        require_once NV_ROOTDIR . '/PHPExcel.php';
    
        $objPHPExcel = new PHPExcel();

        //doc file
        $inputFileType = PHPExcel_IOFactory::identify( $filename );
        $objReader = PHPExcel_IOFactory::createReader( $inputFileType );
        $objReader->setReadDataOnly( true );
        $objPHPExcel = $objReader->load( $filename );
        $total_sheets = $objPHPExcel->getSheetCount();
        $allSheetName = $objPHPExcel->getSheetNames();
        $objWorksheet = $objPHPExcel->setActiveSheetIndex( 0 );
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $item_data = $main_data = $_item_data = $_main_data = array();
        
        $i_col = 0;
        for( $column = 'A'; $column != $highestColumn; $column++ ) 
        {
            $i_col++;
        }
    
        $filetype = $objWorksheet->getCellByColumnAndRow( $i_col, 2 )->getValue();
        $filetype = strtolower( $filetype );
        $sochungtu = '';
        
        if( $filetype == 'c202a' )
        {
            for ( $col = 0; $col <= 20; ++$col )
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            
            for ( $col = 30; $col <= 35; ++$col )
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 21; $col <= 29; ++$col )
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
    
            //tao lai main data
            if( ! empty( $_main_data[33] ) ) $sochungtu = '_' . $_main_data[33];
            $main_data = array(
                'chuyenkhoanTienmat' => $_main_data[0],
                'dmDvqhns' => $_main_data[1],
                'dmTiente' => $_main_data[2],
                'dvNhantienCtmt' => $_main_data[3],
                'dvNhantienDiachi' => $_main_data[4],
                'dvNhantienKbnn' => $_main_data[5],
                'dvNhantienKbnnTen' => $_main_data[6],
                'dvNhantienLoai' => $_main_data[7],
                'dvNhantienMa' => $_main_data[8],
                'dvNhantienSotiennhan' => $_main_data[9],
                'dvNhantienSotkSo' => $_main_data[10],
                'dvNhantienTen' => $_main_data[11],
                'dvNopthueSotiennop' => '',
                'dvqhnsCapns' => $_main_data[12],
                'dvqhnsCkcHdk' => $_main_data[13],
                'dvqhnsCkcHdth' => $_main_data[14],
                'dvqhnsCtmt' => $_main_data[15],
                'dvqhnsKbnn' => $_main_data[16],
                'dvqhnsMa' => $_main_data[17],
                'dvqhnsNamns' => $_main_data[18],
                'dvqhnsTen' => $_main_data[19],
                'ngayChungTu' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[31] ) . '000',
                'par1' => empty( $_main_data[32] ) ? '------' : $_main_data[32],
                'soChungTu' => $_main_data[33] );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[21] ) )
                    {
                        $item_data[] = array(
                            'dmChuong' => $row[21],
                            'dmNdkt' => $row[22],
                            'dmNganhKt' => $row[23],
                            'dmNguonchi' => $row[24],
                            'dvNhantien' => $row[25],
                            'dvNopthue' => $row[26],
                            'maHang' => $row[27],
                            'noiDung' => $row[28],
                            'soTien' => $row[29],
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'c202b' )
        {
            for ( $col = 0; $col <= 30; ++$col )
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            for ( $col = 40; $col <= 47; ++$col )
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 31; $col <= 39; ++$col )
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            if( ! empty( $_main_data[44] ) ) $sochungtu = '_' . $_main_data[44];
            
            $main_data = array(
                'chuyenkhoanTienmat' => $_main_data[0],
                'dmDvqhns' => $_main_data[1],
                'dmTiente' => $_main_data[2],
                'dvNhantienDiachi' => $_main_data[4],
                'dvNhantienKbnn' => $_main_data[5],
                'dvNhantienKbnnTen' => $_main_data[6],
                'dvNhantienLoai' => empty( $_main_data[7] ) ? 0 : $_main_data[7],
                'dvNhantienMa' => empty( $_main_data[8] ) ? 0 : $_main_data[8],
                'dvNhantienSotiennhan' => $_main_data[9],
                'dvNhantienSotkSo' => $_main_data[10],
                'dvNhantienTen' => $_main_data[11],
                'dvNopthueChuong' => $_main_data[12],
                'dvNopthueCqthuMa' => $_main_data[13],
                'dvNopthueCqthuTen' => $_main_data[14],
                'dvNopthueKbHachtoan' => $_main_data[15],
                'dvNopthueKbHachtoanTen' => $_main_data[16],
                'dvNopthueKythue' => $_main_data[17],
                'dvNopthueMasothue' => $_main_data[18],
                'dvNopthueNdkt' => $_main_data[19],
                'dvNopthueSotiennop' => $_main_data[20],
                'dvNopthueTen' => $_main_data[21],
                'dvqhnsCapns' => $_main_data[22],
                'dvqhnsCkcHdk' => $_main_data[23],
                'dvqhnsCkcHdth' => $_main_data[24],
                'dvqhnsCtmt' => $_main_data[25],
                'dvqhnsMa' => $_main_data[27],
                'dvqhnsNamns' => $_main_data[28],
                'dvqhnsTen' => $_main_data[29],
                'ngayChungTu' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[41] ) . '000',
                'par1' => $_main_data[42],
                'par2' => $_main_data[43],
                'par3' => '------',
                'soChungTu' => $_main_data[44],
                'dvqhnsTen' => $_main_data[29],
                'dvqhnsTen' => $_main_data[29],
                'dvqhnsTen' => $_main_data[29]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[31] ) )
                    {
                        $item_data[] = array(
                            'dmChuong' => $row[31],
                            'dmNdkt' => $row[32],
                            'dmNganhKt' => $row[33],
                            'dmNguonchi' => $row[34],
                            'dvNhantien' => $row[35],
                            'dvNopthue' => $row[36],
                            'maHang' => $row[37],
                            'noiDung' => $row[38],
                            'soTien' => $row[39],
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'm01_bk' )
        {
            for ( $col = 0; $col <= 5; ++$col )
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            
            for ( $col = 17; $col <= 21; ++$col )
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 5; $col <= 16; ++$col )
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            if( ! empty( $_main_data[18] ) ) $sochungtu = '_' . $_main_data[18];
            
            $main_data = array(
                'dmDvqhns' => $_main_data[0],
                'dmTiente' => $_main_data[1],
                'dvqhnsCtmt' => $_main_data[2],
                'dvqhnsMa' => $_main_data[3],
                'dvqhnsNguon' => $_main_data[4],
                'dvqhnsTen' => $_main_data[5],
                'par1' => $_main_data[17],
                'soChungTu' => $_main_data[18]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[12] ) )
                    {
                        $item_data[] = array(
                            'chungTuNgay' => PHPExcel_Shared_Date::ExcelToPHP( $row[6] ) . '000',
                            'chungTuSo' => $row[7],
                            'dmNdkt' => $row[8],
                            'ngayHoaDon' => PHPExcel_Shared_Date::ExcelToPHP( $row[9] ) . '000',
                            'noiDung' => $row[10],
                            'soHoaDon' => $row[11],
                            'soTien' => $row[12],
                            'soTienDm' => $row[13],
                            'soTienSl' => $row[14],
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'c203' )
        {
            for ( $row = 2; $row <= 2; ++$row )
            {
                for ( $col = 0; $col <= 16; ++$col )
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_main_data[$col] = del_blank( tcvn2uni( $value ) );
                }
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 16; $col <= 24; ++$col )
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            $main_data = array(
                'dmTiente' => $_main_data[0],
                'dvqhnsCancuTuUt' => $_main_data[1],
                'dvqhnsCancuTuUtKbnn' => $_main_data[2],
                'dvqhnsCancuTuUtKbnnTen' => $_main_data[3],
                'dvqhnsCancuTuUtNgay' => $_main_data[4],
                'dvqhnsCapns' => $_main_data[5],
                'dvqhnsCtmt' => $_main_data[6],
                'dvqhnsCtmtMa' => $_main_data[7],
                'dvqhnsCtmtTen' => $_main_data[8],
                'dvqhnsKbnn' => $_main_data[9],
                'dvqhnsMa' => $_main_data[10],
                'dvqhnsNamns' => $_main_data[11],
                'dvqhnsSotk' => $_main_data[12],
                'dvqhnsSotkSo' => $_main_data[13],
                'dvqhnsTen' => $_main_data[14],
                'dvqhnsThanhtoanThanhTcUt' => $_main_data[15],
                'dvqhnsThanhtoanTuUt' => $_main_data[16]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[17] ) )
                    {
                        $item_data[] = array(
                            'dmChuong' => $row[17],
                            'dmNdkt' => $row[18],
                            'dmNganhKt' => $row[19],
                            'dmNguonchi' => $row[20],
                            'maHang' => $row[21],
                            'soDeNghi' => $row[22],
                            'soPheDuyet' => $row[23],
                            'soUngTruoc' => $row[24],
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'c409' )
        {
            for ( $col = 0; $col <= 3; ++$col ) //3 la cot cuoi du lieu tren dau
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            
            for ( $col = 7; $col <= 15; ++$col ) //7 la cot bat dau phan cuoi, 15 la cot ket thuc phan cuoi
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getCalculatedValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 4; $col <= 6; ++$col ) //4 la cot bat dau bang, 6 là cot ket thuc bang
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            $main_data = array(
                'diaChi' => $_main_data[0],
                'dmTiente' => $_main_data[1],
                'dvqhnsMa' => $_main_data[2],
                'dvqhnsTen' => $_main_data[3],
                'nguoilinhHoten' => $_main_data[8],
                'nguoilinhNgaycapCmnd' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[9] ) . '000',
                'nguoilinhNoicapCmnd' => $_main_data[10],
                'nguoilinhSoCmnd' => $_main_data[11],
                'par1' => $_main_data[12]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[5] ) ) //Xac dinh 1 cot bat buoc co du lieu
                    {
                        $item_data[] = array(
                            'maHang' => $row[4],
                            'noiDung' => $row[5],
                            'soTien' => $row[6]
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'c301' )
        {
            for ( $col = 0; $col <= 33; ++$col )
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            for ( $col = 44; $col <= 53; ++$col )
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 34; $col <= 43; ++$col )
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            if( ! empty( $_main_data[50] ) ) $sochungtu = '_' . $_main_data[50];
            
            $main_data = array(
                'ckTm' => $_main_data[0],
                'daCancuHdNgay' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[1] ) . '000',
                'daCancuHdSo' => $_main_data[2],
                'daCapns' => $_main_data[3],
                'daCkcHdk' => $_main_data[4],
                'daCkcHdth' => $_main_data[5],
                'daCtmt' => $_main_data[6],
                'daKbnn' => $_main_data[7],
                'daKbnnMa' => $_main_data[8],
                'daKbnnTen' => $_main_data[9],
                'daNamns' => $_main_data[10],
                'dmTiente' => $_main_data[11],
                'dvNhantienCtmt' => $_main_data[12],
                'dvNhantienDiachi' => $_main_data[13],
                'dvNhantienKbnn' => $_main_data[14],
                'dvNhantienKbnnTen' => $_main_data[15],
                'dvNhantienLoai' => $_main_data[16],
                'dvNhantienMa' => $_main_data[17],
                'dvNhantienSotiennhan' => $_main_data[18],
                'dvNhantienSotkSo' => $_main_data[19],
                'dvNhantienTen' => $_main_data[20],
                'dvNopthueChuong' => $_main_data[21],
                'dvNopthueCqthu' => $_main_data[22],
                'dvNopthueCqthuMa' => $_main_data[23],
                'dvNopthueCqthuTen' => $_main_data[24],
                'dvNopthueKbHachtoan' => $_main_data[25],
                'dvNopthueKbHachtoanTen' => $_main_data[26],
                'dvNopthueKythue' => $_main_data[27],
                'dvNopthueMasothue' => $_main_data[28],
                'dvNopthueNdkt' => $_main_data[29],
                'dvNopthueSotiennop' => $_main_data[30],
                'dvNopthueTen' => $_main_data[31],
                'dvqhnsMa' => $_main_data[32],
                'dvqhnsTen' => $_main_data[33],
                'gnTaiLieuId' => empty( $_main_data[44] ) ? 93 : $_main_data[44],
                'ngayChungTu' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[45] ) . '000',
                'par1' => empty( $_main_data[46] ) ? '------' : $_main_data[46],
                'par2' => empty( $_main_data[47] ) ? '------' : $_main_data[47],
                'par3' => empty( $_main_data[48] ) ? '------' : $_main_data[48],
                'par4' => empty( $_main_data[49] ) ? '------' : $_main_data[49],
                'soChungTu' => $_main_data[50],
                'tongSoTien' => $_main_data[51],
                'ttTu' => empty( $_main_data[52] ) ? 2 : $_main_data[52],
                'typeChungTu' => empty( $_main_data[53] ) ? 8 : $_main_data[53]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[42] ) )
                    {
                        $item_data[] = array(
                            'dmChuong' => $row[34],
                            'dmNdkt' => $row[35],
                            'dmNganhKt' => $row[36],
                            'dmNguonchi' => $row[37],
                            'maHang' => $row[38],
                            'namKhv' => $row[39],
                            'noiDung' => $row[40],
                            'nopThue' => $row[41],
                            'soTien' => $row[42],
                            'thanhToan' => $row[43]
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'c402a' )
        {
            for ( $col = 0; $col <= 18; ++$col ) //3 la cot cuoi du lieu tren dau
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            
            for ( $col = 24; $col <= 29; ++$col ) //7 la cot bat dau phan cuoi, 15 la cot ket thuc phan cuoi
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getCalculatedValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 19; $col <= 23; ++$col ) //4 la cot bat dau bang, 6 là cot ket thuc bang
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            if( ! empty( $_main_data[27] ) ) $sochungtu = '_' . $_main_data[27];
            
            $main_data = array(
                'dmDvTratien' => $_main_data[0],
                'dmTiente' => $_main_data[2],
                'dvNhantienCtmt' => $_main_data[3],
                'dvNhantienDiachi' => $_main_data[4],
                'dvNhantienKbnn' => $_main_data[5],
                'dvNhantienKbnnNhTen' => $_main_data[6],
                'dvNhantienLoai' => $_main_data[7],
                'dvNhantienMa' => $_main_data[8],
                'dvNhantienSotien' => $_main_data[9],
                'dvNhantienSotkSo' => $_main_data[10],
                'dvNhantienTen' => $_main_data[11],
                'dvTratienCtmt' => $_main_data[12],
                'dvTratienDiachi' => $_main_data[13],
                'dvTratienKbnn' => $_main_data[14],
                'dvTratienKbnnNhTen' => $_main_data[15],
                'dvTratienLoai' => $_main_data[16],
                'dvTratienMa' => $_main_data[17],
                'dvTratienTen' => $_main_data[18],
                'ngayChungTu' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[25] ) . '000',
                'par1' => empty( $_main_data[26] ) ? '------' : $_main_data[26],
                'soChungTu' => $_main_data[27],
                'tongSoTien' => $_main_data[28]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[22] ) ) //Xac dinh 1 cot bat buoc co du lieu
                    {
                        $item_data[] = array(
                            'maHang' => $row[19],
                            'noiDung' => $row[20],
                            'nopThue' => $row[21],
                            'soTien' => $row[22],
                            'thanhToan' => $row[23]
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'c402c' )
        {
            for ( $col = 0; $col <= 28; ++$col ) //3 la cot cuoi du lieu tren dau
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            
            for ( $col = 34; $col <= 41; ++$col ) //7 la cot bat dau phan cuoi, 15 la cot ket thuc phan cuoi
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getCalculatedValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 29; $col <= 33; ++$col ) //4 la cot bat dau bang, 6 là cot ket thuc bang
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            if( ! empty( $_main_data[39] ) ) $sochungtu = '_' . $_main_data[39];
            
            $main_data = array(
                'dmDvTratien' => $_main_data[0],
                'dmTiente' => $_main_data[1],
                'dvNhantienCtmt' => $_main_data[2],
                'dvNhantienDiachi' => $_main_data[3],
                'dvNhantienKbnn' => $_main_data[4],
                'dvNhantienKbnnNhTen' => $_main_data[5],
                'dvNhantienLoai' => $_main_data[6],
                'dvNhantienMa' => $_main_data[7],
                'dvNhantienNganhang' => $_main_data[8],
                'dvNhantienSotien' => $_main_data[9],
                'dvNhantienSotkSo' => $_main_data[10],
                'dvNhantienTen' => $_main_data[11],
                'dvNopthueChuong' => $_main_data[12],
                'dvNopthueCqthu' => $_main_data[13],
                'dvNopthueCqthuMa' => $_main_data[14],
                'dvNopthueCqthuTen' => $_main_data[15],
                'dvNopthueHachtoan' => $_main_data[16],
                'dvNopthueKythue' => $_main_data[17],
                'dvNopthueMa' =>  $_main_data[18],
                'dvNopthueNdkt' => $_main_data[19],
                'dvNopthueStk' => $_main_data[20],
                'dvNopthueTen' => $_main_data[21],
                'dvTratienCtmt' => $_main_data[22],
                'dvTratienDiachi' => $_main_data[23],
                'dvTratienKbnn' => $_main_data[24],
                'dvTratienKbnnNhTen' => $_main_data[25],
                'dvTratienLoai' => $_main_data[26],
                'dvTratienMa' => $_main_data[27],
                'dvTratienTen' => $_main_data[28],
                'ngayChungTu' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[35] ) . '000',
                'par1' => empty( $_main_data[26] ) ? '------' : $_main_data[36],
                'par2' => empty( $_main_data[26] ) ? '------' : $_main_data[37],
                'par3' => empty( $_main_data[26] ) ? '------' : $_main_data[38],
                'soChungTu' => $_main_data[39],
                'tongSoTien' => $_main_data[40]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[32] ) ) //Xac dinh 1 cot bat buoc co du lieu
                    {
                        $item_data[] = array(
                            'maHang' => $row[29],
                            'noiDung' => $row[30],
                            'nopThue' => $row[31],
                            'soTien' => $row[32],
                            'thanhToan' => $row[33]
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'm05' )
        {
            for ( $col = 0; $col <= 21; ++$col ) //3 la cot cuoi du lieu tren dau
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            
            for ( $col = 29; $col <= 44; ++$col ) //7 la cot bat dau phan cuoi, 15 la cot ket thuc phan cuoi
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getCalculatedValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 22; $col <= 28; ++$col ) //4 la cot bat dau bang, 6 là cot ket thuc bang
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            if( ! empty( $_main_data[33] ) ) $sochungtu = '_' . $_main_data[33];
            
            $main_data = array(
                'daCancuHdNgay' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[0] ) . '000',
                'daCancuHdSo' => $_main_data[1],
                'daCancuKlhtNgay' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[2] ) . '000',
                'daCancuKlhtSo' => $_main_data[3],
                'daCancuPhulucHdNgay' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[4] ) . '000',
                'daCancuPhulucHdSo' => $_main_data[5],
                'daDenghiKehoachvon' => $_main_data[6],
                'daDenghiNamKhv' => $_main_data[7],
                'daDenghiNguonvon' => $_main_data[8],
                'daDenghiTtLuyke' => $_main_data[9],
                'daDenghiTtSodu' => $_main_data[10],
                'daDenghiTuTt' => $_main_data[11],
                'daNgoainuocSo' => $_main_data[12],
                'daNgoainuocTai' => $_main_data[13],
                'daTrongnuocSo' => $_main_data[14],
                'daTrongnuocTai' => $_main_data[15],
                'dmTiente' => $_main_data[16],
                'dvThuhuongSotkSo' => $_main_data[17],
                'dvThuhuongSotkTai' =>  $_main_data[18],
                'dvThuhuongTen' => $_main_data[19],
                'dvqhnsMa' => $_main_data[20],
                'dvqhnsTen' => $_main_data[21],
                'gnTaiLieuId' => $_main_data[29],
                'ngayChungTu' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[30] ) . '000',
                'par1' => empty( $_main_data[31] ) ? '------' : $_main_data[31],
                'par2' => empty( $_main_data[32] ) ? '------' : $_main_data[32],
                'soChungTu' => $_main_data[33],
                'stBaohanh' => $_main_data[34],
                'stBaohanhNgoainuoc' => $_main_data[35],
                'stBaohanhTrongnuoc' => $_main_data[36],
                'stThue' => $_main_data[37],
                'stThuhoi' => $_main_data[38],
                'stThuhoiNgoainuoc' => $_main_data[39],
                'stThuhoiTrongnuoc' => $_main_data[40],
                'stThuhuong' => $_main_data[41],
                'stThuhuongNgoainuoc' => $_main_data[42],
                'stThuhuongTrongnuoc' => $_main_data[43]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[28] ) ) //Xac dinh 1 cot bat buoc co du lieu
                    {
                        $item_data[] = array(
                            'duToan' => $row[22],
                            'luykeNgoainuoc' => $row[23],
                            'luykeTrongnuoc' => $row[24],
                            'maHang' => $row[25],
                            'noiDung' => $row[26],
                            'tamungNgoainuoc' => $row[27],
                            'tamungTrongnuoc' => $row[28]
                            );
                    }
                }
            }
        }
        elseif( $filetype == 'c302' )
        {
            for ( $col = 0; $col <= 17; ++$col ) //3 la cot cuoi du lieu tren dau
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
            
            for ( $col = 28; $col <= 37; ++$col ) //7 la cot bat dau phan cuoi, 15 la cot ket thuc phan cuoi
            {
                $value = $objWorksheet->getCellByColumnAndRow( $col, 2 )->getCalculatedValue();

                $_main_data[$col] = del_blank( tcvn2uni( $value ) );
            }
    
            for ( $row = 2; $row <= $highestRow; ++$row )
            {
                for ( $col = 18; $col <= 27; ++$col ) //4 la cot bat dau bang, 6 là cot ket thuc bang
                {
                    $value = $objWorksheet->getCellByColumnAndRow( $col, $row )->getValue();
    
                    $_item_data[$row - 1][$col] = del_blank( tcvn2uni( $value ) );
                }
            }
            
            //tao lai main data
            $daCancuDenghiTungay = PHPExcel_Shared_Date::ExcelToPHP( $_main_data[2] );
            if( ! empty( $_main_data[34] ) ) $sochungtu = '_' . $_main_data[34];
            
            $main_data = array(
                'daCancuDenghiDenngay' => $_main_data[0],
                'daCancuDenghiSo' => $_main_data[1],
                'daCancuDenghiTungay' => nv_date( 'd/m/Y', $daCancuDenghiTungay ),
                'daCapns' => $_main_data[3],
                'daCkcHdth' => $_main_data[4],
                'daCtmt' => $_main_data[5],
                'daDenghiKbnn' => $_main_data[6],
                'daDenghiKbnnTen' => $_main_data[7],
                'daKbnn' => $_main_data[8],
                'daNamns' => $_main_data[9],
                'daSoduTamungUngtruoc' => $_main_data[10],
                'daSotk' => $_main_data[11],
                'daSotkSo' => $_main_data[12],
                'daTcUtDktt' => $_main_data[13],
                'daTuUtChuaDktt' => $_main_data[14],
                'dmTiente' => $_main_data[15],
                'dvqhnsMa' => $_main_data[16],
                'dvqhnsTen' => $_main_data[17],
                'gnTaiLieuId' =>  $_main_data[28],
                'ngayChungTu' => PHPExcel_Shared_Date::ExcelToPHP( $_main_data[29] ) . '000',
                'par1' => empty( $_main_data[30] ) ? '------' : $_main_data[30],
                'par2' => empty( $_main_data[31] ) ? '------' : $_main_data[31],
                'par3' => empty( $_main_data[32] ) ? '------' : $_main_data[32],
                'par4' => empty( $_main_data[33] ) ? '------' : $_main_data[33],
                'soChungTu' => $_main_data[34],
                'tongSoTien' => $_main_data[35],
                'tuUt' => $_main_data[36]
            );
    
            //tao lai mang du lieu
            if ( ! empty( $_item_data ) )
            {
                foreach ( $_item_data as $row )
                {
                    if ( ! empty( $row[27] ) ) //Xac dinh 1 cot bat buoc co du lieu
                    {
                        $item_data[] = array(
                            'dmChuong' => $row[18],
                            'dmNdkt' => $row[19],
                            'dmNganhKt' => $row[20],
                            'dmNguonchi' => $row[21],
                            'maHang' => $row[22],
                            'namKhv' => $row[23],
                            'noiDung' => $row[24],
                            'soDeNghi' => $row[25],
                            'soDuTamUng' => $row[26],
                            'soThanhToan' => $row[27]
                            );
                    }
                }
            }
        }
        else
        {
            $data = array(
                'error' => 1, //bi loi
                'message' => 'Mẫu này chưa hỗ trợ',
            );
            
            die( json_encode( $data ) );
        }
        
        @unlink( $fileupload['tmp_name'] ); //xoa file tam
        
        $objPHPExcel->disconnectWorksheets();
        $objPHPExcel->garbageCollect();
        unset($objPHPExcel);
        
        if ( ! empty( $item_data ) )
        {  
            //tao noi dung xml
            $xml = nv_xml_content( $filetype, $main_data, $item_data );
            
            $filename = 'AnhMai_' . $filetype . $sochungtu . '_' . md5( NV_CHECK_SESSION . $client_info['ip'] ) . '.xml';
            
            $filepath = NV_ROOTDIR . '/uploads/';
            
            file_put_contents( $filepath . '/' . $filename, $xml);
            
            $file_src =  $filepath . '/' . $filename;
            $subfile = nv_pathinfo_filename( $filename );
            
            $tem_file = $filepath . '/' . $subfile . '.zip';
            
            @unlink( $tem_file ); //xoa file cu
            
            $zip = new PclZip( $tem_file );
            
            $paths = explode( '/', $file_src );
            array_pop( $paths );
            $paths = implode( '/', $paths );
            
            $zip->add( $file_src, PCLZIP_OPT_REMOVE_PATH, $paths );
            
            //Kiem tra file nen xong thi xoa
            $file_exists = file_exists( $tem_file );
            
            if ( $file_exists )
            {
                unlink( $file_src );
            }
            
            if ( file_exists( $tem_file ) )
            {
                $file_src = $tem_file;
                $file_basename = $subfile . '.zip';
                $directory = NV_ROOTDIR . '/uploads/';
            }
              
            $data = array(
                'error' => 0, //bi loi
                'filepath' => $filepath . '/' . $subfile . '.zip',
                'filetype' => $filetype,
                'message' => '<a class="btn btn-info width100" href="' . NV_BASE_SITEURL . 'uploads/' . $subfile . '.zip">' . sprintf( $lang_module['taive'], strtoupper( $filetype ) ) . '</a>',
            );
            
            die( json_encode( $data ) );
        }
        else
        {
            $data = array(
                'error' => 1, //bi loi
                'message' => $lang_module['data_file_empty'],
            );
            
            die( json_encode( $data ) );
        }
    }
    else
    {
        $data = array(
            'error' => 1, //bi loi
            'message' => $lang_module['loifile'],
            );
        die( json_encode( $data ) );
    }

    die;
}
exit;