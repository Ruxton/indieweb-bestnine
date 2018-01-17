<?php
	$host = $_SERVER['HTTP_HOST'];
	$path = "/indieweb-bestnine";
	// $path = "";
?>
<html>
<head>
	<title>Indieweb BestNine - No URL!</title>
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
		<div class="ui stacked segment">
			<div class="ui error message">
				<p>The website you are trying use is not contactable.</p>
				<p>Please <a href="http://<?php echo $host.$path; ?>/">try another URL.</a></p>
			</div>
		</div>
		<?php include "footer.php"; ?>
	</div>
</div>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.13/semantic.min.js"></script>
<script type="text/javascript">
	$('select')
	  .dropdown()
	;
</script>
</body>
</html>
