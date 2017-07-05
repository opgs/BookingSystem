<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require('sys/lib/opgslib.php');

$SETTINGS = new INI('settings.ini', 'bookingsettingsdat');

foreach($SETTINGS->getIni()['site'] as $key => $value)
{
	$SITE->$key = htmlspecialchars($value);
}

$AD = new ADFS($SITE->simplesamlpath, 'opgadfs');
$AD->forceAuth();
if(!($AD->checkGroup('Teachers') || $AD->checkGroup('Support Staff')) && htmlspecialchars($_GET['page']) != 'accessdenied')
{
	header('Location: ' . $SITE->path . '/index.php?page=accessdenied');
	exit();
}

header('Location: https://my.opgs.org/booking/Pages/default.aspx');
exit();

?>
