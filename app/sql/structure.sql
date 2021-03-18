DROP TABLE IF EXISTS "CreditCard";
DROP TABLE IF EXISTS "Userpassword";
DROP TABLE IF EXISTS "Password";
DROP TABLE IF EXISTS "Usecase";
DROP TABLE IF EXISTS "User";
DROP TABLE IF EXISTS "Userpassword";

create table "CreditCard"
(
    "CreditCardNumber" varchar not null,
    "ExpirationDate"   varchar not null,
    cvc                integer not null,
    "UserId"           varchar not null,
    "isFavorite"       integer,
    "Provider"         varchar,
    "Id"               varchar not null
        constraint creditcard_pk
            primary key
);

create unique index creditcard_id_uindex
    on "CreditCard" ("Id");


create table "Password"
(
    passwordid   varchar not null
        constraint table_name_pk
            primary key,
    userid       varchar not null,
    passwordtext varchar not null,
    usecaseid    varchar not null,
    "isFavorite" varchar
);


create table "Usecase"
(
    usecaseid varchar not null
        constraint usecase_pk
            primary key,
    url       varchar not null
);

create unique index usecase_usecaseid_uindex
    on "Usecase" (usecaseid);


create table "User"
(
    username      varchar,
    email         varchar,
    lastname      varchar,
    firstname     varchar,
    loginpassword varchar,
    user_id       varchar(255) not null
        constraint user_pk
            primary key
);

create unique index user_user_id_uindex
    on "User" (user_id);


create table "Userpassword"
(
    userid        varchar not null
        constraint userpassword_user_user_id_fk
            references "User",
    passwordid    varchar not null
        constraint userpassword___password
            references "Password"
            on delete cascade,
    passwordvalue varchar
);








