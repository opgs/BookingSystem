<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $SITE->title; ?></title>
<script type="text/javascript">
var dwidth = (window.innerWidth > 0) ? window.innerWidth : screen.width;
if(dwidth < 750)
{
	<?php if(!$mobile){ echo'window.location = window.location + "&m=1";'; } ?>
}
</script>
<link rel="stylesheet" type="text/css" href="<?php echo $SITE->path ?>/theme/css/opgs.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $SITE->path ?>/js/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo $SITE->path; ?>/theme/css/<?php echo htmlspecialchars(isset($_GET['page']) ? $_GET['page'] : "") ?>.css" />
<?php if($AD->isAdmin()){ ?>
<link rel="stylesheet" type="text/css" href="<?php echo $SITE->path ?>/js/jquery.dataTables.min.css"><?php } echo PHP_EOL; ?>
<script type="text/javascript" src="<?php echo $SITE->path ?>/js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="<?php echo $SITE->path ?>/js/jquery-ui.min.js"></script>
<?php if($AD->isAdmin()){ ?><script type="text/javascript" src="<?php echo $SITE->path ?>/js/jquery.dataTables.min.js"></script><?php echo PHP_EOL; } ?>
</head>
<body>
<script type="text/javascript">
$(document).ready(function(){
	<?php if($mobile){ ?>
	$('.accordion-trigger a').on('click', function(e) {
		e.preventDefault();

		// cache the link so you don't have to keep getting a jQuery object
		var triggerDOM = $(this);

		if(triggerDOM.hasClass('closed')) {

			// find the associated accordion content and add the open class to it to trigger the animation
			triggerDOM.closest('.accordion-trigger').next('.accordion-content').addClass('content-open');
			// update the trigger
			triggerDOM.removeClass('closed').addClass('open');

		} else if(triggerDOM.hasClass('open')) {

			// if the accordion is already open, then close it
			triggerDOM.removeClass('open').addClass('closed');
			// remove class from content to trigger close animation
			triggerDOM.closest('.accordion-trigger').next('.accordion-content').removeClass('content-open');
		}
	});
	<?php } ?>
});
</script>
<div id="container">
<?php if($AD->isAdmin()){echo '<div id="topLinks"><a href="' , $SITE->path , '/?page=admin-summary">Admin</a></div>';} ?>
<div id="content">
<?php
$header = ob_get_clean();
?>
