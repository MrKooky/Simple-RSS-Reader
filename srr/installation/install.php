<?php
    if(!empty($_POST['pseudo']) AND !empty($_POST['pwd1']) AND !empty($_POST['pwd2'])){
        if($_POST['pwd1'] == $_POST['pwd2']){
            $sqlite = new PDO('sqlite:../include/data.db');
            $user = $sqlite->quote($_POST['pseudo']);
            $pass = $sqlite->quote(sha1($_POST['pseudo'] . $_POST['pwd1'] . $_POST['pseudo']));
	    $sqlite->query('DELETE FROM users');
            $sqlite->query('INSERT INTO users VALUES("0",' . $user . ',' . $pass . ',"1","defaut")');
             
            header('Location: index.php?ok');
        }
        else{
            header('Location: index.php?err');
        }
    }
    else{
        header('Location: index.php?err');
    }
?>