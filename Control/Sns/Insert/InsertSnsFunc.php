<?php 
function insert_sns_header($params = array(), & $smarty) {
    import("@.Control.Sns.Insert.InsertSns.InsertSnsHeader");
    
    $insertObj = new InsertSnsHeader();
    echo $insertObj->run($params, $smarty);
}

function insert_sns_class_header($params = array(), & $smarty) {
    import("@.Control.Sns.Insert.InsertSns.InsertSnsTip");
    $insertObj = new InsertSnsTip();
    echo $insertObj->run($params, $smarty);
}

function insert_sns_nav($params = array(), & $smarty){
    import("@.Control.Sns.Insert.InsertSns.InsertSnsNav");
    $insertObj = new InsertSnsNav();
    echo $insertObj->run($params, $smarty);
}

function insert_sns_person_second_header($params = array(), & $smarty){
    import("@.Control.Sns.Insert.InsertSns.InsertSnsPersonSecondHeader");
    $insertObj = new InsertSnsPersonSecondHeader();
    echo $insertObj->run($params, $smarty);
}

function insert_sns_resource_nav($params = array(), & $smarty){
    import("@.Control.Sns.Insert.InsertSns.InsertSnsResourceNav");
    $insertObj = new InsertSnsResourceNav();
    echo $insertObj->run($params, $smarty);
}


/**
 * 2.0老的
 */
function insert_account_left($params = array(), & $smarty) {
    import("@.Control.Insert.InsertSns.InsertOldSnsForAccountLeft");
    $insertObj = new InsertSnsForAccountLeft();
    
    echo $insertObj->run($params, $smarty);
}


function insert_publicHeader($params = array(), & $smarty) {
    import("@.Control.Insert.InsertSns.InsertOldSnsHeader");
    $insertObj = new InsertSnsHeader();
    
    echo $insertObj->run($params, $smarty);
}

function insert_space_header($params = array(), & $smarty) {
    import("@.Control.Insert.InsertSns.InsertOldSnsForSpaceHeader");
    $insertObj = new InsertSnsForSpaceHeader();
    echo $insertObj->run($params, $smarty);
}