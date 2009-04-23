CREATE TABLE applications (
 id integer not null primary key,
 name varchar(100) not null,
 resume varchar(120) not null,
 description text,
 theme char(2) not null
);

CREATE TABLE attributes (
 id integer not null primary key,
 app_name varchar(40) not null,
 table_name varchar(40) not null,
 field_name varchar(40) not null,
 type varchar(40) not null,
 allow_null char(3) not null,
 primary_key char(1) not null,
 label varchar(70) not null,
 size varchar(7),
 maxlength varchar(7),
 component char(3) not null,
 hidden char(1) not null,
 browse char(1) not null,
 search char(1) not null,
 report char(1) not null,
 read_only char(1) not null,
 gender char(1) not null,
 comments text
);

create table relations (
 id integer not null primary key,
 attributes_id integer not null,
 table_relation varchar(40) not null,
 field_detail varchar(40) not null,
 field_order varchar(40) not null
);

create table relations_list (
 id integer not null primary key,
 relations_id  integer not null,
 number integer,
 field_name varchar(40) not null
);

create index "relations_id1" on relations_list(relations_id);
create index "attributes_id1" on relations(attributes_id);