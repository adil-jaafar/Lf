<?php

/**
 * Files: Service de Manipulation de fichiers de LF
 * @link http://lf.goodsenses.net/fw/services/Location
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 2-Clause License 
 * @copyright 2013, Adil JAAFAR
 * @author Adil JAAFAR <jaafar.adil@gmail.com>
 * @created 22/02/2013
 * @modified 
 */

namespace lf\Services;

class Files extends Service {
	
	public function getFilesInDir( $dir , $afterFind = null , $filter = null ) {
		
		if( ! is_callable($filter) ) 	$filter = function($f) { return true; } ;
		if( ! is_callable($afterFind) ) $afterFind = function($f) { return $f; } ;
		
		$files = array();
		if ( $h = opendir( $dir ) ) {
			while ( false !== ($file = readdir($h)) ) {
				if ( "." != $file && ".." != $file && $filter($file) && !is_dir($dir.DS.$file) ) {
					$files[] = $afterFind ( $file );
				}
			}
			closedir( $h );
		}
		return $files;
	}
	
	public function addFromString($path, $content) {
		return file_put_contents($path, $content);
	}

	public function getContent( $path ) {
		return file_get_contents( $path );
	}
	
	public function outFile($filename, $filepath, $contentType = '') {
		header('Content-type: '.$contentType); 
		header('Content-Disposition: attachment; filename="'.$filename.'"'); 
		readfile($filepath);
	}
	
	public function outContent($content, $filename, $contentType) {
		header('Content-type: '.$contentType); 
		header('Content-Disposition: attachment; filename="'.$filename.'"'); 
		echo $content;
		die();
	}

	public function getFile( $filepath , $contentType ) {
		header('Content-type: '.$contentType);
		readfile( $filepath );
	}

	public function getStream( $filepath , $contentType ) {
	    if (is_file($filepath)){
	        header("Content-type: ".$contentType );
	        if (isset($_SERVER['HTTP_RANGE'])){
	            $this->rangeDownload($filepath);
	        } else {
	            header("Content-length: " . filesize($filepath));
	            readfile($filepath);
	        }
	    }
	}

    private function rangeDownload($file){

        $fp = @fopen($file, 'rb');

        $size   = filesize($file);
        $length = $size;     
        $start  = 0;         
        $end    = $size - 1; 
       
        header("Accept-Ranges: 0-$length");
        
        if (isset($_SERVER['HTTP_RANGE'])){
            $c_start = $start;
            $c_end   = $end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            
            if (strpos($range, ',') !== false){
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }
            if ($range{0} == '-'){
                $c_start = $size - substr($range, 1);
            } else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }
            $c_end = ($c_end > $end) ? $end : $c_end;
           	if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size){
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }

            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1;
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }

        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: $length");

        $buffer = 1024 * 8;
        while(!feof($fp) && ($p = ftell($fp)) <= $end){
            if ($p + $buffer > $end){
                $buffer = $end - $p + 1;
            }

            set_time_limit(0);
            echo fread($fp, $buffer);
            flush();
        }

        fclose($fp);
    }
	
}