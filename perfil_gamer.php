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
 * Profile view
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/game/lib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/badgeslib.php');

global $USER, $COURSE, $OUTPUT, $CFG;

$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);

$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/perfil_gamer.php', ['id' => $courseid]);
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('perfil_gamer_title', 'block_game'));
$PAGE->set_heading(get_string('perfil_gamer_title', 'block_game'));

echo $OUTPUT->header();
if ($courseid == SITEID) {
    $renderer = $PAGE->get_renderer('block_game');

    $contentrenderable = new \block_game\output\profile($USER, $courseid);

    echo $renderer->render($contentrenderable);
} else {
    $renderer = $PAGE->get_renderer('block_game');

    $contentrenderable = new \block_game\output\profilecourse($USER, $courseid);

    echo $renderer->render($contentrenderable);
}
echo $OUTPUT->footer();
