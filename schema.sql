create table hosts
(
    id serial not null
        constraint hosts_pk
        primary key,
    host varchar not null,
    url_format varchar not null
);

alter table hosts owner to appledash;

create unique index hosts_host_uindex
    on hosts (host);

create table users
(
    id integer not null
        constraint users_pk
        primary key,
    username varchar not null,
    api_key varchar default gen_random_uuid() not null
);

alter table users owner to appledash;

create unique index users_username_uindex
    on users (username);

create table uploads
(
    id serial not null
        constraint uploads_pk
        primary key,
    user_id integer
        constraint uploads_users_id_fk
        references users
        on update restrict on delete restrict,
    host_id integer
        constraint uploads_hosts_id_fk
        references hosts
        on update restrict on delete restrict,
    original_filename varchar not null,
    sha512_hash varchar not null,
    slug varchar not null
);

alter table uploads owner to appledash;

create unique index uploads_slug_uindex
    on uploads (slug);

