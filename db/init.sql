create type skill_kind as enum (
  'tool', 
  'coding', 
  'hardware', 
  'science', 
  'industry', 
  'language'
);

create type experience_kind as enum (
  'job', 
  'internship', 
  'education'
);

create table organization (
  id serial primary key,
  title varchar(255) not null,
  link varchar(255),
  logo varchar(255)
);

create table job (
  id serial primary key,
  title varchar(255) not null,
  brief text not null,

  unique(title)
);

create table skill (
  id serial primary key,
  kind skill_kind not null,
  title varchar(255) not null,
  level integer not null default 0,

  unique(title)
);

create table location (
  id serial primary key,
  zip varchar(16) not null,
  country character(2) not null,
  
  unique(zip, country)
);

create table experience (
  id serial primary key,
  kind experience_kind not null,
  title varchar(255) not null,
  brief text not null default '',
  details text not null default '',
  started date not null,
  ended date,
  
  organization integer references organization on delete set null,
  job integer references job on delete set null,
  location integer references location on delete set null
 );

create table experience_skill (
  experience integer not null references experience on delete cascade,
  skill integer not null references skill on delete cascade,

  primary key(experience, skill)
);

--
-- create table project (
--   id serial primary key,
--   title varchar(255) not null,
--   brief text not null default '',
--   details text not null default '',
--   start datetime not null,
--   end datetime,
--   link varchar(255),
--   picture varchar(255)
-- );
--

