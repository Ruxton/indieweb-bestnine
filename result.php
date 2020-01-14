<?php
	include "config.php";
	$host = $_SERVER['HTTP_HOST'];
	$path = "/indieweb-bestnine";
	// $path = "";

	if(isset($_GET['key'])) {
		$key = $_GET['key'];
	} else {
		header("Location: http://".$host.$path."/");
		exit;
	}
?><html>
<head>
	<title>Your Indieweb BestNine Image</title>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.13/semantic.min.css" />
	<style type="text/css">
		body {
			background-color: #DADADA;
		}
		body > .grid {
			height: 100%;
		}
		.image {
			margin-top: -100px;
		}
		.column {
			width: 450px !important;
		}
	</style>
</head>
<body>
<div class="ui middle aligned center aligned grid">
	<div class="column">
		<h1 class="ui header">Indieweb BestNine</a></h1>
		<h2 class="ui small header">Get your best nine photos<br>on your Indieweb site!</h2>

		<p>Your BestNine image will be displayed below when it is completed.</p>

		<div id="progress">
			<p>Creating your photo</p>
			<img src="ajax-loader.gif" />
		</div>
		<div id="image-container">
		</div>


		<?php include "footer.php"; ?>
	</div>
</div>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.13/semantic.min.js"></script>
<script type="text/javascript">
	function check_image() {
		$.ajax({
			url: 'http://<?php echo $host.$path; ?>/images/<?php echo $key ?>.jpg',
			type: 'HEAD',
			success:
				function(){
						 $('#image-container').append('<img id="bestnine" width="450" src="http://<?php echo $host.$path; ?>/images/<?php echo $key ?>.jpg" />');
						 $('#progress').remove();
				},
			error:
				function(){
					setTimeout(function() { check_image(); }, 20000);
				}
		});
	}

	$(function() {
		check_image();
	});
</script>
</body>
</html>
