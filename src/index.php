<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title> Timeline Bot </title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<?php
		// Grabs the URI and breaks it apart in case we have querystring stuff
		$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
		// Route it up!
		switch ($request_uri[0]) {
			// // Home page
			case '/':
				require 'home.php';
				break;
			// About page
			case '/about':
				require 'about.php';
				break;
			// Everything else
			default:
				header('HTTP/1.0 404 Not Found');
			// 	require '../views/404.php';
				break;
		}
	?>
	
</body>
</html>