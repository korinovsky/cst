/**
 * Database schema required by \cst\caching\DbCache.
 */
if object_id('[cache]', 'U') is not null
    drop table [cache];

drop table if exists [cache];

create table [cache]
(
    [id]  varchar(128) not null,
    [expire] integer,
    [data]   BLOB,
    primary key ([id])
);
