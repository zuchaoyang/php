-- ----------------------------
-- Table structure for `wmw_album`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album`;
CREATE TABLE IF NOT EXISTS `wmw_album` (
  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_name` varchar(30) NOT NULL COMMENT '班级相册名称',
  `album_explain` varchar(200) NOT NULL COMMENT '对相册进行描述(不超过60字)',
  `album_img` varchar(50) NOT NULL COMMENT '相册封面图片',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '添加信息人的账号',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加信息的时间',
  `upd_account` bigint(20) unsigned NOT NULL COMMENT '修改信息人的账号',
  `upd_time` int(10) unsigned NOT NULL COMMENT '修改信息的时间',
  `album_auto_img` varchar(50) NOT NULL COMMENT '系统相册封面',
  `photo_num` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '相片数',
  PRIMARY KEY (`album_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='相册信息表' AUTO_INCREMENT=16 ;

-- ----------------------------
-- Table structure for `wmw_album_class_grants`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_class_grants`;
CREATE TABLE `wmw_album_class_grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` bigint(20) unsigned NOT NULL COMMENT '班级code',
  `album_id` int(10) unsigned NOT NULL COMMENT '相册id',
  `grant` tinyint(1) unsigned NOT NULL COMMENT '相册查看权限，0:公开 1:本班 2:管理员 3:本学校',
  PRIMARY KEY (`id`),
  KEY `class_code` (`class_code`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='班级相册权限表';

-- ----------------------------
-- Table structure for `wmw_album_class_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_class_relation`;
CREATE TABLE `wmw_album_class_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(20) unsigned NOT NULL COMMENT '班级编号',
  `album_id` int(11) unsigned NOT NULL COMMENT '相册id',
  PRIMARY KEY (`id`),
  KEY `class_code` (`class_code`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='班级与相册关系表';

-- ----------------------------
-- Table structure for `wmw_album_person_grants`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_person_grants`;
CREATE TABLE `wmw_album_person_grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '用户账号',
  `album_id` int(10) unsigned NOT NULL COMMENT '相册id',
  `grant` tinyint(3) unsigned NOT NULL COMMENT '相册查看权限，0:公开 1:好友 2:仅主人',
  PRIMARY KEY (`id`),
  KEY `client_account` (`client_account`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='个人相册权限表';

-- ----------------------------
-- Table structure for `wmw_album_person_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_person_relation`;
CREATE TABLE `wmw_album_person_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '用户账号',
  `album_id` int(11) unsigned NOT NULL COMMENT '相册id',
  PRIMARY KEY (`id`),
  KEY `client_account` (`client_account`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=ucs2 COMMENT='个人与相册关系表';

-- ----------------------------
-- Table structure for `wmw_album_photo_comments`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_photo_comments`;
CREATE TABLE `wmw_album_photo_comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键，自增',
  `up_id` int(10) unsigned NOT NULL COMMENT '对照片的评论内容id评论 或者 对相片的评论的评论',
  `photo_id` int(10) unsigned NOT NULL COMMENT '照片id',
  `content` varchar(255) NOT NULL COMMENT '对照片的评论内容',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '评论人账号',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `level` tinyint(3) unsigned NOT NULL COMMENT '只支持两级 1，2 1:对照片的评论2:对照片评论的评',
  PRIMARY KEY (`comment_id`),
  KEY `photo_id` (`photo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='照片评论表';

-- ----------------------------
-- Table structure for `wmw_album_photos`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_photos`;
CREATE TABLE `wmw_album_photos` (
  `photo_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '照片id',
  `album_id` int(10) unsigned NOT NULL COMMENT '照片所属相册',
  `name` varchar(50) NOT NULL COMMENT '照片名称',
  `file_big` varchar(50) NOT NULL COMMENT '照片链接地址',
  `file_middle` varchar(50) NOT NULL COMMENT '照片缩略图',
  `file_small` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL COMMENT '照片描述',
  `comments` mediumint(9) unsigned NOT NULL COMMENT '照片评论总数',
  `upd_account` bigint(20) unsigned NOT NULL COMMENT '最后更新人账号',
  `upd_time` int(11) unsigned NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`photo_id`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='照片信息表';

-- ----------------------------
-- Table structure for `wmw_blog`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog`;
CREATE TABLE `wmw_blog` (
  `blog_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `type_id` int(11) unsigned NOT NULL,
  `views` int(11) unsigned NOT NULL,
  `is_published` tinyint(1) NOT NULL,
  `contentbg` varchar(100) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `comments` mediumint(8) unsigned NOT NULL,
  `add_account` bigint(20) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `upd_account` bigint(20) unsigned NOT NULL,
  `upd_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`blog_id`),
  KEY `fk_add_account` (`add_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_blog_class_grants`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_class_grants`;
CREATE TABLE `wmw_blog_class_grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(10) unsigned NOT NULL COMMENT '班级编号',
  `blog_id` int(10) unsigned NOT NULL COMMENT '日志ID',
  `grant` tinyint(1) NOT NULL COMMENT '权限',
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`),
  KEY `class_code` (`class_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='班级日志权限表';

-- ----------------------------
-- Table structure for `wmw_blog_class_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_class_relation`;
CREATE TABLE `wmw_blog_class_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(10) unsigned NOT NULL COMMENT '班级编号',
  `blog_id` int(10) unsigned NOT NULL COMMENT '日志ID',
  PRIMARY KEY (`id`),
  KEY `class_code` (`class_code`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='班级与日志关系表';

-- ----------------------------
-- Table structure for `wmw_blog_comments`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_comments`;
CREATE TABLE `wmw_blog_comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(10) unsigned NOT NULL COMMENT '日志ID ',
  `content` varchar(255) NOT NULL COMMENT '对日志的评论内容',
  `up_id` int(10) unsigned NOT NULL COMMENT '对日志的评论内容id评论 或者 对日志的评论的评论',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '评论信息人的账号',
  `add_time` int(10) unsigned NOT NULL COMMENT '评论信息时的时间',
  `level` tinyint(1) NOT NULL COMMENT '只支持两级 1，2 1:对照片的评论2:对照片评论的评论',
  PRIMARY KEY (`comment_id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='日志评论表';

-- ----------------------------
-- Records of wmw_blog_comments
-- ----------------------------
INSERT INTO `wmw_blog_comments` VALUES ('1', '64', '123', '0', '11070004', '0', '0');

-- ----------------------------
-- Table structure for `wmw_blog_content`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_content`;
CREATE TABLE `wmw_blog_content` (
  `blog_id` int(11) unsigned NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_blog_person_grants`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_person_grants`;
CREATE TABLE `wmw_blog_person_grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '用户账号',
  `blog_id` int(10) unsigned NOT NULL COMMENT '日志ID',
  `grant` tinyint(1) NOT NULL COMMENT '权限',
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`),
  KEY `client_account` (`client_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='个人日志权限表';


-- ----------------------------
-- Table structure for `wmw_blog_person_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_person_relation`;
CREATE TABLE `wmw_blog_person_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '用户账号',
  `blog_id` int(10) unsigned NOT NULL COMMENT '日志ID',
  PRIMARY KEY (`id`),
  KEY `client_account` (`client_account`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='个人与日志关系表';


-- ----------------------------
-- Table structure for `wmw_blog_types`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_types`;
CREATE TABLE `wmw_blog_types` (
  `type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL,
  `add_account` bigint(20) unsigned NOT NULL,
  `add_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_blog_types_class_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_types_class_relation`;
CREATE TABLE `wmw_blog_types_class_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(10) unsigned NOT NULL COMMENT '班级编号',
  `type_id` int(10) unsigned NOT NULL COMMENT '日志类型ID',
  PRIMARY KEY (`id`),
  KEY `class_code` (`class_code`),
  KEY `blog_id` (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='班级与日志关系表';

-- ----------------------------
-- Table structure for `wmw_blog_types_person_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_types_person_relation`;
CREATE TABLE `wmw_blog_types_person_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '用户账号',
  `type_id` int(10) unsigned NOT NULL COMMENT '日志类型ID',
  PRIMARY KEY (`id`),
  KEY `client_account` (`client_account`),
  KEY `blog_id` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='个人与日志关系表';

-- ----------------------------
-- Table structure for `wmw_checkin`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_checkin`;
CREATE TABLE `wmw_checkin` (
  `checkin_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '签到ID，主键自增',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '签到用户账号',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`checkin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到表';

-- ----------------------------
-- Table structure for `wmw_class_course`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_course`;
CREATE TABLE `wmw_class_course` (
  `course_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(11) unsigned NOT NULL,
  `weekday` tinyint(1) NOT NULL,
  `num_th` tinyint(1) NOT NULL,
  `name` varchar(21) NOT NULL,
  `upd_account` bigint(20) unsigned NOT NULL,
  `upd_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`course_id`),
  KEY `fk_class_code` (`class_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for `wmw_class_course_config`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_course_config`;
CREATE TABLE `wmw_class_course_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL,
  `skin_id` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_client_account` (`client_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for `wmw_class_course_skin`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_course_skin`;
CREATE TABLE `wmw_class_course_skin` (
  `skin_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `url` varchar(20) NOT NULL COMMENT '课程表皮肤',
  `small_img` varchar(20) DEFAULT NULL COMMENT '课程表皮肤小图',
  PRIMARY KEY (`skin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

INSERT INTO `wmw_class_course_skin` (`skin_id`, `name`, `url`, `small_img`) VALUES
(1, '模板一', 'course_bj1.jpg', 'icon_pic01.jpg'),
(2, '模板二', 'course_bj2.jpg', 'icon_pic02.jpg'),
(3, '模板三', 'course_bj3.jpg', 'icon_pic03.jpg'),
(4, '模板四', 'course_bj4.jpg', 'icon_pic04.jpg');

-- ----------------------------
-- Table structure for `wmw_class_exam`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_exam`;
CREATE TABLE `wmw_class_exam` (
  `exam_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `exam_name` varchar(20) NOT NULL,
  `exam_time` int(10) unsigned NOT NULL,
  `add_account` bigint(20) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `upd_account` bigint(20) unsigned NOT NULL,
  `upd_time` int(10) unsigned NOT NULL,
  `exam_good` float unsigned NOT NULL,
  `exam_bad` float unsigned NOT NULL,
  `exam_well` float unsigned NOT NULL,
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发布 0：草稿 1：发布',
  `is_sms` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '否是发短信 0：不发送 1：发送',
  PRIMARY KEY (`exam_id`),
  KEY `class_code` (`class_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for `wmw_class_exam_score`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_exam_score`;
CREATE TABLE `wmw_class_exam_score` (
  `score_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL,
  `exam_id` int(10) unsigned NOT NULL,
  `exam_score` float unsigned NOT NULL,
  `score_py` varchar(150) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_account` bigint(20) unsigned NOT NULL,
  `upd_time` int(10) unsigned NOT NULL,
  `upd_account` bigint(20) unsigned NOT NULL,
  `is_join` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`score_id`),
  KEY `exam_id` (`exam_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for `wmw_class_homework`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_homework`;
CREATE TABLE `wmw_class_homework` (
  `homework_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `add_account` bigint(20) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `upd_account` bigint(20) unsigned NOT NULL,
  `upd_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  `attachment` varchar(50) NOT NULL,
  `content` varchar(200) NOT NULL,
  `is_sms` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `accepters` varchar(10) NOT NULL,
  PRIMARY KEY (`homework_id`),
  KEY `class_code` (`class_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_class_homework_send`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_homework_send`;
CREATE TABLE `wmw_class_homework_send` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `homework_id` int(10) unsigned NOT NULL,
  `client_account` bigint(20) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `is_view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `homework_id` (`homework_id`,`client_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_class_notice`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_notice`;
CREATE TABLE `wmw_class_notice` (
  `notice_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(11) unsigned NOT NULL,
  `notice_title` varchar(20) NOT NULL,
  `notice_content` varchar(200) NOT NULL,
  `add_account` bigint(20) unsigned NOT NULL,
  `add_time` int(11) unsigned NOT NULL,
  `is_sms` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`notice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_class_notice_foot`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_class_notice_foot`;
CREATE TABLE `wmw_class_notice_foot` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `notice_id` int(10) unsigned NOT NULL,
  `client_account` bigint(20) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notice_id` (`notice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_client_active`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_client_active`;
CREATE TABLE `wmw_client_active` (
  `active_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '用户账号',
  `value` mediumint(8) unsigned NOT NULL COMMENT '用户总活跃值',
  PRIMARY KEY (`active_id`),
  UNIQUE KEY `client_account` (`client_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for `wmw_client_active_log`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_client_active_log`;
CREATE TABLE `wmw_client_active_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '账号',
  `value` mediumint(8) unsigned NOT NULL COMMENT '本次所得活跃值',
  `message` varchar(255) NOT NULL COMMENT '活跃说明',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `module` smallint(4) NOT NULL,
  `action` tinyint(4) NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `client_account` (`client_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_feed`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_feed`;
CREATE TABLE IF NOT EXISTS `wmw_feed` (
  `feed_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键，自增',
  `feed_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1：说说 2：日志  3：相册',
  `title` varchar(50) NOT NULL COMMENT '动态标题',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '添加人',
  `timeline` int(10) unsigned NOT NULL COMMENT '添加时间',
  `feed_content` varchar(255) NOT NULL COMMENT '动态内容',
  `img_url` varchar(255) NOT NULL COMMENT '动态中涉及到得图片的url',
  `from_id` int(10) unsigned NOT NULL COMMENT '来源id',
  `action` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '动作 1:发布 2：评论',
  PRIMARY KEY (`feed_id`),
  KEY `add_account` (`add_account`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='动态表' AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `wmw_feed_class_relation`;
CREATE TABLE IF NOT EXISTS `wmw_feed_class_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned NOT NULL COMMENT '动态ID',
  `class_code` int(11) unsigned NOT NULL COMMENT '班级ID',
  `feed_type` tinyint(1) unsigned NOT NULL COMMENT '动态类型',
  `timeline` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='班级动态关系表' AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `wmw_feed_person_relation`;
CREATE TABLE IF NOT EXISTS `wmw_feed_person_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned NOT NULL COMMENT '动态ID',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '用户帐号',
  `feed_type` tinyint(1) unsigned NOT NULL COMMENT '动态类型',
  `timeline` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='个人动态关系表' AUTO_INCREMENT=1 ;

--
-- 表的结构 `wmw_feed_timeline`
--
DROP TABLE IF EXISTS `wmw_feed_timeline`;
CREATE TABLE IF NOT EXISTS `wmw_feed_timeline` (
  `id` int(11) unsigned NOT NULL,
  `feed_id` int(11) unsigned NOT NULL,
  `feed_type` tinyint(1) NOT NULL,
  `client_account` bigint(20) unsigned NOT NULL,
  `timeline` int(11) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='动态时间线表';

-- ----------------------------
-- Table structure for `wmw_mood`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_mood`;
CREATE TABLE IF NOT EXISTS `wmw_mood` (
  `mood_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '说说ID，主键自增',
  `content` varchar(255) NOT NULL COMMENT '说说内容',
  `img_url` varchar(255) NOT NULL COMMENT '说说图片，默认为空串',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '添加用户id',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `comments` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mood_id`),
  KEY `index_add_user` (`add_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='说说表' AUTO_INCREMENT=1 ;

-- ----------------------------
-- Table structure for `wmw_mood_class_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_mood_class_relation`;
CREATE TABLE `wmw_mood_class_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID，主键自增',
  `class_code` bigint(20) unsigned NOT NULL COMMENT '班级编号',
  `mood_id` int(11) unsigned NOT NULL COMMENT '说说ID',
  PRIMARY KEY (`id`),
  KEY `index_class_code` (`class_code`),
  KEY `index_mood_id` (`mood_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='班级与说说关系表';

-- ----------------------------
-- Table structure for `wmw_mood_comments`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_mood_comments`;
CREATE TABLE `wmw_mood_comments` (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '说说评论id，主键自增',
  `up_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级评论id',
  `mood_id` int(11) unsigned NOT NULL COMMENT '说说id',
  `content` varchar(255) NOT NULL COMMENT '评论内容',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '评论人',
  `add_time` int(10) unsigned NOT NULL COMMENT '评论时间',
  `level` tinyint(1) unsigned NOT NULL COMMENT '评论等级，1:对说说的评论2:对说说评论的评论',
  PRIMARY KEY (`comment_id`),
  KEY `index_mood_id` (`mood_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='说说评论表';

-- ----------------------------
-- Table structure for `wmw_mood_person_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_mood_person_relation`;
CREATE TABLE `wmw_mood_person_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID，主键自增',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '用户账号',
  `mood_id` int(11) unsigned NOT NULL COMMENT '说说ID',
  PRIMARY KEY (`id`),
  KEY `index_client_account` (`client_account`),
  KEY `index_mood_id` (`mood_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='个人与说说关系表';

-- ----------------------------
-- Table structure for `wmw_msg_require`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_msg_require`;
CREATE TABLE `wmw_msg_require` (
  `req_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(100) NOT NULL COMMENT '请求内容',
  `to_account` bigint(20) unsigned NOT NULL COMMENT '接收人账号',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '请求人账号',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`req_id`),
  KEY `to_account` (`to_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_msg_response`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_msg_response`;
CREATE TABLE `wmw_msg_response` (
  `res_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(100) NOT NULL COMMENT '请求内容',
  `to_account` bigint(20) unsigned NOT NULL COMMENT '接收人账号',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '回应人账号',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`res_id`),
  KEY `to_account` (`to_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_private_msg`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_private_msg`;
CREATE TABLE `wmw_private_msg` (
  `msg_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信Id',
  `send_uid` bigint(20) unsigned NOT NULL COMMENT '发起者',
  `to_uid` bigint(20) unsigned NOT NULL COMMENT '接受者',
  `content` varchar(255) NOT NULL COMMENT '内容',
  `add_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  `img_url` varchar(255) DEFAULT NULL COMMENT '图片url',
  PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_private_msg_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_private_msg_relation`;
CREATE TABLE `wmw_private_msg_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `send_uid` bigint(20) unsigned NOT NULL COMMENT '与我相关',
  `to_uid` bigint(20) unsigned NOT NULL COMMENT '与我私信的帐号',
  `new_msg_id` int(11) unsigned NOT NULL COMMENT '最新一条私信ID',
  `msg_count` mediumint(11) unsigned NOT NULL DEFAULT '1' COMMENT '共几条私信',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_private_msg_session`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_private_msg_session`;
CREATE TABLE `wmw_private_msg_session` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信Id',
  `send_uid` bigint(20) unsigned NOT NULL COMMENT '发起者',
  `to_uid` bigint(20) unsigned NOT NULL COMMENT '接受者',
  `msg_id` int(11) unsigned NOT NULL COMMENT '私信id',
  PRIMARY KEY (`id`),
  KEY `send_uid` (`send_uid`,`to_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `wmw_person_vistior`;
CREATE TABLE `wmw_person_vistior` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`uid` BIGINT( 20 ) UNSIGNED NOT NULL COMMENT '主人',
`vuid` BIGINT( 20 ) UNSIGNED NOT NULL COMMENT '访客',
`timeline` INT( 11 ) UNSIGNED NOT NULL COMMENT '时间',
PRIMARY KEY ( `id` )
) ENGINE = InnoDB COMMENT = '个人空间访客' ;
