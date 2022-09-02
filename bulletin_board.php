<!DOCTYPE html>
<html lang = "ja">
    <head>
        <meta charset = "UTF-8">
        <title>簡易掲示板</title>
    </head>
    <body>
    
    <?php
        // DB接続設定
        //DSN(Data Source Name)を指定
        $dsn = 'mysql:dbname=****;host=localhost';
        //ユーザー名を指定
        $user = '****';
        //パスワードを指定
        $password = 'PASSWORD';
        //PDO(PHP Data Objects)を使用して、PHPからデータベースにアクセスしている
        //array以降は上手くいかなかった時に警告をするために記載している
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
        //テーブルの作成(IF NOT EXISTSは２回目以降に同じテーブルを作成しないようにしている)
        $sql = "CREATE TABLE IF NOT EXISTS bulletin_board"
        ."("
        //AUTO_INCREMENT:データ型は整数型,カラムに指定なしの時にidを1ずつ増加する
        //PRIMARY KEY(主キー):テーブル内でレコード(行)を一意に識別することができるように指定される列。重複もNULLも格納不可。
        //idは自動で登録される
        ."id INT AUTO_INCREMENT PRIMARY KEY,"
        //名前を入れるカラム(項目)(32文字)
        ."name char(32),"
        //コメントを入れるカラム(項目)
        ."comment TEXT,"
        //時刻を入れるカラム(項目)
        ."date datetime,"
        //パスワードを入れるカラム(項目)
        ."pass char(32)"
        .");";
        //queryメソッドでSQLを実行
        $stmt = $pdo->query($sql);

        //投稿機能

        //フォームに入力・送信された時
        if(!empty($_POST["name"]) && !empty($_POST["comment"])){
            //入力された名前を受信
            $name = $_POST["name"];
            //入力されたコメントを受信
            $comment = $_POST["comment"];
            //入力されたパスワードを受信
            $post_pass = $_POST["post_pass"];
            //タイムスタンプを変数に代入
            $date = date("Y-m-d H:i:s");
            
            //新規投稿か編集か判断(編集対象番号が空かどうか)
            //新規投稿の時
            if(empty($_POST["edit_out"])){
                //パスワードが正しいか判断(現在のパスワードはpassとなっている)
                if($_POST["post_pass"] == "pass"){
                    //テーブルにデータを入力する
                    //prepareメソッドでSQLを取得している
                    //insert文でデータを登録する列名と内容を指定している
                    //形はINSERT INTO テーブル名 (列名1, 列名2,...) VALUES (値1, 値2,...);
                    $sql = $pdo -> prepare("INSERT INTO bulletin_board (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)");
                    //bindParamを使って、指定された変数に値をバインド(埋め込み)する
                    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                    $sql -> bindParam(':pass', $post_pass, PDO::PARAM_STR);
                    //SQLを実行
                    //executeはprepareで作成された文を実行する
                    $sql -> execute();
                //パスワードのフォームに何も記入されていない場合
                }else if(empty($_POST["post_pass"])){
                    echo "パスワードが記入されていません";
                //パスワードが一致しない場合
                }else if($_POST["post_pass"] != "pass"){
                    echo "パスワードが正しくありません";
                }
            }
            //編集の時
            else{
                //パスワードが正しいか判断(現在のパスワードはpassとなっている)
                if($_POST["post_pass"] == "pass"){
                    //編集する投稿番号を取得
                    $edit_out = $_POST["edit_out"];
                    //編集する投稿番号
                    $id = $edit_out;
                    //指定した投稿番号の投稿内容を上書きして編集する
                    $sql = 'UPDATE bulletin_board SET name=:name,comment=:comment,date=:date WHERE id=:id';
                    //prepareメソッドでSQLを取得
                    $stmt = $pdo->prepare($sql);
                    //bindParamを使って、指定された変数に値をバインド(繋げる)する
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    //SQLを実行
                    //executeはprepareで作成された文を実行する
                    $stmt->execute();
                //パスワードのフォームに何も記入されていない場合
                }else if(empty($_POST["post_pass"])){
                    echo "パスワードが記入されていません";
                //パスワードが一致しない場合
                }else if($_POST["post_pass"] != "pass"){
                    echo "パスワードが正しくありません";
                }
            }
        //名前のフォームに何も記入されていない場合
        }else if(empty($_POST["name"]) && !empty($_POST["comment"])){
            echo "名前が記入されていません";
        //コメントのフォームに何も記入されていない場合
        }else if(!empty($_POST["name"]) && empty($_POST["comment"])){
            echo "コメントが記入されていません";
        }
        //編集対象番号が入力・送信された時(edit_inが入力・送信された時)
        if(!empty($_POST["edit_in"])){
            //パスワードが正しいか判断(現在のパスワードはpassとなっている)
            if($_POST["edit_pass"] == "pass"){
                //入力された編集対象番号を取得
                $edit_in = $_POST["edit_in"];
                //SELECT文でテーブルからデータを抽出する
                //'*'はテーブルの中身全てを指定している
                $sql = 'SELECT * FROM bulletin_board';
                //queryメソッドでSQLを実行
                $stmt = $pdo->query($sql);
                //取得したデータを全て一括で配列に取り込む
                $edit_array = $stmt->fetchAll();
                //ファイルの中身を１行ずつループして編集したい行を探す
                foreach($edit_array as $row){
                    //編集対象番号と行番号が一致するか
                    if($row['id'] == $edit_in){
                        //Yes：行内容を送信テキストボックスに出力する
                        $id = $row['id'];
                        $newname = $row['name'];
                        $newcomment = $row['comment'];
                    }
                }
            //パスワードのフォームに何も記入されていない場合
            }else if(empty($_POST["edit_pass"])){
                echo "パスワードが記入されていません";
            //パスワードが一致しない場合
            }else if($_POST["edit_pass"] != "pass"){
                echo "パスワードが正しくありません";
            }
        }
        //削除対象番号が入力・送信された時(deleteが入力・送信された時)
        if(!empty($_POST["delete"])){
            if($_POST["delete_pass"] == "pass"){
                //入力された削除対象番号を受信
                $delete = $_POST["delete"];
                //削除する投稿番号
                $id = $delete;
                //指定した投稿番号の投稿内容を削除する
                $sql = 'delete from bulletin_board where id=:id';
                //prepareメソッドでSQLを取得
                $stmt = $pdo->prepare($sql);
                //bindParamを使って、指定された変数に値をバインド(繋げる)する
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                //SQLを実行
                //executeはprepareで作成された文を実行する
                $stmt->execute();
            //パスワードのフォームに何も記入されていない場合
            }else if(empty($_POST["delete_pass"])){
                echo "パスワードが記入されていません";
            //パスワードが一致しない場合
            }else if($_POST["delete_pass"] != "pass"){
                echo "パスワードが正しくありません";
            }
        }
    ?>
        <form action="" method="post">
        <!--名前を入力するフォームを作成-->
        <input type="text" name="name" placeholder="名前" value = "<?php if(isset($newname)){echo $newname;}?>"><br>
        <!--コメントを入力するフォームを作成-->
        <input type="text" name="comment" placeholder = "コメント" value ="<?php if(isset($newcomment)){echo $newcomment;}?>"><br>
        <!--パスワードを入力するフォームを作成-->
        <input type="password" name="post_pass" placeholder="パスワード">
        <!--編集対象番号を表示するフォームを作成-->
        <input type="hidden" name="edit_out" value = "<?php if(isset($edit_in)){echo $edit_in;} ?>">
        <!--送信ボタンを作成-->
        <input type="submit" name="submit"><br><br>
        <!--削除対象番号を入力するフォームを作成-->
        <input type="number" name="delete" placeholder = "削除対象番号"><br>
        <!--パスワードを入力するフォームを作成-->
        <input type="password" name="delete_pass" placeholder="パスワード">
        <!--削除ボタンを作成-->
        <input type="submit" name="delete_submit" value="削除"><br><br>
        <!--編集対象番号を入力するフォームを作成-->
        <input type="number" name="edit_in" placeholder = "編集対象番号"><br>
        <!--パスワードを入力するフォームを作成-->
        <input type="password" name="edit_pass" placeholder="パスワード">
        <!--編集ボタンを作成-->
        <input type="submit" name="edit_submit" value="編集">
    </form>
    <?php
            //テーブルの内容を表示する
            //タイトル表示
            echo "【投稿一覧】";
            //水平の横線を引く
            echo "<hr>";
            //SELECT文でテーブルからデータを抽出する
            //'*'はテーブルの中身全てを指定している
            $sql = 'SELECT * FROM bulletin_board';
            //queryメソッドでSQLを実行
            $stmt = $pdo->query($sql);
            //SQLで検索したデータを全て一括で配列に取り込む
            $result_array = $stmt->fetchAll();
            //配列を１行(row)ずつ配列に入れる
            foreach ($result_array as $row){
                //１行ずつ表示する
                //$rowの中にはテーブルのカラム名が入る
                echo $row['id'].'.';
                echo $row['name'].' : ';
                echo $row['comment'].' | ';
                echo $row['date'].'<br>';
                //水平の横線を引く
                echo "<hr>";
            }
    ?>
    </body>
</html>
