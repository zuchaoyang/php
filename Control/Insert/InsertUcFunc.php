<?php
function insert_uc_header($params = array(), & $smarty) {
    import("@.Control.Insert.InsertUc.InsertUcHeader");
    $insertObj = new InsertUcHeader();

    echo $insertObj->run($params, $smarty);

}

//uc底部页面
function insert_uc_footer($params = array(), & $smarty) {
    import("@.Control.Insert.InsertUc.InsertUcFooter");
    $insertObj = new InsertUcFooter();

    echo $insertObj->run($params, $smarty);
}


