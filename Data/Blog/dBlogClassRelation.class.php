<?php
class dBlogClassRelation extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_blog_class_relation';
    protected $_fields = array(
        'id',
        'class_code',
        'blog_id',
    	'grant',
    );
    
    protected $_index_list = array(
        'id',
        'class_code',
        'blog_id',
     );
    
     /**
      * 通用的获取班级日志的函数
      * @param $class_codes
      * @param $where_appends
      * 注明：$where_appends只能是数组，并且一个元素只能包含一个过滤条件
      *       ef:
      *       $where_appends = array(
      *       	"add_time>='1000'",
      *       	"add_time<='2000'"
      *       );
      * @param $offset
      * @param $limit
      */
    public function getClassBlogByClassCode($class_codes, $where_appends, $orderby = null, $offset = 0, $limit = 10) {
        if(empty($class_codes)) {
            return false;
        }
        
        $wherearr = array(
            "a.class_code in('" . implode("','", (array)$class_codes) . "')"
        );
        if(!empty($where_appends) && is_array($where_appends)) {
            foreach($where_appends as $where_condition) {
                $where_condition = trim($where_condition);
                $wherearr[] = "b." . $where_condition; 
            } 
        }
        
        $offset = max(intval($offset), 0);
        $limit = $limit > 0 ? $limit : 10;
        
        $selectsql = "select a.class_code,a.grant,b.* from wmw_blog_class_relation a inner join wmw_blog b on a.blog_id=b.blog_id";
        $wheresql = "where " . implode(" and ", $wherearr);
        $orderbysql = !empty($orderby) ? "order by b.{$orderby}" : '';
        $limitsql = "limit $offset,$limit";
        //dump("$selectsql $wheresql $orderbysql $limitsql");exit;
        $class_blog_arr = $this->query("$selectsql $wheresql $orderbysql $limitsql");
        
        //数组重组
        $new_class_blog_arr = array();
        if(!empty($class_blog_arr)) {
            foreach($class_blog_arr as $key=>$class_blog) {
                $new_class_blog_arr[$class_blog['class_code']][$class_blog['blog_id']] = $class_blog;
            }
            
            unset($class_blog_arr);
        }
        
        return !empty($new_class_blog_arr) ? $new_class_blog_arr : false;
    }
    
    /**
     * 添加
     * @param $datas
     * @param $return_insert_id
     */
    public function addBlogClassRelation($datas, $return_insert_id = false) {
        if(empty($datas)) {
            return false;
        }
        
        return $this->add($datas, $return_insert_id);
    }
    
    /**
     * 修改
     * @param $datas
     * @param $id
     */
    public function modifyBlogClassRelation($datas, $id) {
        if(empty($datas) || empty($id)) {
            return false;
        }
        
        return $this->modify($datas, $id);
    }

    /**
     * 删除
     * @param  $id
     */
    public function delBlogClassRelation($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->delete($id);
    }
    
    //根据blog_id 删除信息
    public function delBlogClassRelationByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        $in_str = implode(',', (array)$blog_id);
        $where_sql = !empty($in_str) ? "where blog_id in($in_str)" : "";
        $sql = "delete from {$this->_tablename} $where_sql";

        return $this->execute($sql);
    }
    
}