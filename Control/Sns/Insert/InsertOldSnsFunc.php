<?php 
function insert_account_left($params = array(), & $smarty) {
    import("@.Control.Insert.InsertSns.InsertSnsForAccountLeft");
    $insertObj = new InsertSnsForAccountLeft();
    
    echo $insertObj->run($params, $smarty);
}


function insert_publicHeader($params = array(), & $smarty) {
    import("@.Control.Insert.InsertSns.InsertSnsHeader");
    $insertObj = new InsertSnsHeader();
    
    echo $insertObj->run($params, $smarty);
}

function insert_space_header($params = array(), & $smarty) {
    import("@.Control.Insert.InsertSns.InsertSnsForSpaceHeader");
    $insertObj = new InsertSnsForSpaceHeader();
    echo $insertObj->run($params, $smarty);
}