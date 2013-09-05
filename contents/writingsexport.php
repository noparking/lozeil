<?php
/*
	opentime
	$Author: perrick $
	$URL: svn://svn.noparking.net/var/repos/opentime/contents/contactexport.php $
	$Revision:197 $

	Copyright (C) No Parking 2001 - 2011
*/

$dbExport = new db();
$querywhere = "";

if (!empty($_POST['date_picker_from']['m'])) {
	$from = $_POST['date_picker_from'];
	$querywhere .= " AND ".$dbExport->config['table_writings'].".day >= ".mktime(0, 0, 0, $from['m'], $from['d'], $from['Y']);
}
if (!empty($_POST['date_picker_to']['m'])) {
	$to = $_POST['date_picker_to'];
	$querywhere .= " AND ".$dbExport->config['table_writings'].".day <= ".mktime(0, 0, 0, $to['m'], $to['d'], $to['Y']);
}

$query_export = "SELECT ".
$dbExport->config['table_writings'].".day as ".__('date').", ".
$dbExport->config['table_writings'].".amount_excl_vat as ".__('amount excluding vat').", ".
$dbExport->config['table_writings'].".vat as ".__('VAT').", ".
$dbExport->config['table_writings'].".amount_inc_vat as ".__('amount including vat').", ".
$dbExport->config['table_banks'].".name as ".__('bank').", ".
$dbExport->config['table_categories'].".name as ".__('category').", ".
$dbExport->config['table_sources'].".name as ".__('source').", ".
$dbExport->config['table_types'].".name as ".__('type').", ".
$dbExport->config['table_writings'].".comment as ".__('comment')." ".
"FROM ".$dbExport->config['table_writings'].
" LEFT JOIN ".$dbExport->config['table_banks'].
" ON ".$dbExport->config['table_banks'].".id = ".$dbExport->config['table_writings'].".banks_id".
" LEFT JOIN ".$dbExport->config['table_categories'].
" ON ".$dbExport->config['table_categories'].".id = ".$dbExport->config['table_writings'].".categories_id".
" LEFT JOIN ".$dbExport->config['table_sources'].
" ON ".$dbExport->config['table_sources'].".id = ".$dbExport->config['table_writings'].".sources_id".
" LEFT JOIN ".$dbExport->config['table_types'].
" ON ".$dbExport->config['table_types'].".id = ".$dbExport->config['table_writings'].".types_id".
" WHERE (1=1)".
$querywhere.
" ORDER BY amount_excl_vat";
$result_export = $dbExport->query($query_export);
if ($result_export[1] > 0) {
	while ($row_export = $dbExport->fetchArray($result_export[0])) {
		if (!isset($title)) {
			$title = array_keys($row_export);
		}
		$value[] = $row_export;
	}
	export_excel($title, $value);
}

header("Location: ".link_content("content=writings.php"));
exit;

