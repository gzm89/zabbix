<?php

/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
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


require_once 'vendor/autoload.php';

require_once dirname(__FILE__).'/../../../include/defines.inc.php';
require_once dirname(__FILE__).'/../../../include/hosts.inc.php';

class CAPIScimHelper extends CAPIHelper {

	/**
	 * Make API SCIM call.
	 *
	 * @param mixed   $data        String containing request data as json.
	 * @param string  $auth_token  Authorization token.
	 *
	 * @return array
	 *
	 * @throws Exception      if API SCIM call fails.
	 */
	public static function callRaw($data, string $auth_token = null): array {
		[$class, $method_type] = explode('.', $data['method'], 2) + ['', ''];

		$url = PHPUNIT_URL.'api_scim.php/'.ucfirst($class).'s';

		if (is_array($data['params'])) {
			$data = json_encode($data['params']);
		}

		$debug = [
			'request' => $data,
			'response' => null
		];

		$params = [
			'http' => [
				'method' => strtoupper($method_type),
				'content' => $data,
				'header' => [
					'Content-type: application/json',
					'Content-Length: '.strlen($data)
				],
				'ignore_errors' => '1'
			]
		];

		if ($auth_token !== null) {
			$params['http']['header'][] = 'Authorization: Bearer '.$auth_token;
		}

		$handle = @fopen($url, 'rb', false, stream_context_create($params));
		if ($handle) {
			$response = @stream_get_contents($handle);
			fclose($handle);
		}

		$debug['response'] = $response;

		if ($response !== '') {
			$response = json_decode($response, true);

			if (!is_array($response)) {
				throw new Exception('API response is not in JSON format');
			}
		}

		static::$debug[] = $debug;

		return $response;
	}

	/**
	 * Prepare request for SCIM API call and make SCIM API call (@see callRaw).
	 *
	 * @param string $method              SCIM API method to be called.
	 * @param array  $params              SCIM API call params.
	 *
	 * @return array
	 */
	public static function call($method, $params) {
		$token = $params['token'];
		unset($params['token']);

		return static::callRaw([
			'method' => $method,
			'params' => $params,
		], $token);
	}
}
