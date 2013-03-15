<?php
/**
 * 
 * @param $params
 * @param $smarty
 */
function insert_amscontrol_menu($params = array(), & $smarty) {
    import("@.Control.Insert.InsertAms.InsertAmsForAmscontrolMenu");
    $insertObj = new InsertAmsForAmsControlMenu();
    
    echo $insertObj->run($params, $smarty);
}