<?php
/*
OPGS Booking v1 12/01/2016	L Bridges
OPGS Booking v2 25/01/2016	L Bridges
	- Added block booking creation for admins (admin/blockbooking)
	- Moved LDAP into LDAP class
	- Added bcc support@opgs.org option
	- Logging by recent first
OPGS Booking v3 27/01/2016	L Bridges
	- Moved SQL to common connection
	- Made logs searchable and sortable
	- Fixed room closure for AM and PM
OPGS Booking v4 27/01/2016  L Bridges
	- Common LDAP bind
	- Editing/override name fix
	- SQL connection settings to ini
OPGS Booking v5 28/01/2016  L Bridges
	- Block booking deletion
	- Times in logs
	- Admin actions logged
OPGS Booking v6 ??/??/2016  L Bridges
	- Moved libraries to lib
	- Added detection of &page=
	- Changed from displayName to using Id's
	- Display names from ou list
	- Log sorted by newest first by default
OPGS Booking v7 16/09/2016  L Bridges
	- Updated JS versions, moved to cloudflare cdn for all
	- Staff codes now appear in the override/edit page
	- Added "loading spinner"
	- Caching dates.dat, settings.ini, timetable
	- Cache adfs admin check and attributes
	- Can close room between any two periods
OPGS Booking v8 30/09/2016  L Bridges
	- Moved to jQuery 3+ (3.1.1) and updated jq-ui
	- New lines added into header
	- Fixed spaces in links generated
	- Added charset
OPGS Booking v9 11/10/2016  L Bridges
	- Customizable periods
	- Prettier booking page
	- Recurring bookings
	- Added table stats to admin/summary
OPGS Booking v10 20/10/2016 L Bridges
	- Standardized libs
	- Updated to settings lib v2
OPGS Booking v11 24/10/2016	L Bridges
	- Updates ADFS and LDAP libs to be independent
	- Moved data.php and records.php out of lib folder
*/


header("X-UA-Compatible: IE=edge");

$SITE = new stdClass();

session_start();

ob_start();

require('d:\dev\opgslib\opgslib.php');

$SETTINGS = new INI('settings.ini', 'bookingsettingsdat');
$SETTINGS->getSection('site', $SITE);
$SETTINGS->getSection('yearblocks', $SITE->yearblocks);

$AD = new ADFS($SITE->simplesamlpath, $SITE->adfsname, $SITE->adfsAdminGroup);
$AD->forceAuth();
if(!($AD->checkGroup('Teachers') || $AD->checkGroup('Support Staff')) && htmlspecialchars($_GET['page']) != 'accessdenied')
{
	header('Location: ' . $SITE->path . '/index.php?page=accessdenied');
	exit();
}

$LDAP = new LDAP($SITE->ldap, $SITE->ldapuser, $SITE->ldappass, $SITE->ldapDN);

$SQL = new SQL($SITE->sqlinstance, $SITE->sqldbname, $SITE->sqlusername, $SITE->sqlpassword);

$LOG = new LOG($AD, 'SQL', $SQL);

require('sys/records.php');
require('sys/data.php');

$debug = ob_get_clean();
ob_end_clean();

require('theme/header.php');
require('theme/footer.php');
require('lang/opgs.php');

if(isset($_GET['page']))
{
	if(htmlspecialchars($_GET['page']) == 'booking'){include('sys/booking.php');}else
	if(htmlspecialchars($_GET['page']) == 'accessdenied'){include('sys/accessdenied.php');}else
	if(htmlspecialchars($_GET['page']) == 'admin-summary'){include('sys/admin/summary.php');}else
	if(htmlspecialchars($_GET['page']) == 'admin-blockbooking'){include('sys/admin/blockbooking.php');}else
	if(htmlspecialchars($_GET['page']) == 'admin-termdates'){include('sys/admin/termdates.php');}else
	if(htmlspecialchars($_GET['page']) == 'admin-logs'){include('sys/admin/logs.php');}else
	if(htmlspecialchars($_GET['page']) != 'home'){header('Location: ' . $SITE->path . '/index.php?page=home');exit();}
}else{
	header('Location: ' . $SITE->path . '/index.php?page=home');
	exit();
}

?>
