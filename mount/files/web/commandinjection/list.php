<html>

<body>

	<b>File in path are: </b><br><pre>
	<?php 

		$cmd = "ls -alh ".$_REQUEST['path'];
		passthru($cmd);

	?></pre>
</body>

</html>


