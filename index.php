<html>
<head>
	<title>Indieweb BestNine</title>
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

		<form action="process.php" method="POST" class="ui large form">
			<div class="ui stacked segment">
				<div class="field">
					<label for="uri">URI: </label>
					<input name="uri" id="uri" type="text" placeholder="Indieweb URL" autocorrect="off" autocapitalize="off">
				</div>
				<div class="field">
					<label for="year">Year: </label>
					<select name="year" id="year" class="ui form-control">
						<option value="2018">2018</option>
						<option value="2017" SELECTED>2017</option>
						<option value="2016">2016</option>
						<option value="2015">2015</option>
					</select>
				</div>
				<div class="field">
					<label for="month">Month: </label>
					<select name="month" id="month" class="ui form-control">
						<option value="0" SELECTED>ALL</option>
						<option value="1">January</option>
						<option value="2">February</option>
						<option value="3">March</option>
						<option value="4">April</option>
						<option value="5">May</option>
						<option value="6">June</option>
						<option value="7">July</option>
						<option value="8">August</option>
						<option value="9">September</option>
						<option value="10">October</option>
						<option value="11">November</option>
						<option value="12">December</option>
					</select>
				</div>
				<input class="ui fluid large teal submit button" type="submit" value="Submit" />
			</div>
		</form>
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
