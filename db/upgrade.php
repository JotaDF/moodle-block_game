<?php

// This file keeps track of upgrades to
// the certificate module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_game_upgrade($oldversion=0) {
    global $DB;
    
    $dbman = $DB->get_manager();
    if ($oldversion < 2020012905) {

        // Add field 'score_bonus_day' to 'block_game'.
        $table = new xmldb_table('block_game');
        $field = new xmldb_field('score_bonus_day', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'score_activities');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define table block_xp_filters to be created.
        $table = new xmldb_table('block_game_completed_atv');

        // Adding fields to table block_game_completed_atv.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('moduleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('score', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table block_game_completed_atv.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Conditionally launch create table for block_game_completed_atv.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // block_game savepoint reached
        upgrade_block_savepoint(true, 2020012905, 'game');

    }
    
    return true;
}
