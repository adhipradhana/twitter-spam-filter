<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title> Homepage </title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.1.0/litera/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />

</head>
<body>
	<div class="container">
		<div class="jumbotron">
			<h1 class="text-center" style="font-size : 75px"> TWITTER SPAM DETECTOR </h1>
		</div>

		<form action = "<?php $_PHP_SELF ?>" method = "POST" id="main-form">
		
			<label for="select" style="color: white">Select Algorithm</label>
			<select class="form-control" id="select" form="main-form" name="algorithm">
				<option value="0">Boyer-Moore</option>
				<option value="1">KMP</option>
				<option value="2">Regex</option>
			</select>
			<br>
			<label for="username-container" style="color: white">Username</label>
			<textarea class="form-control" id="username-container" name="username" rows="1"></textarea>
			<br>
			<label for="keyword-container" style="color: white">Keyword</label>
			<textarea class="form-control" id="keyword-container" name="keyword" rows="1"></textarea>
			<br>
			<div class="checkbox" style="color: white">
				<label><input type="checkbox" id="case-sensitive" name="case-sensitive"> Case Sensitive</label>
			</div>
			<br>
			<button type="submit" class="btn btn-primary btn-block">Submit</button>
      	</form>
	</div>

	<br>

	<?php
		function highlightTweet($data) {
			$length = strlen($data->text);
			$keywordLength = $data->keyword_length;
			$buff = "";

			for ($i = 0; $i < $length; $i++) {
				if (in_array($i, $data->index)) {
					$buff = $buff . "<mark>";
					for ($j = $i; $j < $i + $keywordLength; $j++) {
						$buff = $buff . $data->text[$j];  
					}
					$buff = $buff . "</mark>";
					$i += $keywordLength;
					$buff = $buff . $data->text[$i];  
				} else {
					$buff = $buff . $data->text[$i];  
				}
			}

			return $buff;
		}

		function createTweet($data, $keyword){
			$spamText = "<div class=\"alert alert-info\"> Not Spam </div>";
			$tweet_text = $data->text;
			if($data->spam){
				$spamText = "<div class=\"alert alert-danger\"> Spam </div>";
				$tweet_text = highlightTweet($data);
			}
			echo "<div class=\"tweet-box container block-center\">
				<div class=\"row\">
				<div class=\"col-md-12 strip\" style=\"color: white\">
					$spamText
				</div>
				<div class=\"col-md-12 strip\" style=\"color: white\">
					<img src=\"". $data->image. "\" alt=\"profile image\" class=\"rounded\" />
					<strong>". $data->name. "</strong>
					<span class=\"light\"> @".$data->username." </span>
					<br> ".
					$tweet_text .
					"<div class=\"summary\">
						<span class=\"buttons\">
							<a href=\"#\"><i class=\"fa fa-reply\"></i> Reply</a>
							<a href=\"#\"><i class=\"fa fa-retweet\"></i> Retweet</a>
							<a href=\"#\"><i class=\"fa fa-star\"></i> Favourite</a>
							<a href=\"#\"><i class=\"fa fa-ellipsis-h\"></i> More</a>
						</span>
						<a style=\"color: #25e8e1\" href=\"http://twitter.com/". $data->username. "/status/". $data->id. "\"><i class=\"fa fa-file-o\"></i> View summary</a>
					</div>
				</div>
			</div>
		</div>
		<br>
		<br>";
		}
	?>
	</div>

	<?php
		$url = 'http://0.0.0.0:1111/';
		if(isset($_POST["username"])){
			if( $_POST["username"] && $_POST["keyword"]) {
				get_data($url, $_POST);
				exit();
			}
		}

		function get_data($url, $data) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			
			// execute!
			$response = curl_exec($ch);
			
			// close the connection, release resources used
			curl_close($ch);
			
			// do anything you want with your response
			$arr = json_decode($response);
			$keyword = $arr->{'keyword'};
			if ($arr->{'empty'}) {
				echo " <div class=\"container\">
							<div class=\"alert alert-danger\"> 
								<strong> User not found </strong>
							</div> 
						</div>";
			} else {
				foreach ( $arr->{'data'} as $data ){
					createTweet($data, $arr->{'keyword'});
				}
			}
		}
	?>
</body>
</html>