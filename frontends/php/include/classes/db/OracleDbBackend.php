<?php
/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * Database backend class for Oracle.
 */
class OracleDbBackend extends DbBackend {

	/**
	 * Check if 'dbversion' table exists.
	 *
	 * @return boolean
	 */
	protected function checkDbVersionTable() {
		$tableExists = DBfetch(DBselect("SELECT table_name FROM user_tables WHERE table_name='DBVERSION'"));

		if (!$tableExists) {
			$this->setError(_('The frontend does not match Zabbix database.'));
			return false;
		}

		return true;
	}

	/**
	 * Check is current connection contain requested cipher list.
	 *
	 * @return bool
	 */
	public function isConnectionSecure() {
		return false;
	}

	/**
	 * Create connection to database server.
	 *
	 * @param string $host         Host name.
	 * @param string $port         Port.
	 * @param string $user         User name.
	 * @param string $password     Password.
	 * @param string $dbname       Database name.
	 * @param string $schema       DB schema.
	 *
	 * @param
	 * @return resource|null
	 */
	public function connect($host, $port, $user, $password, $dbname, $schema) {
		$connect = '';
		if ($host !== '') {
			$connect = '//'.$host;

			if ($port != '0') {
				$connect .= ':'.$port;
			}
			if ($dbname) {
				$connect .= '/'.$dbname;
			}
		}

		$resource = @oci_connect($user, $password, $connect, 'UTF8');

		if (!$resource) {
			$ociError = oci_error();
			$error = 'Error connecting to database: '.$ociError['message'];
			return null;
		}

		return $resource;
	}

	/**
	 * Initialize connection.
	 *
	 * @return bool
	 */
	public function init() {
		DBexecute('ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.zbx_dbstr('. '));
	}

	/**
	 * Create INSERT SQL query.
	 * Creation example:
	 *	BEGIN
	 *	INSERT INTO usrgrp (usrgrpid, name, gui_access, users_status, debug_mode)
	 *		VALUES ('20', 'admins', '1', '0', '1');
	 *	INSERT INTO usrgrp (usrgrpid, name, gui_access, users_status, debug_mode)
	 *		VALUES ('21', 'users', '0', '0', '0');
	 *  END;
	 */
	public function createInsertQuery($table, array $fields, array $values) {
		$sql = 'BEGIN';
		$fields = '('.implode(',', $fields).')';
		foreach ($values as $row) {
			$sql .= ' INSERT INTO '.$table.' '.$fields.' VALUES ('.implode(',', array_values($row)).');';
		}
		$sql .= ' END;';

		return $sql;
	}
}
