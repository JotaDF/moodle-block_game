<?php
// This file is part of Block Game
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Game block language strings
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Update block_game
 *
 * @param int $oldversion
 * @return mixed
 */
function xmldb_block_game_upgrade($oldversion = 0) {
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

        // Block_game savepoint reached.
        upgrade_block_savepoint(true, 2020012905, 'game');
    }
    if ($oldversion < 2020042983) {
        // Add field 'score_bonus_day' to 'block_game'.
        $table = new xmldb_table('block_game');
        $field = new xmldb_field('score_section', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'score_badges');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Block_game savepoint reached.
        upgrade_block_savepoint(true, 2020042983, 'game');
    }
    if ($oldversion < 2020042996) {
        // Add field 'score_bonus_day' to 'block_game'.
        $table = new xmldb_table('block_game');
        $field = new xmldb_field('ranking', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'level');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Block_game savepoint reached.
        upgrade_block_savepoint(true, 2020042996, 'game');
    }

    return true;
}
