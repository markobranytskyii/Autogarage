<?php

$cfg['Servers'][1]['auth_type'] = 'config';
$cfg['Servers'][1]['host'] = 'mariadb';
$cfg['Servers'][1]['connect_type'] = 'tcp';
$cfg['Servers'][1]['compress'] = false;
$cfg['Servers'][1]['user'] = 'root';
$cfg['Servers'][1]['password'] = getenv('MYSQL_ROOT_PASSWORD');
$cfg['Servers'][1]['hide_db'] = 'information_schema|mysql|performance_schema|sys';
