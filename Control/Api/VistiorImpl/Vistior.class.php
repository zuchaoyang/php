<?php
class Vistior{
    
    //统计所有访客
    public function total_count($client_account) {
        $wheresql = array(
            'uid = ' . $client_account,
            'timeline <= ' . time(),
        );
        
        $mPersonVistior = ClsFactory::Create('Model.PersonVistior.mPersonVistior');
        $vistior_list = $mPersonVistior->getPersonVistiorInfo($wheresql);
        $total_count = empty($vistior_list) ? 0 : count($vistior_list);
        
        return $total_count;
    }
    
    
    //统计今日访客
    public function total_count_day($client_account) {
        
         $wheresql_day = array(
            'uid = ' . $client_account,
             'timeline>= '. strtotime(date('Y-m-d',time())),
             'timeline< '. (strtotime(date('Y-m-d',time())) + 24*3600)
        );
        
        $mPersonVistior = ClsFactory::Create('Model.PersonVistior.mPersonVistior');
        $vistior_list_day = $mPersonVistior->getPersonVistiorInfo($wheresql_day);
        $total_count_day = empty($vistior_list_day) ? 0 : count($vistior_list_day);
        
        return $total_count_day;
    }
    
    
    public function vistior_list($client_account,$orderby,$offset,$limit) {
                
    }
}