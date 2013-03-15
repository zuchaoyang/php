<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');

//老师
$modules = array(101 => 1,102 => 1,103 => 1,104 => 1,105 => 1,106 => 1,109 => 1,201 => 1,202 => 1,203 => 1,301 => 2);

$uid = 11070004;
foreach ($modules as $module => $action) {
    $i = 1;
    while ($i <=10 ) {
        $param_list = array(
            "module" => $module,
            "action" => $action,
            "uid" => $uid,
        );
        
        
        $param_list = serialize($param_list);
        
        $result = Gearman::send('sns_active', $param_list, PRIORITY_NORMAL, false);
        
        $i++;
    }
}


//学生

$modules = array(101 => 2,102 => 2,103 => 2,104 => 1,105 => 1,106 => 1,201 => 1,202 => 1,203 => 1,301 => 2);

$student_uid = 95469975;
foreach ($modules as $module => $action) {
    $i = 1;
    while ($i <=10 ) {
        $param_list = array(
            "module" => $module,
            "action" => $action,
            "uid" => $student_uid,
        );
        
        
        $param_list = serialize($param_list);
        
        $result = Gearman::send('sns_active', $param_list, PRIORITY_NORMAL, false);
        
        $i++;
    }
}


//家长

$modules_1 = array(101 => 2,102 => 2,103 => 2, 109 => 1, 201 => 1,202 => 1, 203 => 1,301 => 2);
//$modules = array(101 => 2);

$family_uid = 95469974;
foreach ($modules_1 as $module => $action) {
    $i = 1;
    while ($i <=10 ) {
        $param_list = array(
            "module" => $module,
            "action" => $action,
            "uid" => $family_uid,
        );
        
        
        $param_list = serialize($param_list);
        
        $result = Gearman::send('sns_active', $param_list, PRIORITY_NORMAL, false);
        
        $i++;
    }
}
