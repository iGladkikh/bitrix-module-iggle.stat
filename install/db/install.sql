create table if not exists i_stat_sess
(
	ID int(18) not null auto_increment,
	SITE_ID char(2) not null,
	GUEST_ID int(18) not null,
	USER_ID int(11),
	FIRST_IP varchar(20),
	LAST_IP varchar(20),
	USER_AGENT varchar(255),
	REFERER varchar(1000),
	FIRST_URI varchar(255),
	LAST_URI varchar(255),
	FIRST_PAGE_TITLE varchar(255),
	LAST_PAGE_TITLE varchar(255),
	CREATE_DATE datetime,
	LAST_ACTIVITY datetime,
	SESSION_TIME int(11),
	HITS int(11),
	IS_BANNED char(1),
	LAST_VISIT datetime,
	FUSER_ID int(18),

	PRIMARY KEY (ID)
);

create table if not exists i_stat_hit
(
	ID int(18) not null auto_increment,
	SITE_ID char(2) not null,
	SESSION_ID int(18) not null,
	IP varchar(20),
	URI varchar(255),
	PAGE_TITLE varchar(255),
	IS_404 char(1),
	CREATE_DATE datetime,

	PRIMARY KEY (ID)
);

create table if not exists i_stat_blacklist
(
	ID int(18) not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	GUEST_ID int(18),
	IP varchar(20),
	CREATE_DATE datetime,
	ACTIVE_TO date,
	COMMENT varchar(255),
	MESSAGE varchar(255),
	LAST_VISIT datetime,

	PRIMARY KEY (ID)
);

create table if not exists i_stat_bot_hit
(
	ID int(18) not null auto_increment,
	SITE_ID char(2) not null,
	BOT_NAME varchar(128) not null,
	IP varchar(20),
	URI varchar(255),
	PAGE_TITLE varchar(255),
	IS_404 char(1),
	CREATE_DATE datetime,

	PRIMARY KEY (ID)
);

create table if not exists i_stat_bot
(
	ID int(18) not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	NAME varchar(128) not null,
	MASK varchar(255) not null,
	CREATE_DATE datetime,
	LAST_VISIT datetime,

	PRIMARY KEY (ID)
);