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
<#6>
<?php
$fields = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'timestamp' => array(
                'type' => 'integer',
                'length' => 4
        ),
        'reviewer' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'question_id' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'state' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'desc_corr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'desc_relv' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'desc_expr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'quest_corr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'quest_relv' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'quest_expr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'answ_corr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'answ_relv' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'answ_expr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'taxonomy' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'knowledge_dimension' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'rating' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'eval_comment' => array(
                'type' => 'clob',
        ),
        'expertise' => array(
                'type' => 'integer',
                'length' => 4,
        ));

$ilDB->createTable("rep_robj_xrev_revi", $fields);
$ilDB->addPrimaryKey("rep_robj_xrev_revi", array("id"));
$ilDB->createSequence("rep_robj_xrev_revi");

?>
<#7>
<?php

$fields = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'term' => array(
                'type' => 'text',
                'length' => 64
        )
);

$ilDB->createTable("rep_robj_xrev_eval", $fields);
$ilDB->addPrimaryKey("rep_robj_xrev_eval", array("id"));

$values = array('', 'eval_good', 'eval_correct', 'eval_refused');
foreach ($values as $key => $value)
        $ilDB->insert("rep_robj_xrev_eval", array(
                'id' => array('integer', $key),
                'term' => array('text', $value)));

$fields = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'term' => array(
                'type' => 'text',
                'length' => 64
        )
);

$ilDB->createTable("rep_robj_xrev_rate", $fields);
$ilDB->addPrimaryKey("rep_robj_xrev_rate", array("id"));

$values = array('', 'quest_accept', 'quest_edit', 'quest_refuse');
foreach ($values as $key => $value)
        $ilDB->insert("rep_robj_xrev_rate", array(
                'id' => array('integer', $key),
                'term' => array('text', $value)));
                
$fields = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'term' => array(
                'type' => 'text',
                'length' => 64
        )
);

$ilDB->createTable("rep_robj_xrev_taxon", $fields);
$ilDB->addPrimaryKey("rep_robj_xrev_taxon", array("id"));

$values = array('', 'taxon_remem', 'taxon_underst', 'taxon_apply', 'taxon_anal', 'taxon_eval', 'taxon_create');
foreach ($values as $key => $value)
        $ilDB->insert("rep_robj_xrev_taxon", array(
                'id' => array('integer', $key),
                'term' => array('text', $value)));

$fields = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'term' => array(
                'type' => 'text',
                'length' => 64
        )
);

$ilDB->createTable("rep_robj_xrev_knowd", $fields);
$ilDB->addPrimaryKey("rep_robj_xrev_knowd", array("id"));

$values = array('', 'knowd_concp', 'knowd_fact', 'knowd_proc', 'knowd_meta');
foreach ($values as $key => $value)
        $ilDB->insert("rep_robj_xrev_knowd", array(
                'id' => array('integer', $key),
                'term' => array('text', $value)));

$fields = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'term' => array(
                'type' => 'text',
                'length' => 64
        )
);

$ilDB->createTable("rep_robj_xrev_expert", $fields);
$ilDB->addPrimaryKey("rep_robj_xrev_expert", array("id"));

$values = array('', 'expert_no', 'expert_some', 'expert_know', 'expert_expert');
foreach ($values as $key => $value)
        $ilDB->insert("rep_robj_xrev_expert", array(
                'id' => array('integer', $key),
                'term' => array('text', $value)));
