<?php
//单张相片 异步删除相片实体
     function del_photo_entity_by_photo_id($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');
        $photo_id = (array)$photo_id;
        $photo_id = serialize($photo_id);
       
        $result = Gearman::send('del_photo_entity', $photo_id, PRIORITY_LOW);
        //var_dump($result);
    }
del_photo_entity_by_photo_id(130);

