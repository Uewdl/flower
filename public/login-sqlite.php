<?php

/** Enable login for SQLite
* @link https://www.adminer.org/plugins/#use
* @author Jakub Vrana, https://www.vrana.cz/
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/

function adminer_object(){
class AdminerLoginSqlite extends Adminer{
	/** @access protected */
	var $login, $password_hash;
	
	/** Set allowed credentials
	* @param string
	* @param string result of password_hash
	*/
	function __construct($login, $password_hash) {
		$this->login = $login;
		$this->password_hash = $password_hash;
	}
	
	function login($login, $password) {
		return true;
		if (DRIVER != "sqlite" && DRIVER != "sqlite2") {
			return true;
		}
		return $this->login == $login && password_verify($password, $this->password_hash);
	}

}

return new AdminerLoginSqlite(NULL,NULL);

};

include 'adminer.php';