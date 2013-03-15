<?php
include_once WEB_ROOT_DIR . "/Control/Sns/Insert/InsertInterface.php";
class InsertSnsNav implements InsertInterface {
    public function run($params, & $smarty) {
    	return $smarty->fetch("./Public/sns_nav.html");
    }
}