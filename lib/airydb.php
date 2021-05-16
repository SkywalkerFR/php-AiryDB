<?php

/**
 *              ** AiryDB **
 *
 * @since 4 December, 2017
 * @version 1.17 - 21 September 2018
 * @author @SkyEmie_
 *
 * Thanks to this two posts
 * https://stackoverflow.com/questions/16698386/using-string-path-to-delete-element-from-array
 * https://stackoverflow.com/questions/9628176/using-a-string-path-to-set-nested-array-data
 */




/* Init some vars */
if (!isset($airydb_dir_db))    $airydb_dir_db    = 'db';
if (!isset($airydb_dir_log))   $airydb_dir_log   = 'db';
if (!isset($airydb_write_log)) $airydb_write_log = True;





/**
* db_sysname funct.
*
* Return the database's path
*
* @param  string 	$db_name 	a database's name
* @return string 				database's path
*
*/
function db_sysname($db_name) {
	global $airydb_dir_db;
	return($airydb_dir_db.'/'.$db_name.'.db');
}




/**
* db_log funct.
*
* Write errors in a log file
*
* @param  string 	$log 	line of log
*
*/
function db_log($log) {
	global $airydb_dir_log, $airydb_write_log;
	if ($airydb_write_log == True) {
		file_put_contents($airydb_dir_log.'/airydb.log', date("[d/m/Y H:i:s]").'- [line '.debug_backtrace()['1']['line'].' in '.debug_backtrace()['1']['file'].'] - '.$log.PHP_EOL, FILE_APPEND | LOCK_EX);
	}
}




/**
* db_sys_write funct.
*
* Sys funct to write without collisions
*
* @param  string 	$db_name 	a database's name
* @param  string 	$array 		content
*
*/
function db_sys_write($db_name, $array) {

	global $airydb_dir_db, $airydb_dir_log;

	while (file_exists($airydb_dir_db.'/'.$db_name.'.lock')) {

		if (round(microtime(true), 0) - fileatime($airydb_dir_db.'/'.$db_name.'.lock') > 2) unlink($airydb_dir_db.'/'.$db_name.'.lock');

		sleep(0.01);

		if ($airydb_write_log == True) {
			file_put_contents($airydb_dir_log.'/airydb_wait.log', date("[d/m/Y H:i:s]").' - [WAIT WRITE] - '.$db_name.PHP_EOL, FILE_APPEND | LOCK_EX);
		}
	}

	file_put_contents($airydb_dir_db.'/'.$db_name.'.lock', 'lock', LOCK_EX);
	file_put_contents(db_sysname($db_name), gzdeflate(serialize($array)), LOCK_EX);

	unlink($airydb_dir_db.'/'.$db_name.'.lock');
}




/**
* db_exist funct.
*
* Return if a database exists
*
* @param  string 	$db_name 	a database's name
* @return boolean
*
*/
function db_exist($db_name) {
	if (file_exists(db_sysname($db_name))) {
		return(True);
	} else {
		return(False);
	}
}




/**
* db_create funct.
*
* Create a database
*
* @param  string 	$db_name 	a database's name
* @return boolean
*
*/
function db_create($db_name) {
	global $airydb_dir_db;
	if (!db_exist($db_name)) {
		if (!is_dir($airydb_dir_db)) {
			if (!mkdir($airydb_dir_db, 0755, True)) {
				db_log("[db_create] Cannot create the folder '".$airydb_dir_db."'");
				return(False);
			}
		}
		db_sys_write($db_name, array());
		
		if (!db_exist($db_name)) {
			db_log("[db_create] Cannot create the database '".db_sysname($db_name)."'");
			return(False);
		}
		
		if (!chmod(db_sysname($db_name), 0777)) {
			db_log("[db_create] Cannot chmod '".db_sysname($db_name)."'");
			return(False);
		}

		return(True);
	} else {
		db_log("[db_create] Database '".db_sysname($db_name)."' already exists");
		return(False);
	}
}




