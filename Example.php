<?php

require('lib/airydb.php');

/**
 * Have fun !! 
*/ 


/** 
 * Here you can change the default values 
 * They are not required, you can just add them after require() if you want to change the default values

$airydb_dir_db    = 'db';
$airydb_dir_log   = 'db';
$airydb_write_log = True;
*/


if (db_exist('user')) {
	db_reset('user');
} else {
	db_create('user');
}

db_add_dir('user', '/today/user123/privacy');
db_add_value('user', '/today/user123', 'email', 'jambon@gmail.com');
db_add_value('user', '/today/user123', 'phone', '0123456789');
db_add_value('user', '/today/user123', 'rip', 'so_sad');
db_add_value('user', '/today/user123/privacy', 'hash_password', 'SXQncyBhIHByYW5rIGJybyAhISB4RA==');

db_add_dir('user', '/today/Couscous/garbit');

db_add_dir('user', '/today/Couscous/rip');
db_del_dir('user', '/today/Couscous/rip'); /* For the science.. */

db_del_value('user', '/today/SkywalkerFR', 'rip');

$arrayUser1 = array(
	'username' => 'Arthur',
	'perm'     => '0x02',
	'border'   => '#00ff00',
	'pp'       => '/var/www/html/data/pp_123.png',
	'banner'   => False,
);

$arrayUser2 = array(
	'username' => 'Elodie',
	'perm'     => '0x01',
	'border'   => '#112233',
	'pp'       => '/var/www/html/data/pp_1221.png',
	'banner'   => True,
);

db_add_value('user', NULL, NULL, $arrayUser1);
db_add_value('user', NULL, NULL, $arrayUser2);


$hash_password = db_get_value('user', '/today/user123/privacy', 'hash_password');
$user_array    = db_get_dir('user', '/today/user123');
$db_content    = db_get_all('user');



echo 'Hash password : <pre>'.$hash_password.'</pre><hr>';

echo 'user123\'s array : <pre>';
print_r($user_array);
echo '</pre><hr>';

echo 'All content in the database : <pre>';
print_r($db_content);
echo '</pre><hr>';

?>
