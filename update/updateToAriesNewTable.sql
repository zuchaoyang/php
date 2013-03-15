-- ----------------------------
-- Table structure for `wmw_album`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album`;
CREATE TABLE IF NOT EXISTS `wmw_album` (
  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_name` varchar(30) NOT NULL COMMENT '�༶�������',
  `album_explain` varchar(200) NOT NULL COMMENT '������������(������60��)',
  `album_img` varchar(50) NOT NULL COMMENT '������ͼƬ',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '�����Ϣ�˵��˺�',
  `add_time` int(10) unsigned NOT NULL COMMENT '�����Ϣ��ʱ��',
  `upd_account` bigint(20) unsigned NOT NULL COMMENT '�޸���Ϣ�˵��˺�',
  `upd_time` int(10) unsigned NOT NULL COMMENT '�޸���Ϣ��ʱ��',
  `album_auto_img` varchar(50) NOT NULL COMMENT 'ϵͳ������',
  `photo_num` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '��Ƭ��',
  PRIMARY KEY (`album_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='�����Ϣ��' AUTO_INCREMENT=16 ;

-- ----------------------------
-- Table structure for `wmw_album_class_grants`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_class_grants`;
CREATE TABLE `wmw_album_class_grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` bigint(20) unsigned NOT NULL COMMENT '�༶code',
  `album_id` int(10) unsigned NOT NULL COMMENT '���id',
  `grant` tinyint(1) unsigned NOT NULL COMMENT '���鿴Ȩ�ޣ�0:���� 1:���� 2:����Ա 3:��ѧУ',
  PRIMARY KEY (`id`),
  KEY `class_code` (`class_code`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='�༶���Ȩ�ޱ�';

-- ----------------------------
-- Table structure for `wmw_album_class_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_class_relation`;
CREATE TABLE `wmw_album_class_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(20) unsigned NOT NULL COMMENT '�༶���',
  `album_id` int(11) unsigned NOT NULL COMMENT '���id',
  PRIMARY KEY (`id`),
  KEY `class_code` (`class_code`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='�༶������ϵ��';

-- ----------------------------
-- Table structure for `wmw_album_person_grants`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_person_grants`;
CREATE TABLE `wmw_album_person_grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�û��˺�',
  `album_id` int(10) unsigned NOT NULL COMMENT '���id',
  `grant` tinyint(3) unsigned NOT NULL COMMENT '���鿴Ȩ�ޣ�0:���� 1:���� 2:������',
  PRIMARY KEY (`id`),
  KEY `client_account` (`client_account`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='�������Ȩ�ޱ�';

-- ----------------------------
-- Table structure for `wmw_album_person_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_person_relation`;
CREATE TABLE `wmw_album_person_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�û��˺�',
  `album_id` int(11) unsigned NOT NULL COMMENT '���id',
  PRIMARY KEY (`id`),
  KEY `client_account` (`client_account`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=ucs2 COMMENT='����������ϵ��';

-- ----------------------------
-- Table structure for `wmw_album_photo_comments`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_photo_comments`;
CREATE TABLE `wmw_album_photo_comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '����������',
  `up_id` int(10) unsigned NOT NULL COMMENT '����Ƭ����������id���� ���� ����Ƭ�����۵�����',
  `photo_id` int(10) unsigned NOT NULL COMMENT '��Ƭid',
  `content` varchar(255) NOT NULL COMMENT '����Ƭ����������',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�������˺�',
  `add_time` int(10) unsigned NOT NULL COMMENT '���ʱ��',
  `level` tinyint(3) unsigned NOT NULL COMMENT 'ֻ֧������ 1��2 1:����Ƭ������2:����Ƭ���۵���',
  PRIMARY KEY (`comment_id`),
  KEY `photo_id` (`photo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='��Ƭ���۱�';

-- ----------------------------
-- Table structure for `wmw_album_photos`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_album_photos`;
CREATE TABLE `wmw_album_photos` (
  `photo_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '��Ƭid',
  `album_id` int(10) unsigned NOT NULL COMMENT '��Ƭ�������',
  `name` varchar(50) NOT NULL COMMENT '��Ƭ����',
  `file_big` varchar(50) NOT NULL COMMENT '��Ƭ���ӵ�ַ',
  `file_middle` varchar(50) NOT NULL COMMENT '��Ƭ����ͼ',
  `file_small` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL COMMENT '��Ƭ����',
  `comments` mediumint(9) unsigned NOT NULL COMMENT '��Ƭ��������',
  `upd_account` bigint(20) unsigned NOT NULL COMMENT '���������˺�',
  `upd_time` int(11) unsigned NOT NULL COMMENT '������ʱ��',
  PRIMARY KEY (`photo_id`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='��Ƭ��Ϣ��';

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
  `class_code` int(10) unsigned NOT NULL COMMENT '�༶���',
  `blog_id` int(10) unsigned NOT NULL COMMENT '��־ID',
  `grant` tinyint(1) NOT NULL COMMENT 'Ȩ��',
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`),
  KEY `class_code` (`class_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='�༶��־Ȩ�ޱ�';

-- ----------------------------
-- Table structure for `wmw_blog_class_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_class_relation`;
CREATE TABLE `wmw_blog_class_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` int(10) unsigned NOT NULL COMMENT '�༶���',
  `blog_id` int(10) unsigned NOT NULL COMMENT '��־ID',
  PRIMARY KEY (`id`),
  KEY `class_code` (`class_code`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='�༶����־��ϵ��';

-- ----------------------------
-- Table structure for `wmw_blog_comments`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_comments`;
CREATE TABLE `wmw_blog_comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(10) unsigned NOT NULL COMMENT '��־ID ',
  `content` varchar(255) NOT NULL COMMENT '����־����������',
  `up_id` int(10) unsigned NOT NULL COMMENT '����־����������id���� ���� ����־�����۵�����',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '������Ϣ�˵��˺�',
  `add_time` int(10) unsigned NOT NULL COMMENT '������Ϣʱ��ʱ��',
  `level` tinyint(1) NOT NULL COMMENT 'ֻ֧������ 1��2 1:����Ƭ������2:����Ƭ���۵�����',
  PRIMARY KEY (`comment_id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='��־���۱�';

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
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�û��˺�',
  `blog_id` int(10) unsigned NOT NULL COMMENT '��־ID',
  `grant` tinyint(1) NOT NULL COMMENT 'Ȩ��',
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`),
  KEY `client_account` (`client_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='������־Ȩ�ޱ�';


-- ----------------------------
-- Table structure for `wmw_blog_person_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_person_relation`;
CREATE TABLE `wmw_blog_person_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�û��˺�',
  `blog_id` int(10) unsigned NOT NULL COMMENT '��־ID',
  PRIMARY KEY (`id`),
  KEY `client_account` (`client_account`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='��������־��ϵ��';


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
  `class_code` int(10) unsigned NOT NULL COMMENT '�༶���',
  `type_id` int(10) unsigned NOT NULL COMMENT '��־����ID',
  PRIMARY KEY (`id`),
  KEY `class_code` (`class_code`),
  KEY `blog_id` (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='�༶����־��ϵ��';

-- ----------------------------
-- Table structure for `wmw_blog_types_person_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_blog_types_person_relation`;
CREATE TABLE `wmw_blog_types_person_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�û��˺�',
  `type_id` int(10) unsigned NOT NULL COMMENT '��־����ID',
  PRIMARY KEY (`id`),
  KEY `client_account` (`client_account`),
  KEY `blog_id` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='��������־��ϵ��';

-- ----------------------------
-- Table structure for `wmw_checkin`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_checkin`;
CREATE TABLE `wmw_checkin` (
  `checkin_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ǩ��ID����������',
  `client_account` bigint(20) unsigned NOT NULL COMMENT 'ǩ���û��˺�',
  `add_time` int(10) unsigned NOT NULL COMMENT '���ʱ��',
  PRIMARY KEY (`checkin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ǩ����';

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
  `url` varchar(20) NOT NULL COMMENT '�γ̱�Ƥ��',
  `small_img` varchar(20) DEFAULT NULL COMMENT '�γ̱�Ƥ��Сͼ',
  PRIMARY KEY (`skin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

INSERT INTO `wmw_class_course_skin` (`skin_id`, `name`, `url`, `small_img`) VALUES
(1, 'ģ��һ', 'course_bj1.jpg', 'icon_pic01.jpg'),
(2, 'ģ���', 'course_bj2.jpg', 'icon_pic02.jpg'),
(3, 'ģ����', 'course_bj3.jpg', 'icon_pic03.jpg'),
(4, 'ģ����', 'course_bj4.jpg', 'icon_pic04.jpg');

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
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '�Ƿ񷢲� 0���ݸ� 1������',
  `is_sms` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '���Ƿ����� 0�������� 1������',
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
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�û��˺�',
  `value` mediumint(8) unsigned NOT NULL COMMENT '�û��ܻ�Ծֵ',
  PRIMARY KEY (`active_id`),
  UNIQUE KEY `client_account` (`client_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for `wmw_client_active_log`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_client_active_log`;
CREATE TABLE `wmw_client_active_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�˺�',
  `value` mediumint(8) unsigned NOT NULL COMMENT '�������û�Ծֵ',
  `message` varchar(255) NOT NULL COMMENT '��Ծ˵��',
  `add_time` int(10) unsigned NOT NULL COMMENT '���ʱ��',
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
  `feed_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '����������',
  `feed_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1��˵˵ 2����־  3�����',
  `title` varchar(50) NOT NULL COMMENT '��̬����',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '�����',
  `timeline` int(10) unsigned NOT NULL COMMENT '���ʱ��',
  `feed_content` varchar(255) NOT NULL COMMENT '��̬����',
  `img_url` varchar(255) NOT NULL COMMENT '��̬���漰����ͼƬ��url',
  `from_id` int(10) unsigned NOT NULL COMMENT '��Դid',
  `action` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '���� 1:���� 2������',
  PRIMARY KEY (`feed_id`),
  KEY `add_account` (`add_account`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='��̬��' AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `wmw_feed_class_relation`;
CREATE TABLE IF NOT EXISTS `wmw_feed_class_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned NOT NULL COMMENT '��̬ID',
  `class_code` int(11) unsigned NOT NULL COMMENT '�༶ID',
  `feed_type` tinyint(1) unsigned NOT NULL COMMENT '��̬����',
  `timeline` int(11) unsigned NOT NULL COMMENT '����ʱ��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='�༶��̬��ϵ��' AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `wmw_feed_person_relation`;
CREATE TABLE IF NOT EXISTS `wmw_feed_person_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned NOT NULL COMMENT '��̬ID',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�û��ʺ�',
  `feed_type` tinyint(1) unsigned NOT NULL COMMENT '��̬����',
  `timeline` int(11) unsigned NOT NULL COMMENT '����ʱ��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='���˶�̬��ϵ��' AUTO_INCREMENT=1 ;

--
-- ��Ľṹ `wmw_feed_timeline`
--
DROP TABLE IF EXISTS `wmw_feed_timeline`;
CREATE TABLE IF NOT EXISTS `wmw_feed_timeline` (
  `id` int(11) unsigned NOT NULL,
  `feed_id` int(11) unsigned NOT NULL,
  `feed_type` tinyint(1) NOT NULL,
  `client_account` bigint(20) unsigned NOT NULL,
  `timeline` int(11) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='��̬ʱ���߱�';

-- ----------------------------
-- Table structure for `wmw_mood`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_mood`;
CREATE TABLE IF NOT EXISTS `wmw_mood` (
  `mood_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '˵˵ID����������',
  `content` varchar(255) NOT NULL COMMENT '˵˵����',
  `img_url` varchar(255) NOT NULL COMMENT '˵˵ͼƬ��Ĭ��Ϊ�մ�',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '����û�id',
  `add_time` int(10) unsigned NOT NULL COMMENT '���ʱ��',
  `comments` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mood_id`),
  KEY `index_add_user` (`add_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='˵˵��' AUTO_INCREMENT=1 ;

-- ----------------------------
-- Table structure for `wmw_mood_class_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_mood_class_relation`;
CREATE TABLE `wmw_mood_class_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID����������',
  `class_code` bigint(20) unsigned NOT NULL COMMENT '�༶���',
  `mood_id` int(11) unsigned NOT NULL COMMENT '˵˵ID',
  PRIMARY KEY (`id`),
  KEY `index_class_code` (`class_code`),
  KEY `index_mood_id` (`mood_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='�༶��˵˵��ϵ��';

-- ----------------------------
-- Table structure for `wmw_mood_comments`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_mood_comments`;
CREATE TABLE `wmw_mood_comments` (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '˵˵����id����������',
  `up_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '�ϼ�����id',
  `mood_id` int(11) unsigned NOT NULL COMMENT '˵˵id',
  `content` varchar(255) NOT NULL COMMENT '��������',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '������',
  `add_time` int(10) unsigned NOT NULL COMMENT '����ʱ��',
  `level` tinyint(1) unsigned NOT NULL COMMENT '���۵ȼ���1:��˵˵������2:��˵˵���۵�����',
  PRIMARY KEY (`comment_id`),
  KEY `index_mood_id` (`mood_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='˵˵���۱�';

-- ----------------------------
-- Table structure for `wmw_mood_person_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_mood_person_relation`;
CREATE TABLE `wmw_mood_person_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID����������',
  `client_account` bigint(20) unsigned NOT NULL COMMENT '�û��˺�',
  `mood_id` int(11) unsigned NOT NULL COMMENT '˵˵ID',
  PRIMARY KEY (`id`),
  KEY `index_client_account` (`client_account`),
  KEY `index_mood_id` (`mood_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='������˵˵��ϵ��';

-- ----------------------------
-- Table structure for `wmw_msg_require`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_msg_require`;
CREATE TABLE `wmw_msg_require` (
  `req_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(100) NOT NULL COMMENT '��������',
  `to_account` bigint(20) unsigned NOT NULL COMMENT '�������˺�',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '�������˺�',
  `add_time` int(10) unsigned NOT NULL COMMENT '���ʱ��',
  PRIMARY KEY (`req_id`),
  KEY `to_account` (`to_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_msg_response`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_msg_response`;
CREATE TABLE `wmw_msg_response` (
  `res_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(100) NOT NULL COMMENT '��������',
  `to_account` bigint(20) unsigned NOT NULL COMMENT '�������˺�',
  `add_account` bigint(20) unsigned NOT NULL COMMENT '��Ӧ���˺�',
  `add_time` int(10) unsigned NOT NULL COMMENT '���ʱ��',
  PRIMARY KEY (`res_id`),
  KEY `to_account` (`to_account`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_private_msg`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_private_msg`;
CREATE TABLE `wmw_private_msg` (
  `msg_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '˽��Id',
  `send_uid` bigint(20) unsigned NOT NULL COMMENT '������',
  `to_uid` bigint(20) unsigned NOT NULL COMMENT '������',
  `content` varchar(255) NOT NULL COMMENT '����',
  `add_time` int(11) unsigned NOT NULL COMMENT '���ʱ��',
  `img_url` varchar(255) DEFAULT NULL COMMENT 'ͼƬurl',
  PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_private_msg_relation`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_private_msg_relation`;
CREATE TABLE `wmw_private_msg_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `send_uid` bigint(20) unsigned NOT NULL COMMENT '�������',
  `to_uid` bigint(20) unsigned NOT NULL COMMENT '����˽�ŵ��ʺ�',
  `new_msg_id` int(11) unsigned NOT NULL COMMENT '����һ��˽��ID',
  `msg_count` mediumint(11) unsigned NOT NULL DEFAULT '1' COMMENT '������˽��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wmw_private_msg_session`
-- ----------------------------
DROP TABLE IF EXISTS `wmw_private_msg_session`;
CREATE TABLE `wmw_private_msg_session` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '˽��Id',
  `send_uid` bigint(20) unsigned NOT NULL COMMENT '������',
  `to_uid` bigint(20) unsigned NOT NULL COMMENT '������',
  `msg_id` int(11) unsigned NOT NULL COMMENT '˽��id',
  PRIMARY KEY (`id`),
  KEY `send_uid` (`send_uid`,`to_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `wmw_person_vistior`;
CREATE TABLE `wmw_person_vistior` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`uid` BIGINT( 20 ) UNSIGNED NOT NULL COMMENT '����',
`vuid` BIGINT( 20 ) UNSIGNED NOT NULL COMMENT '�ÿ�',
`timeline` INT( 11 ) UNSIGNED NOT NULL COMMENT 'ʱ��',
PRIMARY KEY ( `id` )
) ENGINE = InnoDB COMMENT = '���˿ռ�ÿ�' ;
