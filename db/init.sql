create table experience (
  id integer primary key,
  kind varchar(64) not null,
  title varchar(255) not null,
  brief text not null default '',
  details text not null default '',
  start datetime not null,
  end datetime,
  location varchar(64)
);

create table experience_keypoint (
  id integer primary key,
  experience integer not null,
  content varchar(255) not null,
  
  foreign key(experience) references experience(id)
);

create table experience_organization (
  experience integer not null,
  organization integer not null,

  foreign key(experience) references experience(id),
  foreign key(organization) references organization(id),
  primary key(experience)
);

create table experience_job(
  experience integer not null,
  job integer not null,

  foreign key(experience) references experience(id),
  foreign key(job) references job(id),
  primary key(experience)
);

create table experience_skill (
  experience integer not null,
  skill integer not null,

  foreign key(experience) references experience(id),
  foreign key(skill) references skill(id),
  primary key(experience, skill)
);

--
-- create table project (
--   id integer primary key,
--   title varchar(255) not null,
--   brief text not null default '',
--   details text not null default '',
--   start datetime not null,
--   end datetime,
--   link varchar(255),
--   picture varchar(255)
-- );
--

create table organization (
  id integer primary key,
  title varchar(255) not null,
  link varchar(255),
  logo varchar(255)
);

create table job (
  id integer primary key,
  title varchar(255) not null,
  brief text not null
);

create table skill (
  id integer primary key,
  kind varchar(64) not null,
  title varchar(255) not null,
  level integer not null default 0
);
