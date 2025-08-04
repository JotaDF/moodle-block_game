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
 * Rank view
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/game/lib.php');
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->libdir . '/filelib.php');

require_login();

global $USER, $COURSE, $OUTPUT, $CFG;

$courseid = required_param('id', PARAM_INT);
$groupid = optional_param('group', 0, PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($courseid);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/rank_game.php', ['id' => $courseid]);
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('rank_game_title', 'block_game'));
$PAGE->set_heading(get_string('rank_game_title', 'block_game'));

echo $OUTPUT->header();
$game = new stdClass();
$game->config = get_config('block_game');

/* Now verify grading user has access to all groups or is member of the same group when separate groups used in course */
$ok = false;
if ($course->groupmode == 1 && !has_capability('moodle/course:viewhiddenactivities', $context)) {
    if (groups_is_member($groupid, $USER->id)) {
        $ok = true;
    }
} else {
    $ok = true;
}
if (has_capability('moodle/course:update', $context, $USER->id)) {
    echo $OUTPUT->download_dataformat_selector(get_string('downloadthis', 'block_game'), 'download.php',
            'dataformat', ['id' => $courseid, 'op' => 'ranking']);
}

if ($ok) {
    if ($courseid == SITEID) {
        $renderer = $PAGE->get_renderer('block_game');

        $rankpage = new \block_game\output\rank($course, $USER, $game->config, $groupid);
        echo $renderer->render($rankpage);
    } else {
        $game->config = block_game_get_config_block($courseid);
        $renderer = $PAGE->get_renderer('block_game');

        $rankpage = new \block_game\output\rank($course, $USER, $game->config, $groupid);
        echo $renderer->render($rankpage);
    }
}
echo $OUTPUT->footer();
