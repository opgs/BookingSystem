<?php

error_reporting(-1);
ini_set('display_errors', 'On');

$SITE = new stdClass();

require('sys/lib/opgslib.php');

$SETTINGS = new INI('settings.ini', 'bookingsettingsdat');

foreach($SETTINGS->getIni()['site'] as $key => $value)
{
	$SITE->$key = htmlspecialchars($value);
}

$AD = new ADFS($SITE->simplesamlpath, $SITE->adfsname, $SITE->adfsAdminGroup);
$AD->forceAuth();
$page = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : "";
if(!($AD->checkGroup('Teachers') || $AD->checkGroup('Support Staff')) && ($page != 'accessdenied'))
{
	header('Location: ' . $SITE->path . '/index.php?page=accessdenied');
	exit();
}

header('Location: https://my.opgs.org/booking/Pages/default.aspx');
exit();

?>
