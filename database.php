<?php
class Database{
  private $table;
  private $columns;
  private $colNum;
  private $dir;
  private $fp;
  private $counterFp;

  // コンストラクタ-------------------------------------------------
  public function __construct($name){
    $this->dir = './database/'.$name.'/';
    $this->fp = $this->dir.$name.'.csv';
    $this->counterFp = $this->dir.'counter.txt';
    $this->setTable();
  }
  // ---------------------------------------------------------------

  // 内部処理-------------------------------------------------------------
  // $this->table,$this->columnsに値をセット-------------------------
  private function setTable(){
    $this->table = array();
    $this->columns = array();

    $file = $this->fileLockStart($this->fp, "r");
    
    $isFirst = true;
    while($line = fgetcsv($file)){
      if( $isFirst ){ // 1行目でカラム取得
        foreach($line as $l){
          array_push($this->columns, $l);
        }
        $isFirst = false;
      }else{ // 2行目以降でデータ取得
        $temp = array();
        for( $i=0;$i<count($line);$i++ ){
          $temp[ $this->columns[$i] ] = $line[$i];
        }
        array_push($this->table, $temp);
      }
    }

    $this->fileLockEnd($file);

    $this->colNum = count($this->columns);
  }
  // ---------------------------------------------------------------

  // ファイルロックの開始---------------------------------------------
  private function fileLockStart($fp, $type){
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
  private function fileLockEnd($file){
    flock($file, LOCK_UN);
    fclose($file);
  }
  // ---------------------------------------------------------------

  // 追加判断--------------------------------------------------------
  private function validate($datas){
    if( count($datas) != $this->colNum - 1 ){ //要素数検証
      return true;
    }
    return false;
  }
  // ---------------------------------------------------------------
  // --------------------------------------------------------------------

  // レコードを追加---------------------------------------------------
  public function insert($datas){
    if( $this->validate($datas) ){
      return false;
    }

    // 自動インクリメント--------------------------------
    $file = $this->fileLockStart($this->counterFp, "r");
    $id = (int)fgets($file);
    $this->fileLockEnd($file);

    $id++;

    $file = $this->fileLockStart($this->counterFp, "w");
    fputs($file, $id);
    $this->fileLockEnd($file);
    // --------------------------------------------------

    $values = array();
    $values[ $this->columns[0] ] = (string)$id;
    for( $i=1;$i<$this->colNum;$i++ ){
      $values[ $this->columns[$i] ] = $datas[ $i-1 ];
    }

    $this->setTable();
    array_push($this->table, $values);

    $file = $this->fileLockStart($this->fp, "w");
    if ( $file ) {
      fputcsv($file, $this->columns);
      foreach($this->table as $line){
        fputcsv($file, $line);
      } 
    }
    $this->fileLockEnd($file);

    return true;
  }
  // ---------------------------------------------------------------

  // 指定したレコードの値を更新----------------------------------------
  public function update($datas, $id){
    if( $this->validate($datas) ){
      return false;
    }

    $values = array();
    $values[ $this->columns[0] ] = (string)$id;
    for( $i=1;$i<$this->colNum;$i++ ){
      $values[ $this->columns[$i] ] = $datas[ $i-1 ];
    }

    $c = 0;
    $this->setTable();
    $file = $this->fileLockStart($this->fp, "w");
    foreach($this->table as $line){
      if( $line['id'] == $id ){
        break;
      }
      $c++;
    }
    if( isset($this->table[$c]) ){
      for( $i=1;$i<$this->colNum;$i++ ){
        $this->table[$c][ $this->columns[$i] ] = $datas[ $i-1 ];
      }
    }
    if ( $file ) {
      fputcsv($file, $this->columns);
      foreach($this->table as $line){
        fputcsv($file, $line);
      } 
    }
    $this->fileLockEnd($file);

    return true;
  }
  // ---------------------------------------------------------------

  // 指定したレコードを削除--------------------------------------------
  public function delete($id){
    $isIdFlag = false;

    $this->setTable();
    $file = $this->fileLockStart($this->fp, "w");
    foreach($this->table as $line){
      if( $line['id'] == $id ){
        $isIdFlag = true;
        break;
      }
    }
    if( $isIdFlag && $file ){
      fputcsv($file, $this->columns);
      foreach($this->table as $line){
        if( $line['id'] == $id ){
          continue;
        }
        fputcsv($file, $line);
      }
    }
    $this->fileLockEnd($file);

    if( $isIdFlag ){
      return true;
    }else{
      return false;
    }
  }
  // ---------------------------------------------------------------

  // 取得処理-------------------------------------------------------------
  // テーブルの取得---------------------------------------------------
  public function getTable(){
    return $this->table;
  }
  // ---------------------------------------------------------------

  // 列名を取得------------------------------------------------------
  public function getColumns(){
    return $this->columns;
  }
  // ---------------------------------------------------------------

  // 一行ごと全てのデータ表示------------------------------------------
  public function selectAll(){
    echo '-----------------------------------------------------------<br>';
    foreach($this->getTable() as $line){
      foreach($this->getColumns() as $col){
        echo $col.' : '.$line[$col].'<br>';
      }
      echo '-----------------------------------------------------------<br>';
    }
  }
  // ---------------------------------------------------------------

  // 行ごとに表示----------------------------------------------------
  public function dumpLines($lines){
    echo '-----------------------------------------------------------<br>';
    foreach($lines as $line){
      foreach($this->getColumns() as $col){
        echo $col.' : '.$line[$col].'<br>';
      }
      echo '-----------------------------------------------------------<br>';
    }
  }
  // ---------------------------------------------------------------

  // 任意のレコードを取得する------------------------------------------
  public function find($id){
    $this->setTable();
    foreach($this->table as $line){
      if( $line['id'] == $id ){
        return $line;
      }
    }
    return false;
  }
  // ---------------------------------------------------------------

  // カラム名と値を指定しそれに該当するレコードを取得する----------------
  public function where($col, $value){
    $lines = array();
    $this->setTable();
    foreach($this->table as $line){
      if( $line[$col] == $value ){
        array_push($lines, $line);
      }
    }
    
    if( $lines != null ){
      return $lines;
    }else{
      return false;
    }
  }
  // ---------------------------------------------------------------

  // 複数のレコードを配列で渡し、そのidを返す---------------------------
  public function getId($lines){
    $ids = array();
    foreach($lines as $line){
      array_push($ids, $line['id']);
    }
    return $ids;
  }
  // ---------------------------------------------------------------
  // --------------------------------------------------------------------

  // テーブルの削除-------------------------------------------------------
  public function deleteTable(){
    unlink($this->fp);
    unlink($this->counterFp);
    if( is_dir($this->dir) ){
      rmdir($this->dir);
    }
  }
  // --------------------------------------------------------------------
}
?>
