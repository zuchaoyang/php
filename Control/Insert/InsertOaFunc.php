<?php 
function insert_oa_header($params = array(), & $smarty) {
    import("@.Control.Insert.InsertOa.InsertOaHeader");
    $insertObj = new InsertOaHeader();
    
    echo $insertObj->run($params, $smarty);
}

function insert_oa_left($params = array(), & $smarty) {
    import("@.Control.Insert.InsertOa.InsertOaLeft");
    $insertObj = new InsertOaLeft();
    
    echo $insertObj->run($params, $smarty);
}

function insert_oa_right($params = array(), & $smarty) {
    import("@.Control.Insert.InsertOa.InsertOaRight");
    $insertObj = new InsertOaRight();
    
    echo $insertObj->run($params, $smarty);
}