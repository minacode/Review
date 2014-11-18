<#1>
<?php
?>
<#2>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	)
);

$ilDB->createTable("rep_robj_xrev_revobj", $fields);
$ilDB->addPrimaryKey("rep_robj_xrev_revobj", array("id"));
?>