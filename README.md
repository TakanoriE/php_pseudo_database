# php_pseudo_database

## CreateTableクラス
### インスタンス作成 
$col = array("id","name","password","permission");
new CreateTable('user', $col);

## Databaseクラス
### インスタンス作成 
$user = new Database('user');

### selectAllメソッド
$user->selectAll();

### insertメソッド
$datas = array("hoge", "abcdef", "0");  // idはオートインクリメントの為、指定不要

if( $user->insert($datas) ){  // 引数にid以外の値を格納した配列

  echo "Success!!<br>"; // 成功時
  
}else{

  echo "Error!!<br>"; //失敗時
  
}

### updateメソッド
$datas = array("hoge", "abcdef", "0"); // idはオートインクリメントの為、指定不要
// 引数にid以外の値を格納した配列と変更するレコードのid

if( $user->update($datas, 4) ){

  echo "Success!!<br>"; // 成功時
  
}else{

  echo "Error!!<br>"; //失敗時
  
}

 
### deleteメソッド
if( $user->delete(5) ){ // 引数に削除するレコードのid
  echo "Success!!<br>"; // 成功時
}else{
  echo "Error!!<br>"; //失敗時
}

### getTableメソッド
$table = $user->getTable();
取得したテーブル内のデータを2次元配列として返す

### getColumnsメソッド
$columns = $user->getColumns();
取得したテーブルのカラム名を配列として返す

### findメソッド
if( $line = $user->find(1) ){ // 引数に取得するレコードのid
  $lines = array();
  array_push($lines, $line);
  $user->dumpLines( $lines );
}else{
  echo "Error!!<br>";
}

### whereメソッド
// 引数にカラム名とその値
$lines = $user->where('name', 'hoge');
if( $lines ){
  $user->dumpLines( $lines );
}

### getIdメソッド
$lines = $user->where('permission', '1');
$ids = $user->getId($lines);
foreach($ids as $id){
  $user->delete($id);
}
レコードの配列を渡すことで、そのレコードのidを返す。

### deleteTableメソッド
$user = new Database('user');
$user->deleteTable();
テーブルに関連するファイル全てが削除されるため注意
