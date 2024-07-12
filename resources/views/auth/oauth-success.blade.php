<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>OAuth Success</title>
</head>

<body>
	<h1>OAuth Success</h1>
	<p> You have successfully authenticated. Please return to Figma to continue.</p>

	<script>
		// Send the read key to the parent window
		window.parent.postMessage({
			readKey: '{{$readKey}}'
		}, '*');
	</script>
</body>

</html>