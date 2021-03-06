IF (EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'z_db_versions')) DROP TABLE z_db_versions;

CREATE TABLE z_db_versions (
	id smallint NOT NULL IDENTITY(1,1),
	major TINYINT NOT NULL,
	minor TINYINT NOT NULL,
	point TINYINT NOT NULL,
	script_type TINYINT NOT NULL DEFAULT 0,
	date_added DATETIME NOT NULL,
	CONSTRAINT PK_ID PRIMARY KEY (id ASC)
);