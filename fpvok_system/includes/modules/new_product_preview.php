<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: new_product_preview.php 3009 2006-02-11 15:41:10Z wilt $
 */
if (! defined ( 'IS_ADMIN_FLAG' )) {
  die ( 'Illegal Access' );
}
$file = 'F:\1.jpg';//$_FILES;
// copy image only if modified
$fileArray = array_keys($_FILES);

array_shift($fileArray);

for($i = 0; $i < 20; $i ++) {
    if ($_POST ['existimg_' . $i]) {
        $existimgArray[] = $_POST ['existimg_' . $i];
    }
}

require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'extra_functions/functions_bmz_io.php');
$watermark = DIR_FS_CATALOG_IMAGES.'watermark.png';
$wateralpha = 65;
if (! isset ( $_GET ['read'] ) || $_GET ['read'] == 'only') {
  if (count($_FILES) > 0) {
    $data = array();
    for($i = count ( $fileArray )-1; $i >=0; $i --) {
    	$setSubFolder = date('d')<16?'a':'b';
        $nameBase = date('Ym').'/'.$setSubFolder.'/'.time().$i;

        $data['ImgExtension'] = strtolower(substr( $_FILES[$fileArray[$i]]['name'], strrpos($_FILES[$fileArray[$i]]['name'], '.')));
        $source_name = $_FILES[$fileArray[$i]]['tmp_name'];
        
        //Normal images
        $destination_name = DIR_FS_CATALOG_IMAGES . 's/'.$nameBase . $data['ImgExtension'];
        io_makeFileDir($destination_name);
		      
        if ( !move_uploaded_file($source_name, $destination_name) ) {
          $messageStack->add( TEXT_MSG_NOUPLOAD_LARGE, "error" );
        }else{
          $messageStack->add( TEXT_MSG_UPLOAD_LARGE, "success" );          
        }        
		//die("11");
		$data['originalFileName'] = 'vold/'.$nameBase . $data['ImgExtension'];
        $data['smallFileName'] = 's/'.$nameBase . $data['ImgExtension'];
        $data['mediumFileName'] = 'l/'.$nameBase . $data['ImgExtension'];
        //print_r($data['mediumFileName']);
        $data['largeFileName'] = 'v/'.$nameBase . $data['ImgExtension'];
        //print_r($data['largeFileName']);
        $destination_name_original = DIR_FS_CATALOG_IMAGES .$data['originalFileName'];
        $destination_name_small = DIR_FS_CATALOG_IMAGES .$data['smallFileName'];
        $destination_name_medium = DIR_FS_CATALOG_IMAGES .$data['mediumFileName'];
        $destination_name_large = DIR_FS_CATALOG_IMAGES .$data['largeFileName'];
        io_makeFileDir($destination_name_medium);
        io_makeFileDir($destination_name_large);
        io_makeFileDir($destination_name_original);
       
        //copy original image
        if (!copy($destination_name, $destination_name_original))
        {
        	$messageStack->add('failed to copy '.$destination_name_original.'...', "error" );
        }
        else
        {
        	$messageStack->add('Successed to copy '.$destination_name_original.'...', "success" );
        } 

        if (!copy($destination_name, $destination_name_medium)) {
          $messageStack->add('failed to copy '.$destination_name_medium.'...', "error" );
        }else{
          $messageStack->add('Successed to copy '.$destination_name_medium.'...', "success" );
          if ($data['ImgExtension'] == ".jpg" || $data['ImgExtension'] == ".jpeg")
            $im = @imagecreatefromjpeg ($destination_name_medium );
          if ($data['ImgExtension'] == ".gif")
            $im = @imagecreatefromgif ($destination_name_medium );
            var_dump($im);
          if ($data['ImgExtension'] == ".png")
            $im = @imagecreatefrompng ($destination_name_medium );
          list($width_orig, $height_orig) = getimagesize($destination_name_medium );
          $width = PRODUCT_MEDIUM_IMAGE_WIDTH;
          $height = PRODUCT_MEDIUM_IMAGE_HEIGHT;
          if ($width && ($width_orig < $height_orig)) {
              $width = ($height / $height_orig) * $width_orig;
          } else {
              $height = ($width / $width_orig) * $height_orig;
          }

          $im_s = imagecreatetruecolor(PRODUCT_MEDIUM_IMAGE_WIDTH,PRODUCT_MEDIUM_IMAGE_HEIGHT);
          $white = imagecolorallocate($im_s, 255, 255, 255);
          imagefill($im_s, 0, 0, $white);
          if ($width_orig <= $width){
            $wpos=(PRODUCT_MEDIUM_IMAGE_WIDTH - $width_orig)/2;
            $hpos=(PRODUCT_MEDIUM_IMAGE_HEIGHT - $height_orig)/2;
            imagecopy($im_s,$im,$wpos, $hpos, 0, 0,$width_orig,$height_orig );
          }else{
            $wpos=(PRODUCT_MEDIUM_IMAGE_WIDTH - $width)/2;
            $hpos=(PRODUCT_MEDIUM_IMAGE_HEIGHT - $height)/2;
            imagecopyresampled($im_s,$im,$wpos, $hpos, 0, 0,$width,$height,$width_orig,$height_orig );
          }

          //imagecopyresampled($im_s, $im, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig );
          imagejpeg ( $im_s, $destination_name_medium, 100 );
          imagedestroy ( $im );       
        }
        
        
        
        if (!copy($destination_name, $destination_name_large)) {
          $messageStack->add('failed to copy '.$destination_name_large.'...', "error" );
        }else{
          $messageStack->add('Successed to copy '.$destination_name_large.'...', "success" );
          if ($data['ImgExtension'] == ".jpg" || $data['ImgExtension'] == ".jpeg")
            $im = @imagecreatefromjpeg ($destination_name_large );
          if ($data['ImgExtension'] == ".gif")
            $im = @imagecreatefromgif ($destination_name_large );
          if ($data['ImgExtension'] == ".png")
            $im = @imagecreatefrompng ($destination_name_large );
          list($width_orig, $height_orig) = getimagesize($destination_name_large );
          $width = PRODUCT_MEDIUM_LARGE_WIDTH;
          $height = PRODUCT_MEDIUM_LARGE_HEIGHT;
          if ($width && ($width_orig < $height_orig)) {
              $width = ($height / $height_orig) * $width_orig;
          } else {
              $height = ($width / $width_orig) * $height_orig;
          }
          $im_s = @imagecreatetruecolor ( PRODUCT_MEDIUM_LARGE_WIDTH, PRODUCT_MEDIUM_LARGE_HEIGHT );
          $white = imagecolorallocate($im_s, 255, 255, 255);
          imagefill($im_s, 0, 0, $white);
          if ($width_orig <= $width){
	          $wpos=(PRODUCT_MEDIUM_LARGE_WIDTH - $width_orig)/2;
	          $hpos=(PRODUCT_MEDIUM_LARGE_HEIGHT - $height_orig)/2;
	          imagecopy($im_s,$im,$wpos, $hpos, 0, 0,$width_orig,$height_orig );
          }else{
          	$wpos=(PRODUCT_MEDIUM_LARGE_WIDTH - $width)/2;
            $hpos=(PRODUCT_MEDIUM_LARGE_HEIGHT - $height)/2;
			//imagecopyresized($im_s,$im,$wpos, $hpos, 0, 0,$width,$height,$width_orig,$height_orig );
            imagecopyresampled($im_s,$im,$wpos, $hpos, 0, 0,$width,$height,$width_orig,$height_orig );
          }
          $im_watermark = @imagecreatefrompng ($watermark );
          list($water_w, $water_h) = getimagesize($watermark);
          $dst_x = (PRODUCT_MEDIUM_LARGE_WIDTH - $water_w)/2;
          $dst_y = (PRODUCT_MEDIUM_LARGE_HEIGHT - $water_h)/2;
          imagecopymerge($im_s, $im_watermark, $dst_x, $dst_y, 0, 0,$water_w, $water_h,$wateralpha);
          @imagejpeg ( $im_s, $destination_name_large, 100 );
          @imagedestroy ( $im );        
        }
        
        if ($data['ImgExtension'] == ".jpg" || $data['ImgExtension'] == ".jpeg")
          $im = @imagecreatefromjpeg ($destination_name_small );
        if ($data['ImgExtension'] == ".gif")
          $im = @imagecreatefromgif ($destination_name_small );
        if ($data['ImgExtension'] == ".png")
          $im = @imagecreatefrompng ($destination_name_small );
        list($width_orig, $height_orig) = getimagesize($destination_name_small);
        $width = PRODUCT_MEDIUM_SMALL_WIDTH;
        $height = PRODUCT_MEDIUM_SMALL_HEIGHT;
        if ($width && ($width_orig < $height_orig)) {
            $width = ($height / $height_orig) * $width_orig;
        } else {
            $height = ($width / $width_orig) * $height_orig;
        }
        $im_s = @imagecreatetruecolor( PRODUCT_MEDIUM_SMALL_WIDTH, PRODUCT_MEDIUM_SMALL_HEIGHT ) or die("Cannot Initialize new GD image stream");
        $white = imagecolorallocate($im_s, 255, 255, 255);
        imagefill($im_s, 0, 0, $white);
        if ($width_orig <= $width){
            $wpos=(PRODUCT_MEDIUM_SMALL_WIDTH - $width_orig)/2;
            $hpos=(PRODUCT_MEDIUM_SMALL_HEIGHT - $height_orig)/2;
            imagecopy($im_s,$im,$wpos, $hpos, 0, 0,$width_orig,$height_orig );
        }else{
            $wpos=(PRODUCT_MEDIUM_SMALL_WIDTH - $width)/2;
            $hpos=(PRODUCT_MEDIUM_SMALL_HEIGHT - $height)/2;
            imagecopyresampled($im_s,$im,$wpos, $hpos, 0, 0,$width,$height,$width_orig,$height_orig );
        }
        imagejpeg ( $im_s, $destination_name_small, 100 );
        
        imagedestroy ( $im );
        $products_image_names[] = 's/'.$nameBase.$data['ImgExtension'];
        
    }
    if(is_array($existimgArray)){
      if(is_array($products_image_names)){
        $newArray = array_merge($existimgArray,$products_image_names);
        $products_image_name = $newArray[0];
        $products_image_name_string = implode ( ',', $newArray);
      }else{
        $products_image_name = $existimgArray[0];
        $products_image_name_string = implode ( ',', $existimgArray);
      }
    }else{
      $products_image_name = $products_image_names[0];
      $products_image_name_string = @implode ( ',', $products_image_names);
    }
    
    }

  }else{
    if(is_array($existimgArray)){
    $products_image_name = $existimgArray[0];
    $products_image_name_string = implode ( ',', $existimgArray);
    }else{
    $products_image_name = $existimgArray[0];
    $products_image_name_string = $existimgArray[0];
    }
    
  }
?>  
  