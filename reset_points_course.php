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
require_once($CFG->libdir . '/completionlib.php');

require_login();

global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;

$confirm = optional_param('c', 0, PARAM_INT);
$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$game = new stdClass();
$game = $SESSION->game;

require_login($course);

$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/reset_points_course.php', array('id' => $courseid));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('reset_points_title', 'block_game'));
$PAGE->set_heading(get_string('reset_points_title', 'block_game'));

echo $OUTPUT->header();
$outputhtml = '<div class="boxs">';

if ($courseid > 1) {

    $context = context_course::instance($courseid, MUST_EXIST);
    if (has_capability('moodle/course:update', $context, $USER->id)) {
        $outputhtml .= '<div align="center">';

        $outputhtml .= '<h3>( ' . get_string('reset_points_title', 'block_game') . ': <strong>'
                . $course->fullname . '</strong> )</h3><br/>';

        $outputhtml .= '<br/><h5>';
        if ($confirm > 0) {
            if (reset_points_game($courseid)) {
                $outputhtml .= '<strong>' . get_string('reset_points_sucess', 'block_game')
                        . '</strong><br/><br/><a class="btn btn-success" href="' . $CFG->wwwroot . '/course/view.php?id='
                        . $courseid . '">' . get_string('ok', 'block_game') . '</a>';
            } else {
                $outputhtml .= '<strong>' . get_string('reset_points_error', 'block_game') . '</strong><br/>';
            }
        } else {
            $outputhtml .= '<strong>' . get_string('label_confirm_reset_points', 'block_game') . '</strong><br/><br/>';
            $outputhtml .= '<a class="btn btn-secondary" href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseid . '">'
                    . get_string('no', 'block_game') . '</a>' . '  <a class="btn btn-danger" href="reset_points_course.php?id='
                    . $courseid . '&c=1">' . get_string('yes', 'block_game') . '</a>' . '<br/>';
        }
        $outputhtml .= '</h5>';
        $outputhtml .= '</div>';
    } else {
        $outputhtml .= '<strong>' . get_string('reset_points_not_permission', 'block_game') . '</strong><br/>';
    }
}
$outputhtml .= '</div>';
echo $outputhtml;
echo $OUTPUT->footer();
