-- should be ok for PG & MySQL, not tested on sqlite
CREATE TABLE light_acl(
	unikey VARCHAR(100) PRIMARY KEY,
	login VARCHAR(255),
	path VARCHAR(2048),
	accessright SMALLINT
);