?>
<#8>
<?php
$ilDB->addTableColumn("rep_robj_xrev_revi", "review_obj", array("type" => "integer", "length" => 4));
?>
<#9>
<?php
$ilDB->addTableColumn("rep_robj_xrev_quest", "state", array("type" => "integer", "length" => 4));
?>
<#10>
<?php
$ilDB->addTableColumn("rep_robj_xrev_revobj", "group_id", array("type" => "integer", "length" => 4));
?>
<#11>
<?php
$ilDB->addTableColumn("rep_robj_xrev_quest", "review_obj", array("type" => "integer", "length" => 4));
?>
<#12>
<?php
$ilDB->dropPrimaryKey("rep_robj_xrev_quest");
?>
<#13>
<?php
$ilDB->addTableColumn("rep_robj_xrev_quest", "question_id", array("type" => "integer", "length" => 4));
$ilDB->addPrimaryKey("rep_robj_xrev_quest", array("id"));
?>
<#14>
<?php
$ilDB->createSequence("rep_robj_xrev_quest");
?>
<#15>
<?php
$ilDB->update("rep_robj_xrev_eval", array("term" => array("text", "select")), array("id" => array("integer", 0)));
$ilDB->update("rep_robj_xrev_expert", array("term" => array("text", "select")), array("id" => array("integer", 0)));
$ilDB->update("rep_robj_xrev_knowd", array("term" => array("text", "select")), array("id" => array("integer", 0)));
$ilDB->update("rep_robj_xrev_rate", array("term" => array("text", "select")), array("id" => array("integer", 0)));
$ilDB->update("rep_robj_xrev_taxon", array("term" => array("text", "select")), array("id" => array("integer", 0)));
?>
<#16>
<?php
$fields = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'timestamp' => array(
                'type' => 'integer',
                'length' => 4
        ),
        'reviewer' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'question_id' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'desc_corr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'desc_relv' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'desc_expr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'quest_corr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'quest_relv' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'quest_expr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'answ_corr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'answ_relv' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'answ_expr' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'taxonomy' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'knowledge_dimension' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'rating' => array(
                'type' => 'integer',
                'length' => 4,
        ),
        'eval_comment' => array(
                'type' => 'clob',
        ),
        'expertise' => array(
                'type' => 'integer',
                'length' => 4,
        ));
$ilDB->createTable("rep_robj_xrev_hist", $fields);
?>
<#17>
<?php

if (!$ilDB->tableExists("qpl_rev_qst")) {
    $fields = array(
        'question_id'         => array(
                    'type'  => 'integer' ,
                    'length' => 8
        ),
        'taxonomy'            => array(
                    'type' => 'text' ,
                    'length' => 20
            ),
        'knowledge_dimension' => array(
                    'type' => 'text',
                    'length' => 20
        )
    );

    $ilDB->createTable("qpl_rev_qst", $fields);
}

?>

<#18>
<?php
if (!$ilDB->tableColumnExists("rep_robj_xrev_quest", "phase")) {
    $ilDB->addTableColumn("rep_robj_xrev_quest", "phase",
            array("type" => "integer", "length" => 4));
}
?>

<#19>
<?php
if (!$ilDB->tableExists("rep_robj_xrev_alloc")) {
    $fields = array(
        'review_obj'         => array(
                    'type'  => 'integer',
                    'length' => 4
        ),
        'phase'         => array(
                    'type'  => 'integer',
                    'length' => 4
        ),
        'author'            => array(
                    'type' => 'integer',
                    'length' => 4
        ),
        'reviewer' => array(
                    'type' => 'integer',
                    'length' => 4
        )
    );

    $ilDB->createTable("rep_robj_xrev_alloc", $fields);
}
?>

<#20>
<?php
if (!$ilDB->tableExists("rep_robj_xrev_phases")) {
    $fields = array(
        'review_obj'         => array(
                    'type'  => 'integer',
                    'length' => 4
        ),
        'phase'         => array(
                    'type'  => 'integer',
                    'length' => 4
        ),
        'nr_reviewers'            => array(
                    'type' => 'integer',
                    'length' => 4
        )
    );

    $ilDB->createTable("rep_robj_xrev_alloc", $fields);
}
?
/*
$tables = array("rep_robj_xrev_loutc", "rep_robj_xrev_cont",
        "rep_robj_xrev_topic", "rep_robj_xrev_subar");
$fields = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'term' => array(
                'type' => 'text',
                'length' => 64
        )
);
foreach ($tables as $table) {
    $ilDB->createTable($table, $fields);
    $ilDB->addPrimaryKey($table, array("id"));
    $ilDB->addSequence($table);
    $ilDB->insert($table, array(
            'id' => array('integer', $ilDB->nextID($table)),
            'term' => array('text', "select")));
}

if ($ilDB->tableExists("qpl_rev_qst")) {
    $ilDB->addTableColumn("qpl_rev_qst", "learning_outcome", array("type" => "integer", "length" => 8));
    $ilDB->addTableColumn("qpl_rev_qst", "content", array("type" => "integer", "length" => 8));
    $ilDB->addTableColumn("qpl_rev_qst", "topic", array("type" => "integer", "length" => 8));
    $ilDB->addTableColumn("qpl_rev_qst", "subject_area", array("type" => "integer", "length" => 8));
}
*/
?>
