/**
 * Database schema required by \cst\caching\DbCache.
 */

drop table if exists `cache`;

create table `cache`
(
    `id`  varchar(128) not null,
    `expire` integer,
    `data`   LONGBLOB,
    primary key (`id`)
) engine InnoDB;
