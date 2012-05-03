<?php
    set_time_limit(0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    if(!isset($_GET['id']) or !is_numeric($_GET['id'])){ die(); }
    include 'include/simplepie.inc';
    $simple = new SimplePie();
    $simple->enable_cache(false);
    $simple->set_useragent('Mozilla/4.0 '.SIMPLEPIE_USERAGENT.' (with Simple RSS Reader)');
    $sqlite = new PDO('sqlite:include/data.db');
    $sqlite->query('DELETE FROM items WHERE read="1" AND user_id="' . $_GET['id'] . '"');
    $sqlite->query('VACUUM');
    $query = $sqlite->query('SELECT id,url FROM feeds WHERE user_id="' . $_GET['id'] . '"');
    while($response = $query->fetch()){
        $ddate = $sqlite->query('SELECT last_check FROM feeds WHERE id="' . $response['id'] . '" AND user_id="' . $_GET['id'] . '"');
        $ddate = $ddate->fetch();
        $ddate = $ddate[0];
        if(time() > $ddate + 1200){
            $simple->set_feed_url($response['url']);
            $simple->init();
            $simple->handle_content_type();
            foreach($simple->get_items() as $item){
                $maxid = $sqlite->query('SELECT max(id) FROM items');
                $maxid = $maxid->fetch();
                $maxid = $maxid[0] + 1;
                $permalink = $sqlite->quote($item->get_permalink());
                $title = $sqlite->quote($item->get_title());
                $description = $sqlite->quote($item->get_content());
                if(empty($description)){
                    $description = $sqlite->quote($item->get_description());
                }
                $date = strtotime($item->get_date());
                if($date < $ddate){ break; }
                else{
                    $date = $sqlite->quote($date);
                    $sqlite->query('INSERT INTO items VALUES(' . $maxid . ',' . $response['id'] . ',' . $title . ',' . $permalink . ',' . $description . ',' . $date . ',\'0\',' . $_GET['id'] . ')');   
                }
            }
            $sqlite->query('UPDATE feeds SET last_check=' . $sqlite->quote(time()) . ' WHERE id=' . $sqlite->quote($response['id']));
        }
    }
    header('Location: index.php');
?>