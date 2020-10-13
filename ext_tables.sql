#
# Table structure for table 'tx_viewstatistics_domain_model_track'
#
CREATE TABLE tx_viewstatistics_domain_model_track (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	action varchar(255) DEFAULT '' NOT NULL,
	frontend_user int(11) unsigned DEFAULT '0' NOT NULL,
	login_duration int(11) unsigned DEFAULT '0' NOT NULL,
	page int(11) unsigned DEFAULT '0' NOT NULL,
	root_page int(11) unsigned DEFAULT '0' NOT NULL,
	object_uid int(11) unsigned DEFAULT '0' NOT NULL,
	object_type varchar(255) DEFAULT '' NOT NULL,
	ip_address varchar(46) DEFAULT '' NOT NULL,
	request_uri varchar(4095) DEFAULT '' NOT NULL,
	referrer varchar(4095) DEFAULT '' NOT NULL,
	user_agent varchar(4095) DEFAULT '' NOT NULL,
	language int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY frontend_user (frontend_user),
);
