<?php

require('lib/AiryDB.lib');
require('lib/print_array.lib');

$mosh = db_get_all('db_test');

print_array($mosh.'fff');




?>