/**
* db_delete funct.
*
* Delete a database
*
* @param  string 	$db_name 	a database's name
* @return boolean
*
*/
function db_delete($db_name) {

	if (db_exist($db_name)) {
		unlink(db_sysname($db_name));
		return(True);
	} else {
		db_log("[db_delete] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}




/**
* db_reset funct.
*
* Reset a database (delete all content in the database)
*
* @param  string 	$db_name 	a database's name
* @return boolean
*
*/
function db_reset($db_name) {
	if (db_exist($db_name)) {
		db_sys_write($db_name, array());
		return(True);
	} else {
		db_log("[db_reset] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}




/**
* db_get_all funct.
*
* Get all database's content in an array
*
* @param  string 	$db_name 	a database's name
* @return array or boolean
*
*/
function db_get_all($db_name) {

	global $airydb_dir_db, $airydb_dir_log;

	if (db_exist($db_name)) {

		while (file_exists($airydb_dir_db.'/'.$db_name.'.lock')) {

			sleep(0.01);
			
			if ($airydb_write_log == True) {
				file_put_contents($airydb_dir_log.'/airydb_wait.log', date("[d/m/Y H:i:s]").' - [WAIT READ] - '.$db_name.PHP_EOL, FILE_APPEND | LOCK_EX);
			}
		}

		$db_contenu = gzinflate(file_get_contents(db_sysname($db_name)));

		if ($db_contenu) {
			$db_contenu = unserialize($db_contenu);
			if (is_array($db_contenu)) {
				return($db_contenu);

			} else {
				db_log("[db_get_all] Database '".db_sysname($db_name)."' unreadable");
				return(False);
			}

		} else {

			if (filesize(db_sysname($db_name)) == '0') return(array());

			db_log("[db_get_all] Unable to uncompress '".db_sysname($db_name)."'");
			return(False);
		}

	} else {
		db_log("[db_get_all] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}



/**
* db_get_value funct.
*
* Query a value in a database
*
* @param  string 	$db_name 	a database's name
* @param  string 	$path 		path like /dir/dir2/test
* @param  string 	$name 		name associated to the value
* @return string or boolean
*
*/
function db_get_value($db_name, $path = NULL, $name) {

	if ($name == ''){
		db_log("[db_get_value] No name given, database '".db_sysname($db_name)."'");
		return(False);
	}

	if (db_exist($db_name)) {

		$db_contenu = db_get_all($db_name);

		if (is_array($db_contenu)) {
			
			if ($path === NULL) {
				$sub_array = $db_contenu[$name];

			} else {

				if (substr($path, -1) == '/') {
					$path = substr($path, 0, -1);
				}
				if ($path['0'] == '/') {
					$path = substr($path, 1);
				}

				$pathstr = $path;
				$path .= '/'.$name;
				$path = explode("/", $path);
				$sub_array = '';
				foreach ($path as $dir) {

					if (empty($sub_array)) {
						if (isset($db_contenu[$dir])) {
							$sub_array = $db_contenu[$dir];
						} else {
							db_log("[db_get_value] Can't find (0x01) path: '".$pathstr."' name: '".$name."' in '".db_sysname($db_name)."'");
							return(False);
						}
					} else {
						if (isset($sub_array[$dir])) {
							$sub_array = $sub_array[$dir];
						} else {
							db_log("[db_get_value] Can't find (0x02) path: '".$pathstr."' name: '".$name."' in '".db_sysname($db_name)."'");
							return(False);
						}
					}
				}
			}

		} else {
			db_log("[db_get_value] Database '".db_sysname($db_name)."' unreadable");
			return(False);
		}

		if (is_array($sub_array)) {
			db_log("[db_get_value] Path/Name exist but the result is a dir, not a value, in '".db_sysname($db_name)."'");
			return(False);
		} else {
			return($sub_array);
		}
		
	} else {
		db_log("[db_get_value] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}




/**
* db_get_dir funct.
*
* Query a dir in a database
*
* @param  string 	$db_name 	a database's name
* @param  string 	$path 		path like /dir/dir2/test
* @return array or boolean
*
*/
function db_get_dir($db_name, $path = NULL) {
	if (db_exist($db_name)) {

		$db_contenu = db_get_all($db_name);

		if (is_array($db_contenu)) {
			
			if ($path === NULL) {
				$sub_array = $db_contenu;

			} else {

				if (substr($path, -1) == '/') {
					$path = substr($path, 0, -1);
				}
				if ($path['0'] == '/') {
					$path = substr($path, 1);
				}

				$pathstr = $path;
				$path = explode("/", $path);
				$sub_array = '';
				foreach ($path as $dir) {

					if (empty($sub_array)) {
						if (isset($db_contenu[$dir])) {
							$sub_array = $db_contenu[$dir];
						} else {
							db_log("[db_get_dir] Can't find (0x01) path: '".$pathstr."' in '".db_sysname($db_name)."'");
							return(False);
						}
					} else {
						if (isset($sub_array[$dir])) {
							$sub_array = $sub_array[$dir];
						} else {
							db_log("[db_get_dir] Can't find (0x02) path: '".$pathstr."' in '".db_sysname($db_name)."'");
							return(False);
						}
					}
				}
			}

		} else {
			db_log("[db_get_dir] Database '".db_sysname($db_name)."' unreadable");
			return(False);
		}

		if (!is_array($sub_array)) {
			db_log("[db_get_dir] Path exist but the result is a value, not a dir, in '".db_sysname($db_name)."'");
			return(False);
		} else {
			return($sub_array);
		}
		
	} else {
		db_log("[db_get_dir] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}




/**
* db_add_value funct.
*
* Add a value in a database
*
* @param  string 	$db_name 	a database's name
* @param  string 	$path 		path like /dir/dir2/test
* @param  string 	$name 		name associated to the value
* @param  string 	$value 		value associated to the name
* @return boolean
*
*/
function db_add_value($db_name, $path = NULL, $name = NULL, $value = NULL) {
	if (db_exist($db_name)) {

		$db_contenu = db_get_all($db_name);

		if (is_array($db_contenu)) {
			
			if ($path === NULL && $name === NULL) {
				$db_contenu[] = $value;

			} elseif ($path === NULL) {
				$db_contenu[$name] = $value;

			} else {

				if (substr($path, -1) == '/') {
					$path = substr($path, 0, -1);
				}
				if ($path['0'] == '/') {
					$path = substr($path, 1);
				}
				if (!$name == NULL || $name === 0) {
					$path .= '/'.$name;
				}

				$path = explode("/", $path);
				$db_gen = &$db_contenu;

				foreach($path as $dir) {
					$db_gen = &$db_gen[$dir];
				}
				$db_gen = $value;
				unset($db_gen);

				db_sys_write($db_name, $db_contenu);
				return(True);
			}

		} else {
			db_log("[db_add_value] Database '".db_sysname($db_name)."' unreadable");
			return(False);
		}
		
		db_sys_write($db_name, $db_contenu);
		return(True);

	} else {
		db_log("[db_add_value] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}




/**
* db_del_value funct.
*
* Delete a value in a database
*
* @param  string 	$db_name 	a database's name
* @param  string 	$path 		path like /dir/dir2/test
* @param  string 	$name 		name associated to the value
* @return boolean
*
*/
function db_del_value($db_name, $path, $name){
	if (db_exist($db_name)) {

		$db_contenu = db_get_all($db_name);

		if (is_array($db_contenu)) {

			if (substr($path, -1) == '/') {
				$path = substr($path, 0, -1);
			}
			if ($path['0'] == '/') {
				$path = substr($path, 1);
			}

			$path .= '/'.$name;
			$pathstr = $path;
			$path = explode("/", $path);
			$db_genprev = NULL;
			$db_gen = &$db_contenu;

			foreach ($path as &$dir){
				$db_genprev = &$db_gen;
				
				if (!isset($db_genprev[$dir])) {
					db_log("[db_del_value] Can't find path: '".$pathstr."' in '".db_sysname($db_name)."'");
					return(False);
				}

				$db_gen = &$db_gen[$dir];
			}

			if (is_array($db_gen)) {
				db_log("[db_del_value] Path/Name exist but the result is a dir, not a value, in '".db_sysname($db_name)."'");
				return(False);
			}

			if ($db_genprev !== NULL) {
				unset($db_genprev[$dir]);
			}

			db_sys_write($db_name, $db_contenu);
			return(True);

		} else {
			db_log("[db_del_value] Database '".db_sysname($db_name)."' unreadable");
			return(False);
		}

		db_sys_write($db_name, $db_contenu);
		return(True);

	} else {
		db_log("[db_del_value] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}




/**
* db_add_dir funct.
*
* Add a dir in a database
*
* @param  string 	$db_name 	a database's name
* @param  string 	$path 		path like /dir/dir2/test
* @return boolean
*
*/
function db_add_dir($db_name, $path) {
	if (db_exist($db_name)) {

		$db_contenu = db_get_all($db_name);

		if (is_array($db_contenu)) {

			if (substr($path, -1) == '/') {
				$path = substr($path, 0, -1);
			}
			if ($path['0'] == '/') {
				$path = substr($path, 1);
			}

			$path = explode("/", $path);
			$db_gen = &$db_contenu;

			foreach($path as $dir) {
				$db_gen = &$db_gen[$dir];
			}

			unset($db_gen);

			db_sys_write($db_name, $db_contenu);
			return(True);

		} else {
			db_log("[db_add_dir] Database '".db_sysname($db_name)."' unreadable");
			return(False);
		}
		
		db_sys_write($db_name, $db_contenu);
		return(True);

	} else {
		db_log("[db_add_dir] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}




/**
* db_del_dir funct.
*
* Delete a dir in a database
*
* @param  string 	$db_name 	a database's name
* @param  string 	$path 		path like /dir/dir2/test
* @return boolean
*
*/
function db_del_dir($db_name, $path){
	if (db_exist($db_name)) {

		$db_contenu = db_get_all($db_name);

		if (is_array($db_contenu)) {

			if (substr($path, -1) == '/') {
				$path = substr($path, 0, -1);
			}
			if ($path['0'] == '/') {
				$path = substr($path, 1);
			}

			$pathstr = $path;
			$path = explode("/", $path);
			$db_genprev = NULL;
			$db_gen = &$db_contenu;

			foreach ($path as &$dir){
				$db_genprev = &$db_gen;

				if (!isset($db_genprev[$dir]) && array_key_exists($dir, $db_genprev) == False) {
					/* array_key_exists to check if it's not an empty dir, tnks Ozachi ^^ */
					db_log("[db_del_dir] Can't find path: '".$pathstr."' in '".db_sysname($db_name)."'");
					return(False);

				}elseif (!is_array($db_genprev[$dir]) && $db_genprev[$dir] !== NULL) {
					db_log("[db_del_dir] Path exist but the result is a value, not a dir, in '".db_sysname($db_name)."'");
					return(False);
				}

				$db_gen = &$db_gen[$dir];
			}

			if ($db_genprev !== NULL) {
				unset($db_genprev[$dir]);
			}

			db_sys_write($db_name, $db_contenu);
			return(True);

		} else {
			db_log("[db_del_dir] Database '".db_sysname($db_name)."' unreadable");
			return(False);
		}

		db_sys_write($db_name, $db_contenu);
		return(True);

	} else {
		db_log("[db_del_dir] Database '".db_sysname($db_name)."' does not exist");
		return(False);
	}
}




/**
* db_exist_value funct.
*
* Check if a value exist in a database
*
* @param  string 	$db_name 	a database's name
* @param  string 	$path 		path like /dir/dir2/test
* @param  string 	$name 		name associated to the value
* @return string or boolean
*
*/
function db_exist_value($db_name, $path, $name) {
	if (db_get_value($db_name, $path)) {
		return(True);
	} else {
		return(False);
	}
}




/**
* db_exist_dir funct.
*
* Check if a dir exist in a database
*
* @param  string 	$db_name 	a database's name
* @param  string 	$path 		path like /dir/dir2/test
* @return array or boolean
*
*/
function db_exist_dir($db_name, $path) {
	if (db_get_dir($db_name, $path)) {
		return(True);
	} else {
		return(False);
	}
}

?>
