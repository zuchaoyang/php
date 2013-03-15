<?php
/**
 * 命令集合的抽象类
 */
abstract class Command {
    abstract protected  function onCommand();
    public function errorOut($data) {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($data);
            exit;
        } else {
            header('Location:' . $data['data']['location']);
            exit;
        }
        return false;
    }
}

