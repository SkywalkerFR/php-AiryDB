# php-AiryDB
A tiny lib made for PHP to deploy tiny and portable databases, without mysql server etc..

# How to use ?
Just need to add the AiryDB.lib file with a require in you php script :

```php
require('lib/AiryDB.lib');
```
_(obviously you can change his extension to php if you want, it's for me to differentiate my scripts from the libraries to include.)_

# List of functions :

Funct                                       |Utility
--------------------------------------------|------
db_exist($db_name)                          | Return if a database exist
db_create($db_name)                         | Create a databse
db_delete($db_name)                         | Delete a databse
db_reset($db_name)                          | Reset a databse (delete all content in the database)
db_get_all($db_name)                        | Get all databse's content in an array
db_get_value($db_name, $path, $name)        | Query a value in a databse
db_get_dir($db_name, $path)                 | Query a dir in a databse
db_add_value($db_name, $path, $name, $value)| Add a value in a databse
db_del_value($db_name, $path, $name)        | Delete a value in a databse
db_add_dir($db_name, $path)                 | Add a dir in a databse
db_del_dir($db_name, $path)                 | Delete a dir in a databse

# Have fun !
Tanks !
