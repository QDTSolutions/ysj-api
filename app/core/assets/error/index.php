<?php
if (!defined('ABSPATH')) {
	die('LRVTipsy Security');
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
	<title><?= $title ?></title>
	<link rel="shortcut icon" href="./app/views/assets/images/laravel.png">
	<meta content="Laravel Admin" name="description" />
	<meta content="Laravel" name="author" />
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,900" rel="stylesheet">
	<link href="<?php echo site_url('app/core/assets/error/css/style.css');
				?>" rel="stylesheet" type="text/css">
</head>

<body>
	<div class="form-body no-side">
		<div class="row">
			<div class="form-holder">
				<div class="form-content">
					<div class="form-items">
						<div class="spacer"></div>
						<img src="./app/views/assets/images/laravel.png" alt="Laravel" class="logo" style="max-width: 250px;">
						<h1 class="mt-4">Laravel API | Error</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript" src="<?php echo site_url('app/core/assets/error/js/plugins.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo site_url('app/core/assets/error/js/actions.js'); ?>"></script>
</body>

</html>