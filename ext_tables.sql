#
# Table structure for table 'tx_veguestbook_entries'
#
CREATE TABLE tx_veguestbook_entries (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	uid_tt_news int(11) unsigned DEFAULT '0' NOT NULL,
	sys_language_uid varchar(50) DEFAULT '' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	firstname varchar(25) DEFAULT '' NOT NULL,
	surname varchar(255) DEFAULT '' NOT NULL,
	email varchar(50) DEFAULT '' NOT NULL,
	homepage text NOT NULL,
	place varchar(50) DEFAULT '' NOT NULL,
	entry text NOT NULL,
	entrycomment text NOT NULL,
	remote_addr varchar(50) DEFAULT '' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);