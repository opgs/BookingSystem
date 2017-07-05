<?php
ob_start();
?>

<h1>Access Denied</h1>

<p>
Only members of staff have access to the room booking system
</p>

<?php
$body = ob_get_clean();
?>
