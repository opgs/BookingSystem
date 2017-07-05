<?php
if(!$AD->isAdmin())
{
	header('Location: ' . $SITE->path . '/index.php?page=home');
	exit();
}

$LOG->add('Viewed logs', 5);

ob_start();
?>

<?php include('adminmenu.php'); ?>

<script type="text/javascript">
$(document).ready(function(){	
	$('table').dataTable({"pagingType": "full_numbers", "aaSorting": [], "pageLength": 10});
	$('#DataTables_Table_0_filter input').prop("value","").trigger("input");
});
</script>

<table>
<thead><th>Name</th><th>Date</th><th></th></thead>
<tfoot><th>Name</th><th>Date</th><th></th></tfoot>
<?php
$log = $LOG->read();

foreach($log as $entry)
{
	?>
	<tr><td><?php echo $entry['LG_By']; ?></td><td><?php echo $entry['LG_Date']; ?></td><td><pre><?php echo $entry['LG_Entry']; ?></pre></td></tr>
	<?php
}

unset($query, $tt);

?>
</table>

<?php
$body = ob_get_clean();
?>
