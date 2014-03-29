<html>

<body>

	<b>File in path are: </b><br><pre>
	<?php 

		$cmd = "ls -alh ".str_replace(';', ' ', $_REQUEST['path']);
		passthru($cmd);

	?></pre>
</body>

</html>


