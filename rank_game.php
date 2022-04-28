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
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->libdir . '/filelib.php' );

require_login();

global $USER, $COURSE, $OUTPUT, $CFG;

$courseid = required_param('id', PARAM_INT);
$groupid = optional_param('group', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($courseid);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/rank_game.php', array('id' => $courseid));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('rank_game_title', 'block_game'));
$PAGE->set_heading(get_string('rank_game_title', 'block_game'));

echo $OUTPUT->header();
$cfggame = get_config('block_game');

/* Now verify grading user has access to all groups or is member of the same group when separate groups used in course */
$ok = false;
if ($course->groupmode == 1 and ! has_capability('moodle/course:viewhiddenactivities', $context)) {
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
$game = new stdClass();
if ($ok) {
    if ($courseid == SITEID) {
        $game->config = $cfggame;
    } else {
        $game->config = block_game_get_config_block($courseid);
    }
    $limit = 0;
    if (isset($game->config->show_rank) && $game->config->show_rank == 1) {
        $outputhtml = '<div class="rank">';
        if ($courseid != SITEID) {
            $limit = $game->config->limit_rank;
            $txtlimit = "";
            if ($limit > 0) {
                $txtlimit = "<strong>Top " . $limit . "</strong>";
            }
            $txtlimit .= groups_print_course_menu($course, '/blocks/game/rank_game.php?id=' . $courseid);
            $outputhtml .= '<h3>( ' . $course->fullname . ' ) ' . $txtlimit . '</h3><br/>';
        } else {
            $outputhtml .= '<h3>( ' . get_string('general', 'block_game') . ' )</h3><br/>';
        }
        $outputhtml .= '<table class="generaltable" width="100%">';
        // View details.
        $context = context_course::instance($COURSE->id, MUST_EXIST);
        $header = '';
        $showreader = false;
        if (has_capability('moodle/course:update', $context, $USER->id)) {
            $header .= '<tr class="">';
            $header .= '<td width="9%" align="center" class="cell c0 " style=""><strong>'
                    . get_string('order', 'block_game') . '</strong></td>';
            $header .= '<td width="42%" class="cell c1 " style=""><strong>'
                    . get_string('name', 'block_game') . '</strong></td>';
            $header .= '<td width="15%" align="center" class="cell c2 " style=""><strong>'
                    . get_string('score_atv', 'block_game') . '</strong></td>';
            $header .= '<td width="15%" align="center" class="cell c3 " style=""><strong>'
                    . get_string('score_mod', 'block_game') . '</strong></td>';
            $header .= '<td width="10%" align="center" class="cell c4 " style=""><strong>'
                    . get_string('score_section', 'block_game') . '</strong></td>';
            $header .= '<td width="15%" align="center" class="cell c5 " style=""><strong>'
                    . get_string('score_bonus_day', 'block_game') . '</strong></td>';
            $header .= '<td width="9%" align="center" class="cell c6 " style=""><strong>'
                    . get_string('label_badge', 'block_game') . '</strong></td>';
            $header .= '<td width="9%" align="center" class="cell c7 lastcol" style=""><strong>'
                    . get_string('score_total', 'block_game') . '</strong></td>';
            $header .= '</tr>';
            $showreader = true;
        } else {
            $header .= '<tr class="">';
            $header .= '<td width="10%" align="center" class="cell c0 " style=""><strong>'
                    . get_string('order', 'block_game') . '</strong></td>';
            $header .= '<td width="80%" class="cell c1 " style=""><strong>'
                    . get_string('name', 'block_game') . '</strong></td>';
            $header .= '<td width="10%" align="center" class="cell c2 lastcol" style=""><strong>'
                    . get_string('score_total', 'block_game') . '</strong></td>';
            $header .= '</tr>';
        }
        $outputhtml .= $header;
        $rs = block_game_rank_list($courseid, $groupid);
        $ord = 1;
        foreach ($rs as $gamer) {
            $avatartxt = '';
            if ($cfggame->use_avatar == 1) {
                $avatartxt .= '<img  align="center" height="40" width="40" src="';
                $avatar = block_game_get_avatar_user($gamer->userid);
                $fs = get_file_storage();
                if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $avatar . '.svg')) {
                    $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $avatar);
                } else {
                    $img = $CFG->wwwroot . '/blocks/game/pix/a' . $avatar . '.svg';
                }
                $avatartxt .= $img . '" title="avatar"/>';
            }
            $ordtxt = $ord . '&ordm;';
            $usertxt = $avatartxt . ' ******** ';
            if ($game->config->show_identity == 0) {
                $usertxt = $avatartxt . ' ' . $gamer->firstname . ' ' . $gamer->lastname;
            }
            $scoretxt = $gamer->pt . get_string('abbreviate_score', 'block_game');
            if ($gamer->userid == $USER->id) {
                $usertxt = $avatartxt . ' <strong>' . $gamer->firstname . ' ' . $gamer->lastname . '</trong>';
                $scoretxt = '<strong>' . $gamer->pt . get_string('abbreviate_score', 'block_game') . '</trong>';
                $ordtxt = '<strong>' . $ord . '&ordm;</trong>';
            }
            $outputhtml .= '<tr class="">';
            $outputhtml .= '<td align="center" class="cell c0" style="">' . $ordtxt . '</td>';
            $outputhtml .= '<td class="cell c2" style=""> ' . $usertxt . ' </td>';
            $colltd = 'c3 lastcol';
            if ($showreader) {
                $outputhtml .= '<td align="center" class="cell c3 small" style="">' . $gamer->sum_score_activities . '</td>';
                $outputhtml .= '<td align="center" class="cell c4 small" style="">' . $gamer->sum_score_module_completed . '</td>';
                $outputhtml .= '<td align="center" class="cell c5 small" style="">' . $gamer->sum_score_section . '</td>';
                $outputhtml .= '<td align="center" class="cell c6 small" style="">' . $gamer->sum_score_bonus_day . '</td>';
                $outputhtml .= '<td align="center" class="cell c6 small" style="">' . $gamer->sum_score_badges . '</td>';
                $colltd = 'c7 lastcol';
            }
            $outputhtml .= '<td align="center" class="cell ' . $colltd . ' small" style="">' . $scoretxt . '</td>';
            $outputhtml .= '</tr>';

            if ($limit > 0 && $limit == $ord) {
                break;
            }
            $ord++;
        }
        $outputhtml .= '</table>';

        $usernotstart = block_game_get_no_players($courseid, $groupid);
        if ($usernotstart > 0) {
            if ($usernotstart == 1) {
                $outputhtml .= '<br/>(' . $usernotstart . ' ' . get_string('not_start_game', 'block_game') . ' )';
            } else {
                $outputhtml .= '<br/>(' . $usernotstart . ' ' . get_string('not_start_game_s', 'block_game') . ' )';
            }
        }
        $outputhtml .= '</div>';
    } else {
        $outputhtml = "... <br/><br/>";
        $context = context_course::instance($courseid, MUST_EXIST);
        if (has_capability('moodle/course:update', $context, $USER->id)) {
            $outputhtml .= get_string('not_initial_config_game', 'block_game');
        }
    }
}
echo $outputhtml;

echo $OUTPUT->footer();
