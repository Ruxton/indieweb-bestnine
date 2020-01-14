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
	  <?php include "header.php"; ?>
		<form action="process.php" method="POST" class="ui large form">
			<div class="ui stacked segment">
				<div class="field">
					<label for="uri">URI: </label>
					<input name="uri" id="uri" type="url" placeholder="Indieweb URL" autocorrect="off" autocapitalize="off">
				</div>
				<div class="field">
					<label for="year">Year: </label>
					<select name="year" id="year" class="ui form-control">
						<?php
							$current_year = date('Y');
							$date_range = range($current_year, $current_year-5);
							foreach($date_range as $date) {
								$selected = ($date == $current_year-1) ? ' SELECTED' : '';
								?>
								<option value="<?php echo $date; ?>"<?php echo $selected;?>><?php echo $date; ?></option><?php
							}
						?>
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
