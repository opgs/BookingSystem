<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $SITE->title; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $SITE->path ?>/theme/css/opgs.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $SITE->path ?>/js/jquery-ui.min.css">
<?php if($AD->isAdmin()){ ?>
<link rel="stylesheet" type="text/css" href="<?php echo $SITE->path ?>/js/jquery.dataTables.min.css"><?php } echo PHP_EOL; ?>
<link rel="stylesheet" type="text/css" href="<?php echo $SITE->path ?>/theme/css/<?php echo htmlspecialchars($_GET['page']) ?>.css" />
<script type="text/javascript" src="<?php echo $SITE->path ?>/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="<?php echo $SITE->path ?>/js/jquery-ui.min.js"></script>
<?php if($AD->isAdmin()){ ?><script type="text/javascript" src="<?php echo $SITE->path ?>/js/jquery.dataTables.min.js"></script><?php echo PHP_EOL; } ?>
</head>
<body>
<div id="container">
<?php if($AD->isAdmin()){echo '<div id="topLinks"><a href="' . $SITE->path . '/?page=admin-summary">Admin</a></div>';} ?>
<div id="content">
<?php
$header = ob_get_clean();
?>
