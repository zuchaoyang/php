<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertUcFooter implements  InsertInterface {
    public function run($params, &$smarty) {
        return $smarty->fetch('./Uc/uc_footer.html');
    }
}