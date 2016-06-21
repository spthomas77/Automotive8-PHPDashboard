<?php
require_once('config.php');

if( IS_LOGGED_IN ) {
	header('Location: campaign.html');
	exit;
} else {
	header('Location: login.html');
	exit;
}
?>


</body>
</html>

