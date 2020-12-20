<?php
class CreateTable{
  // コンストラクタ-------------------------------------------------
  public function __construct($name, $columns){
    $dir = './database/'.$name.'/';
    if( !( is_dir($dir) ) ){
      if( !( is_dir('./database') ) ){
        mkdir('./database');
      }
      mkdir($dir, 0666);
    }

    $filePath = $dir.$name.'.csv';
    if( !( file_exists($filePath) ) ){
      touch($filePath);
      $file = $this->fileLockStart($filePath, "w");
      if ( $file ) {
        fputcsv($file, $columns);
      }
      $this->fileLockEnd($file);
    }

    $counterFp = $dir.'counter.txt';
    if( !( file_exists($counterFp) ) ){
      touch($counterFp);
      $file = $this->fileLockStart($counterFp, "w");
      if ( $file ) {
        $c = array("0");
        fputcsv($file, $c);
      }
      $this->fileLockEnd($file);
    }
  }
  // ---------------------------------------------------------------

  // ファイルロックの開始---------------------------------------------
  public function fileLockStart($fp, $type){
    $file = fopen($fp, $type);
    if( $type == "r" ){
      flock($file, LOCK_SH);
    }elseif( $type == "w" ){
      flock($file, LOCK_EX);
    }
    return $file;
  }
  // ---------------------------------------------------------------

  // ファイルロックの解除---------------------------------------------
  public function fileLockEnd($file){
    flock($file, LOCK_UN);
    fclose($file);
  }
  // ---------------------------------------------------------------
}
?>
