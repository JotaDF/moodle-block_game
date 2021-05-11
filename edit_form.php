<?php
// This file is part of Moodle - http://moodle.org/
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
 * Block Game configuration form definition
 *
 * @package   block_game
 * @copyright 2019 Jose Wilson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/game/lib.php');

/**
 *  Block Game config form definition class
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_game_edit_form extends block_edit_form {

    /**
     * Block Game form definition
     *
     * @param mixed $mform
     * @return void
     */
    protected function specific_definition($mform) {
        global $COURSE;

        // Start block specific section in config form.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        if ($COURSE->id != SITEID) {
            // Game block instance alternate title.
            $mform->addElement('text', 'config_game_title', get_string('config_title', 'block_game'));
            $mform->setDefault('config_game_title', '');
            $mform->setType('config_game_title', PARAM_MULTILANG);
            $mform->addHelpButton('config_game_title', 'config_title', 'block_game');
            // Control visibility name course.
            $mform->addElement('selectyesno', 'config_show_name_course', get_string('config_name_course', 'block_game'));
            $mform->setDefault('config_show_name_course', 0);
            $mform->addHelpButton('config_show_name_course', 'config_name_course', 'block_game');

            // Control visibility of link info user game.
            $mform->addElement('selectyesno', 'config_show_info', get_string('config_info', 'block_game'));
            $mform->setDefault('config_show_info', 1);
            $mform->addHelpButton('config_show_info', 'config_info', 'block_game');
            // Control score activities.
            $mform->addElement('selectyesno', 'config_score_activities', get_string('config_score_activities', 'block_game'));
            $mform->setDefault('config_score_activities', 1);
            $mform->addHelpButton('config_score_activities', 'config_score_activities', 'block_game');
            // Control bonus of day.
            $bonusdayoptions = array(0 => 0, 5 => 5, 10 => 10, 15 => 15, 20 => 20, 50 => 50, 100 => 100);
            $mform->addElement('select', 'config_bonus_day', get_string('config_bonus_day', 'block_game'), $bonusdayoptions);
            $mform->addHelpButton('config_bonus_day', 'config_bonus_day', 'block_game');
            // Control visibility of rank group.
            $mform->addElement('selectyesno', 'config_show_rank_group', get_string('config_rank_group', 'block_game'));
            $mform->setDefault('config_show_rank_group', 0);
            $mform->addHelpButton('config_show_rank_group', 'config_rank_group', 'block_game');
            // Control visibility of rank group calculation.
            $calcoptions = array(0 => get_string('sum', 'block_game'), 1 => get_string('med', 'block_game'));
            $mform->addElement('select', 'config_rank_group_calc',
                    get_string('config_rank_group_calc', 'block_game'), $calcoptions);
            $mform->setDefault('config_rank_group_calc', 0);
            $mform->disabledIf('config_rank_group_calc', 'config_show_rank_group', 'eq', 0);
            $mform->addHelpButton('config_rank_group_calc', 'config_rank_group_calc', 'block_game');
            // Control visibility of rank.
            $mform->addElement('selectyesno', 'config_show_rank', get_string('config_rank', 'block_game'));
            $mform->setDefault('config_show_rank', 1);
            $mform->addHelpButton('config_show_rank', 'config_rank', 'block_game');
            // Control limit rank.
            $limit = array(0 => 0, 5 => 5, 10 => 10, 20 => 20, 50 => 50, 100 => 100);
            $mform->addElement('select', 'config_limit_rank', get_string('config_limit_rank', 'block_game'), $limit);
            $mform->addHelpButton('config_limit_rank', 'config_limit_rank', 'block_game');
            // Preserve user identity.
            $mform->addElement('selectyesno', 'config_show_identity', get_string('config_identity', 'block_game'));
            $mform->setDefault('config_show_identity', 0);
            $mform->disabledIf('config_show_identity', 'config_show_rank', 'eq', 0);
            $mform->addHelpButton('config_show_identity', 'config_identity', 'block_game');
            // Control visibility of score.
            $mform->addElement('selectyesno', 'config_show_score', get_string('config_score', 'block_game'));
            $mform->setDefault('config_show_score', 1);
            $mform->addHelpButton('config_show_score', 'config_score', 'block_game');
            // Control visibility of level.
            $mform->addElement('selectyesno', 'config_show_level', get_string('config_level', 'block_game'));
            $mform->setDefault('config_show_level', 1);
            $mform->addHelpButton('config_show_level', 'config_level', 'block_game');
            // Options controlling level up.
            $levels = array(4 => 4, 6 => 6, 8 => 8, 10 => 10, 12 => 12);
            $mform->addElement('select', 'config_level_number', get_string('config_level_number', 'block_game'), $levels);
            $mform->setDefault('config_level_number', 6);
            $mform->disabledIf('config_level_number', 'config_show_level', 'eq', 0);
            $mform->addHelpButton('config_level_number', 'config_level_number', 'block_game');
            $leveluppoints = array(1 => 300, 2 => 500, 3 => 1000, 4 => 2000,
                5 => 4000, 6 => 6000, 7 => 10000, 8 => 20000,
                9 => 30000, 10 => 50000, 11 => 70000, 12 => 100000);
            for ($i = 1; $i <= 12; $i++) {
                $mform->addElement('text', 'config_level_up' . $i, get_string('config_level_up' . $i, 'block_game'));
                $mform->setDefault('config_level_up' . $i, $leveluppoints[$i]);
                $mform->disabledIf('config_level_up' . $i, 'config_show_level', 'eq', 0);
                foreach ($levels as $level) {
                    if ($level < $i) {
                        $mform->disabledIf('config_level_up' . $i, 'config_level_number', 'eq', $level);
                    }
                }
                $mform->setType('config_level_up' . $i, PARAM_INT);
                $mform->addHelpButton('config_level_up' . $i, 'config_level_up' . $i, 'block_game');
            }
            // Options controlling sections.
            $mform->addElement('html', '<hr/>');
            $mform->addElement('html', get_string('title_config_section', 'block_game'));
            $sections = block_game_get_sections_course($COURSE->id);
            foreach ($sections as $section) {
                $limit = array(0 => 0, 5 => 5, 10 => 10, 20 => 20, 30 => 30, 50 => 50, 60 => 60, 80 => 80, 100 => 100);
                $txtsection = get_string('section', 'block_game') . ' ' . $section->section;
                if (isset($section->name) && $section->name != "") {
                    $txtsection = $section->name;
                }
                $mform->addElement('select', 'config_section_' . $section->section, $txtsection, $limit);
                $mform->addHelpButton('config_section_' . $section->section, 'config_section', 'block_game');
            }
        }
    }

}
