insert into configuration (name, value)
    select 'dbVersion', '0.0.1.0'
;

insert into account (username, email, password, name, admin)
    select 'Admin', 'support@domm.cz', '$2y$10$oANVtIBu2gzp9H0vjD4.meCCuVTqWMvWi7S4Yr4n8HTy0jYF9NcEy', 'Administrátor', true union all
    select 'Domm', 'domm98cz@gmail.com', '$2y$10$oANVtIBu2gzp9H0vjD4.meCCuVTqWMvWi7S4Yr4n8HTy0jYF9NcEy', 'Domm', true
;

