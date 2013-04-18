-- 废弃表重命名

alter table wmw_album_info rename to old_wmw_album_info;
alter table wmw_class_album rename to old_wmw_class_album;
alter table wmw_class_feed rename to old_wmw_class_feed;
alter table wmw_class_log rename to old_wmw_class_log;
alter table wmw_class_talk rename to old_wmw_class_talk;
alter table wmw_class_talkcomment rename to old_wmw_class_talkcomment;
alter table wmw_client_feed rename to old_wmw_client_feed;
alter table wmw_curriculum_info rename to old_wmw_curriculum_info;
alter table wmw_curriculum_skin rename to old_wmw_curriculum_skin;
alter table wmw_exam_info rename to old_wmw_exam_info;
alter table wmw_log_plun rename to old_wmw_log_plun;
alter table wmw_log_types rename to old_wmw_log_types;
alter table wmw_news_info rename to old_wmw_news_info;
alter table wmw_person_logs rename to old_wmw_person_logs;
alter table wmw_person_talk rename to old_wmw_person_talk;
alter table wmw_person_talkcomment rename to old_wmw_person_talkcomment;
alter table wmw_photo_plun rename to old_wmw_photo_plun;
alter table wmw_photos_info rename to old_wmw_photos_info;
alter table wmw_student_score rename to old_wmw_student_score;

-- 处理收藏评语库数据分类

ALTER TABLE `wmw_py_collect` ADD COLUMN `py_type`  tinyint NOT NULL AFTER `add_time`, ADD COLUMN `py_att`  tinyint NOT NULL AFTER `py_type`;


UPDATE wmw_py_collect a,wmw_py_info b SET a.py_type=b.py_type WHERE b.py_content=a.py_content;
UPDATE wmw_py_collect a,wmw_py_info b SET a.py_att=b.py_att WHERE b.py_content=a.py_content;
DELETE FROM `wmw_py_collect` WHERE py_type=0;
ALTER TABLE `wmw_client_class` ADD `sort_seq` SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `bms_school_request` ENGINE = InnoDB ;
ALTER TABLE china_unicom ENGINE = InnoDB ;
ALTER TABLE old_wmw_exam_info ENGINE = InnoDB ;
ALTER TABLE old_wmw_student_score ENGINE = InnoDB ;
ALTER TABLE wmw_bms_account ENGINE = InnoDB ;
ALTER TABLE wmw_class_info_history ENGINE = InnoDB ;
ALTER TABLE wmw_class_style ENGINE = InnoDB ;
ALTER TABLE wmw_client_class ENGINE = InnoDB ;
ALTER TABLE wmw_client_class_history ENGINE = InnoDB ;
ALTER TABLE wmw_family_relation ENGINE = InnoDB ;
ALTER TABLE wmw_gazx_regist_info ENGINE = InnoDB ;
ALTER TABLE wmw_py_info ENGINE = InnoDB ;
ALTER TABLE wmw_school_client_statistics ENGINE = InnoDB ;
ALTER TABLE wmw_school_teacher ENGINE = InnoDB ;
ALTER TABLE wmw_sls_action ENGINE = InnoDB ;
ALTER TABLE wmw_sys_subject ENGINE = InnoDB ;
ALTER TABLE wmw_upgrade_lock ENGINE = InnoDB ;
ALTER TABLE wmw_wo ENGINE = InnoDB ;
ALTER TABLE wmw_wo_c ENGINE = InnoDB ;
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 