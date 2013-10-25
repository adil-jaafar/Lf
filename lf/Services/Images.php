<?php

/**
 * Images: Service de Manipulation des images de LF
 * @link http://lf.goodsenses.net/fw/services/Location
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 22/07/2013
 * @modified 
 */

namespace lf\Services;

class Images extends Service {

    public function createThumbnail( $filepath , $thumbnailpath , $width , $height = 0 , $format = "image/jpeg" ) {
        
        switch( $format ){
            case 'image/gif';
                $source_image = imagecreatefromgif($filepath);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($filepath);
                break;
            default:
                $source_image = imagecreatefromjpeg($filepath);
                 
                break;
        }

        $src_width = imagesx($source_image);
        $src_height = imagesy($source_image);

        if( 0 == $height ) $height = floor( $src_height * ($width / $src_width) );

        $virtual_image = imagecreatetruecolor( $width , $height );
        $white = imagecolorallocate($virtual_image, 255, 255, 255); 
        imagefill($virtual_image,0,0,$white);
        /*
        switch ( $format ){
            case "image/png":
                $background = imagecolorallocate($virtual_image, 255, 255, 255);
                imagecolortransparent($virtual_image, $background);
                imagealphablending($virtual_image, false);
                imagesavealpha($virtual_image, true);
                break;
            case "image/gif":
                $background = imagecolorallocate($virtual_image, 255, 255, 255);
                imagecolortransparent($virtual_image, $background);
                break;
        }
        */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $width, $height, $src_width, $src_height );

        imagejpeg($virtual_image, $thumbnailpath, 100) ;
        chmod( $thumbnailpath , 0770 );

    }

}