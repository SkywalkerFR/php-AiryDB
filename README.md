# AiryDB
It's a tiny lib made for PHP to deploy tiny and portable databases, without mysql server etc..
It's look like the regedit hierarchical structure on Windows

# How to use ?
Just need to add the AiryDB.lib file with a require in you php script :

```php
require('lib/AiryDB.lib');
```
_(Obviously you can change his extension to *.php if you want, it's for me to differentiate my scripts from the libraries to include.)_

# Functions list

Funct                                       |Utility
--------------------------------------------|------
```db_exist($db_name)```                          | Return if a database exists
```db_create($db_name)```                         | Create a database
```db_delete($db_name)```                         | Delete a database
```db_reset($db_name)```                          | Reset a database (delete all content in the database)
```db_get_all($db_name)```                        | Get all database's content in an array
```db_get_value($db_name, $path, $name)```        | Query a value in a database
```db_get_dir($db_name, $path)```                 | Query a dir in a database
```db_add_value($db_name, $path, $name, $value)```| Add a value in a database
```db_del_value($db_name, $path, $name)```        | Delete a value in a database
```db_add_dir($db_name, $path)```                 | Add a dir in a database
```db_del_dir($db_name, $path)```                | Delete a dir in a database

__Have fun!__
