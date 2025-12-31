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
  
  organization integer references organization(id),
  job integer references job(id),
  location integer references location(id)
 );

create table experience_skill (
  experience integer not null references experience(id),
  skill integer not null references skill(id),

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

