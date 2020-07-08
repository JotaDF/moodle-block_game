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
require_once($CFG->dirroot . '/blocks/game/libgame.php');

require_login();

global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;


$courseid = required_param('id', PARAM_INT);


$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$game = new stdClass();
$game = $SESSION->game;

require_login($course);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/rank_game.php', array('id' => $courseid));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('rank_game_title', 'block_game'));
$PAGE->set_heading(get_string('rank_game_title', 'block_game'));

echo $OUTPUT->header();
$cfggame = get_config('block_game');
if ($courseid == 1) {
    $game->config = $cfggame;
}
$limit = 0;
if ($game->config->show_rank == 1) {
    $outputhtml = '<div class="rank">';
    if ($courseid != 1) {
        $limit = $game->config->limit_rank;
        $txtlimit = "";
        if ($limit > 0) {
            $txtlimit = "<strong>Top " . $limit . "</strong>";
        }
        $outputhtml .= '<h3>( ' . $course->fullname . ' ) ' . $txtlimit . '</h3><br/>';
    } else {
        $outputhtml .= '<h3>( ' . get_string('general', 'block_game') . ' )</h3><br/>';
    }
    $outputhtml .= '<table border="0" width="100%">';
    $rs = rank_list($courseid);
    $ord = 1;
    foreach ($rs as $gamer) {
        $avatartxt = '';
        if ($cfggame->use_avatar == 1) {
            $avatartxt = $OUTPUT->pix_icon('a' . get_avatar_user($gamer->userid), 'Avatar', 'block_game');
        }
        $ordtxt = $ord . '&ordm;';
        $usertxt = $avatartxt . ' ******** ';
        if ($game->config->show_identity == 0) {
            $usertxt = $avatartxt . ' ' . $gamer->firstname . ' ' . $gamer->lastname;
        }
        $scoretxt = $gamer->pt;
        if ($gamer->userid == $USER->id) {
            $usertxt = $avatartxt . ' <strong>' . $gamer->firstname . ' ' . $gamer->lastname . '</trong>';
            $scoretxt = '<strong>' . (int) $gamer->pt . '</trong>';
            $ordtxt = '<strong>' . $ord . '&ordm;</trong>';
        }
        $outputhtml .= '<tr>';
        $outputhtml .= '<td>';
        $outputhtml .= $ordtxt . '<hr/></td><td> ' . $usertxt . ' <hr/></td><td> ' . $scoretxt . '<hr/></td>';
        $outputhtml .= '</tr>';

        if ($limit > 0 && $limit == $ord) {
            break;
        }
        $ord++;
    }
    $outputhtml .= '</table>';

    $usernotstart = get_no_players($courseid);
    if ($usernotstart > 0) {
        if ($usernotstart == 1) {
            $outputhtml .= '<br/>(' . $usernotstart . ' ' . get_string('not_start_game', 'block_game') . ' )';
        } else {
            $outputhtml .= '<br/>(' . $usernotstart . ' ' . get_string('not_start_game_s', 'block_game') . ' )';
        }
    }
    $outputhtml .= '</div>';
}
echo $outputhtml;

echo $OUTPUT->footer();
