-- we don't know how to generate root <with-no-name> (class Root) :(
create table quote_authors
(
    author_id   uuid default gen_random_uuid() not null
        primary key,
    author_name varchar(200)                   not null
);

create table quotes
(
    quote_id     uuid default gen_random_uuid() not null
        primary key,
    quote_text   varchar(300)                   not null,
    quote_author uuid
        constraint fk_q_author
            references quote_authors,
    constraint developer_quotes__ui_author_quote
        unique (quote_author, quote_text)
);

create table quote_users
(
    user_id   uuid default gen_random_uuid() not null
        primary key,
    full_name varchar(36)                    not null,
    mobile_number varchar(18)                null,
    email_address text                       null
);

create unique index quote_users__ui_email_address
    on quote_users (lower(email_address));

create unique index quote_users__ui_mobile_number
    on quote_users (mobile_number);

create table quote_views
(
    quote_id uuid not null
        constraint fk_qv_quote
            references quotes,
    user_id  uuid not null
        constraint fk_qv_user
            references quote_users,
    primary key (quote_id, user_id)
);

