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
 * Game block config form definition
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/game/lib.php');
require_login();

global $USER, $COURSE, $OUTPUT, $CFG;


$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/help_game.php', array('id' => $courseid));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('help_game_title', 'block_game'));
$PAGE->set_heading(get_string('help_game_title', 'block_game'));

$cfggame = get_config('block_game');
$isfrontpage = $courseid == SITEID;
$gameconfig = $isfrontpage ? $cfggame : block_game_get_config_block($courseid);
$notconfigured = !$isfrontpage && empty($gameconfig->show_info);
$sections = !$isfrontpage ? block_game_get_sections_course($courseid) : [];

$help_page = new \block_game\output\help($course, $gameconfig, $cfggame, $isfrontpage, $notconfigured, $sections);

echo $OUTPUT->header();
echo $OUTPUT->render($help_page);
echo $OUTPUT->footer();
