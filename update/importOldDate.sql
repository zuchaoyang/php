-- album
-- 照片图片需要程序统一处理，现在中号图片没有

INSERT INTO wmw_album(album_id,album_name,album_explain,album_img,add_account,add_time,upd_account,upd_time,album_auto_img,photo_num) SELECT album_id,album_name,album_explain,album_img,add_account,add_date,upd_account,upd_date,'' as album_auto_img,0 as photo_num FROM old_wmw_album_info;

INSERT INTO wmw_album_person_relation(client_account,album_id,`grant`) SELECT add_account,album_id,0 as album_grant FROM old_wmw_album_info;

INSERT INTO wmw_album_photos(photo_id,album_id,`name`,file_big,file_middle,file_small,description,comments,upd_account,upd_time) SELECT photo_id,album_id,photo_name,photo_url,'' as file_middle,photo_min_url,photo_explain,0 as comments,upd_account,upd_date FROM old_wmw_photos_info;

UPDATE wmw_album a,(SELECT album_id,count(photo_id) as photo_num FROM `wmw_album_photos` GROUP BY album_id) b SET a.photo_num=b.photo_num WHERE a.album_id=b.album_id;

-- blog
-- 日志摘要和第一张图没有，需要程序处理
-- 旧数据日期显示不对 "2012-今天 16:08"

INSERT INTO wmw_blog(blog_id,title,type_id,views,is_published,contentbg,summary,comments,add_account,add_time,upd_account,upd_time,first_img) SELECT log_id,log_name,log_type,read_count,log_status,contentbg,'' as summary,0 as comments,add_account,UNIX_TIMESTAMP(add_date) as add_time,upd_account,UNIX_TIMESTAMP(upd_date) as upd_time,'' as first_img FROM old_wmw_person_logs;

INSERT INTO wmw_blog_content(blog_id,content) SELECT log_id,log_content FROM old_wmw_person_logs;

INSERT INTO wmw_blog_person_relation(client_account,blog_id,`grant`) SELECT add_account,log_id,0 as blog_grant FROM old_wmw_person_logs;

INSERT INTO wmw_blog_types(type_id,`name`,add_account,add_time) SELECT logtype_id,logtype_name,add_account,UNIX_TIMESTAMP(add_date) FROM old_wmw_log_types;

INSERT INTO wmw_blog_types_person_relation(client_account,type_id) SELECT add_account,logtype_id FROM old_wmw_log_types;

-- exam
-- 成绩有些学生姓名显示 "--"

DELETE FROM old_wmw_exam_info WHERE school_id=0;

INSERT INTO wmw_class_exam(exam_id,class_code,subject_id,exam_name,exam_time,add_account,add_time,upd_account,upd_time,exam_good,exam_bad,exam_well,is_published,is_sms) SELECT exam_id,class_code,subject_id,exam_name,UNIX_TIMESTAMP(exam_date) as exam_date,add_account,UNIX_TIMESTAMP(add_date) as add_date,upd_account,UNIX_TIMESTAMP(upd_date) as upd_date,exam_good,exam_bad,exam_well,1 as is_published,1 as is_sms FROM old_wmw_exam_info;

INSERT INTO wmw_class_exam_score(score_id,client_account,exam_id,exam_score,score_py,add_time,add_account,upd_time,upd_account,is_join,is_sms) SELECT score_id,client_account,exam_id,exam_score,score_py,UNIX_TIMESTAMP(add_date) as add_date,add_account,UNIX_TIMESTAMP(upd_date) as upd_date,upd_account,1 as is_join,1 as is_sms FROM old_wmw_student_score;

UPDATE wmw_class_exam_score SET is_join=0 WHERE exam_score=0;
