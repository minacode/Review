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
<#3>
<?php
?>
<#4>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'timestamp' => array(
		'type' => 'timestamp'
	)
);

$ilDB->createTable("rep_robj_xrev_quest", $fields);
$ilDB->addPrimaryKey("rep_robj_xrev_quest", array("id"));
?>
<#5>
<?php
$ilDB->dropTableColumn("rep_robj_xrev_quest", "timestamp");
$ilDB->addTableColumn("rep_robj_xrev_quest", "timestamp", array("type" => "integer", "length" => 4));
?>