ALTER DATABASE game SET search_path TO 'public';
ALTER DATABASE game SET timezone TO 'Europe/Prague';

SET search_path TO public;

CREATE OR REPLACE FUNCTION trigger_set_timestamp()
    RETURNS TRIGGER AS $$
BEGIN
    NEW.dt_upd = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

create table configuration
(
    id serial not null,
    name varchar(128) not null,
    value varchar(128) not null,
    dt_ins timestamptz default current_timestamp not null,
    dt_upd timestamptz default current_timestamp not null,
    enabled boolean not null default true,
    constraint pk_configuration PRIMARY KEY (id),
    constraint unq_configuration_name unique (name)
);

create trigger tr_system_configuration_dt_upd
    before update on configuration
    for each row
execute function trigger_set_timestamp();

drop table if exists "player";
create table if not exists "player"
(
    id                 serial       not null,
    username           varchar(100) not null,
    password           varchar(60),
    name               varchar(200) default null,
    email              text         not null,
    system_player        boolean        not null default false,
    admin              boolean        not null default false,
    enabled            boolean        not null default true,
    dt_ins             timestamp    not null default current_timestamp,
    dt_upd             timestamp    not null default current_timestamp,
    constraint pk_player primary key (id),
    constraint unq_player_username unique (username),
    constraint unq_player_email unique (email),
    constraint unq_player_name unique (name)
);

create trigger tr_player_dt_upd
    before update on "player"
    for each row
execute function trigger_set_timestamp();

drop table if exists "island";
create table if not exists "island"
(
    id                 serial       not null,
    seed               varchar(100) not null,
    code               varchar(64)  not null,
    name               varchar(200) not null,
    data               json         default null,
    started            boolean      not null default false,
    finished           boolean      not null default false,
    enabled            boolean      not null default true,
    dt_ins             timestamp    not null default current_timestamp,
    dt_upd             timestamp    not null default current_timestamp,
    constraint pk_island primary key (id),
    constraint unq_island_code unique (code),
    constraint unq_island_name unique (name)
);

create trigger tr_island_dt_upd
    before update on "island"
    for each row
execute function trigger_set_timestamp();

drop table if exists "map";
create table if not exists "map"
(
    id                 serial       not null,
    id_island           int4        not null constraint fk_map_island references island,
    type               varchar(3)   not null,
    x                  int4 not null,
    y                  int4 not null,
    enabled            boolean      not null default true,
    dt_ins             timestamp    not null default current_timestamp,
    dt_upd             timestamp    not null default current_timestamp,
    constraint pk_map primary key (id),
    constraint unq_map_tile unique (id_island, x, y)
);

create trigger tr_map_dt_upd
    before update on "map"
    for each row
execute function trigger_set_timestamp();
