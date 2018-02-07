<?php

require('lib/AiryDB.lib');

/**
 * Have fun !!
 */ 


if (db_exist('user')) {
	db_reset('user');
} else {
	db_create('user');
}

db_add_dir('user', '/today/SkywalkerFR/privacy');
db_add_value('user', '/today/SkywalkerFR', 'email', 'jambon@gmail.com');
db_add_value('user', '/today/SkywalkerFR', 'phone', '0123456789');
db_add_value('user', '/today/SkywalkerFR', 'rip', 'so_sad');
db_add_value('user', '/today/SkywalkerFR/privacy', 'hash_password', 'SXQncyBhIHByYW5rIGJybyAhISB4RA==');

db_add_dir('user', '/today/Couscous/garbit');

db_add_dir('user', '/today/Couscous/rip');
db_del_dir('user', '/today/Couscous/rip'); // For the science, RIP

db_del_value('user', '/today/SkywalkerFR', 'rip');


$hash_password = db_get_value('user', '/today/SkywalkerFR/privacy', 'hash_password');
$user_array    = db_get_dir('user', '/today/SkywalkerFR');
$db_content    = db_get_all('user');



echo 'Hash password : <pre>'.$hash_password.'</pre><hr>';

echo 'SkywalkerFR\'s array : <pre>';
print_r($user_array);
echo '</pre><hr>';

echo 'All content in the database : <pre>';
print_r($db_content);
echo '</pre><hr>';

?>
