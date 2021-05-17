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

global $USER, $COURSE, $OUTPUT, $CFG;


$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$game = new stdClass();
$game->courseid = $courseid;
$game->userid = $USER->id;

$game = block_game_load_game($game);
$cfggame = get_config('block_game');

if ($courseid != SITEID) {
    $game->config = block_game_get_config_block($courseid);
} else {
    $game->config = $cfggame;
}

$showavatar = !isset($game->config->use_avatar) || $game->config->use_avatar == 1;
$showrank = !isset($game->config->show_rank) || $game->config->show_rank == 1;
$showscore = !isset($game->config->show_score) || $game->config->show_score == 1;
$showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;

require_login($course);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/perfil_gamer.php', array('id' => $courseid));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('perfil_gamer_title', 'block_game'));
$PAGE->set_heading(get_string('perfil_gamer_title', 'block_game'));

echo $OUTPUT->header();
$outputhtml = '';
if ($courseid == SITEID) {
    if ($showavatar == 1) {
        $outputhtml .= '<div class="boxgame text-center">';
        $outputhtml .= '<img  align="center" hspace="12" height="140" width="140" src="';
        $outputhtml .= $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.svg" title="avatar"/>';
    } else {
        $outputhtml .= '<div class="boxgame">' . $OUTPUT->user_picture($USER, array('size' => 80, 'hspace' => 12));
    }
    $outputhtml .= '  <strong style="font-size:14px;">' . $USER->firstname . ' ' . $USER->lastname . '</strong></div>';
    $outputhtml .= '<hr/>';

    $outputhtml .= '<div class="boxs container" style="font-size:11px;"><div class="row">';
    $rs = block_game_get_games_user($USER->id);
    $fullpoints = 0;
    $fullpointsbonus = 0;
    $fullpointsactivities = 0;
    $fullpointssection = 0;
    $fullpointsbadges = 0;
    foreach ($rs as $gameuser) {
        $fullpoints = ($fullpoints + ($gameuser->score + $gameuser->score_bonus_day +
                $gameuser->score_activities + $gameuser->score_badges + $gameuser->score_section));
        $fullpointsbonus += $gameuser->score_bonus_day;
        $fullpointsactivities += $gameuser->score_activities;
        $fullpointssection += $gameuser->score_section;
        $fullpointsbadges += $gameuser->score_badges;

        $outputhtml .= '<div class="col- shadow border mt-3 mb-1 mr-1 p-2 rounded">';
        $outputhtml .= '<div class="container"><div class="row"><div class="col- text-center w-100">';
        $course = $DB->get_record('course', array('id' => $gameuser->courseid));
        if ($gameuser->courseid != SITEID) {
            $outputhtml .= '<h5><strong>' . $course->fullname . '</strong></h5><hr/>';
        } else {
            $outputhtml .= '<h5><strong>' . get_string('general', 'block_game') . '</strong></h5><hr/>';
        }
        $outputhtml .= '</div></div><div class="row"><div class="col-">';

        if ($showrank == 1) {
            $outputhtml .= '<div class="container boxgame"><div class="row">';
            $outputhtml .= '<div class="col- "><img src="';
            $outputhtml .= $CFG->wwwroot . '/blocks/game/pix/rank.svg" height="65" width="65" align="center" hspace="12"/>';
            $outputhtml .= '</div><div class="col- text-center"><br/>' . get_string('label_rank', 'block_game');
            $outputhtml .= '<br/><strong style="font-size:14px;">' . $gameuser->ranking . '&ordm; / '
                    . block_game_get_players($gameuser->courseid) . '</strong></div></div></div>';
        }
        if ($showscore == 1) {
            if ($gameuser->courseid != SITEID) {
                $outputhtml .= '<div class="container boxgame"><div class="row">';
                $outputhtml .= '<div class="col- "><img src="' . $CFG->wwwroot;
                $outputhtml .= '/blocks/game/pix/score.svg" height="65" width="65" align="center" hspace="12"/>';
                $outputhtml .= '</div><div class="col- text-center"><br/>' . get_string('label_score', 'block_game');
                $outputhtml .= '<br/><strong style="font-size:14px;">';
                $outputhtml .= ($gameuser->score + $gameuser->score_bonus_day +
                        $gameuser->score_activities + $gameuser->score_section) . '</strong></div></div></div>';
            } else {
                $outputhtml .= '<div class="container boxgame"><div class="row">';
                $outputhtml .= '<div class="col- "><img src="' . $CFG->wwwroot;
                $outputhtml .= '/blocks/game/pix/score.svg" height="65" width="65" align="center" hspace="12"/>';
                $outputhtml .= '</div><div class="col- text-center"><br/>' . get_string('label_score', 'block_game');
                $outputhtml .= '<br/><strong style="font-size:14px;">' . $fullpoints . '</strong></div></div></div>';
            }
        }
        if ($showlevel == 1) {
            $outputhtml .= '<div class="container boxgame"><div class="row">';
            $outputhtml .= '<div class="col- "><img src="' . $CFG->wwwroot;
            $outputhtml .= '/blocks/game/pix/level.svg" height="65" width="65" align="center" hspace="12"/>';
            $outputhtml .= '</div><div class="col- text-center"><br/>' . get_string('label_level', 'block_game');
            $outputhtml .= '<br/><strong style="font-size:14px;">';
            $outputhtml .= $gameuser->level . '</strong></div></div></div>';
        }
        $outputhtml .= '</div><div class="col- ml-3" style="min-width: 165px; font-size:12px;">';
        $outputhtml .= '<strong>' . get_string('score_detail', 'block_game') . '</strong><br/>';
        $outputhtml .= '<table width="100%" class="generaltable">';

        if ($gameuser->courseid == SITEID) {
            $outputhtml .= '<tr><td>' . get_string('score_atv', 'block_game') . ':</td>';
            $outputhtml .= '<td class="text-right"><strong>' . $fullpointsactivities;
            $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
            $outputhtml .= '<tr><td>' . get_string('score_section', 'block_game') . ':</td>';
            $outputhtml .= '<td class="text-right"><strong>' . $fullpointssection;
            $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
            $outputhtml .= '<tr><td>' . get_string('score_bonus_day', 'block_game') . ':</td>';
            $outputhtml .= '<td class="text-right"><strong>' . $fullpointsbonus;
            $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
            $outputhtml .= '<tr><td>' . get_string('label_badge', 'block_game') . ':</td>';
            $outputhtml .= '<td class="text-right"><strong>' . $fullpointsbadges;
            $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
        } else {
            $outputhtml .= '<tr><td>' . get_string('score_atv', 'block_game') . ':</td>';
            $outputhtml .= '<td class="text-right"><strong>' . $gameuser->score_activities;
            $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
            $outputhtml .= '<tr><td>' . get_string('score_section', 'block_game') . ':</td>';
            $outputhtml .= '<td class="text-right"><strong>' . $gameuser->score_section;
            $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
            $outputhtml .= '<tr><td>' . get_string('score_bonus_day', 'block_game') . ':</td>';
            $outputhtml .= '<td class="text-right"><strong>' . $gameuser->score_bonus_day;
            $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
        }
        $outputhtml .= '</table>';
        // Progress Bar.
        $gameuser = block_game_get_percente_level($gameuser);
        if ($gameuser->courseid == SITEID) {
            $gameuser->score_activities = $fullpointsactivities;
            $gameuser->score_section = $fullpointssection;
            $gameuser->score_bonus_day = $fullpointsbonus;
            $gameuser->score_badges = $fullpointsbadges;
            $gameuser = block_game_get_percente_level($gameuser);
        }
        $xlevel = 'level_up' . ($gameuser->level + 1);

        $outputhtml .= '<div class="progress" title="' . get_string('help_progress_level_text', 'block_game') . '">';
        $percent = round($gameuser->percent, 1);
        $outputhtml .= '<div class="progress-bar" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="';
        $outputhtml .= $percent . '" aria-valuemin="0" aria-valuemax="100">';
        $outputhtml .= $percent . '%';
        $outputhtml .= '</div></div>';

        $outputhtml .= '<div class="w-100 text-right" title="' . get_string('help_progress_level_text', 'block_game') . '">';
        $outputhtml .= get_string('next_level', 'block_game') . ' =>' . $gameuser->config->$xlevel;
        $outputhtml .= get_string('abbreviate_score', 'block_game') . '</div>';
        $outputhtml .= '</div></div></div></div>';
        $outputhtml .= '<hr/>';
    }
    $outputhtml .= '</div>';
    $outputhtml .= '<br/><h4>' . get_string('label_badge', 'block_game') . '</h4><br/>';
    if ($game->badges != "") {
        $badges = explode(",", $game->badges);
        foreach ($badges as $badge) {
            $coursebadge = $DB->get_record('course', array('id' => $badge));
            $outputhtml .= '<img src="' . $CFG->wwwroot;
            $outputhtml .= '/blocks/game/pix/badge.svg" height="80" width="80" align="center" hspace="12"/>';
            $outputhtml .= '<strong style="font-size:14px;">' . $coursebadge->fullname . '</strong> ';
        }
    }
    $outputhtml .= '<hr/>';
} else {
    $outputhtml .= '<div>';
    $game->config = block_game_get_config_block($courseid);
    $outputhtml .= '<h3>( ' . $course->fullname . ' )</h3><br/>';
    if ($showavatar == 1) {
        $outputhtml .= '<div class="boxgame">';
        $outputhtml .= '<img  align="center" hspace="12" height="140" width="140" ';
        $outputhtml .= 'src="' . $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.svg" title="avatar"/>';
    } else {
        $outputhtml .= '<div class="boxgame">' . $OUTPUT->user_picture($USER, array('size' => 80, 'hspace' => 12));
    }
    $outputhtml .= '  <strong style="font-size:14px;">' . $USER->firstname . ' ' . $USER->lastname . '</strong></div>';

    $outputhtml .= '<div class="boxgame"><div class="row"><div class="col- ">';
    if ($showrank == 1) {
        $outputhtml .= '<div class="container boxgame"><div class="row">';
        $outputhtml .= '<div class="col- "><img src="';
        $outputhtml .= $CFG->wwwroot . '/blocks/game/pix/rank.svg" height="65" width="65" align="center" hspace="12"/>';
        $outputhtml .= '</div><div class="col- text-center"><br/>' . get_string('label_rank', 'block_game');
        $outputhtml .= '<br/><strong style="font-size:14px;">' . $game->ranking . '&ordm; / '
                . block_game_get_players($game->courseid) . '</strong></div></div></div>';
    }
    if ($showscore == 1) {
        if ($game->courseid != SITEID) {
            $outputhtml .= '<div class="container boxgame"><div class="row">';
            $outputhtml .= '<div class="col- "><img src="' . $CFG->wwwroot;
            $outputhtml .= '/blocks/game/pix/score.svg" height="65" width="65" align="center" hspace="12"/>';
            $outputhtml .= '</div><div class="col- text-center"><br/>' . get_string('label_score', 'block_game');
            $outputhtml .= '<br/><strong style="font-size:14px;">';
            $outputhtml .= ($game->score + $game->score_bonus_day +
                    $game->score_activities + $game->score_section) . '</strong></div></div></div>';
        } else {
            $outputhtml .= '<div class="container boxgame"><div class="row">';
            $outputhtml .= '<div class="col- "><img src="' . $CFG->wwwroot;
            $outputhtml .= '/blocks/game/pix/score.svg" height="65" width="65" align="center" hspace="12"/>';
            $outputhtml .= '</div><div class="col- text-center"><br/>' . get_string('label_score', 'block_game');
            $outputhtml .= '<br/><strong style="font-size:14px;">' . $fullpoints . '</strong></div></div></div>';
        }
    }
    if ($showlevel == 1) {
        $outputhtml .= '<div class="container boxgame"><div class="row">';
        $outputhtml .= '<div class="col- "><img src="' . $CFG->wwwroot;
        $outputhtml .= '/blocks/game/pix/level.svg" height="65" width="65" align="center" hspace="12"/>';
        $outputhtml .= '</div><div class="col- text-center"><br/>' . get_string('label_level', 'block_game');
        $outputhtml .= '<br/><strong style="font-size:14px;">';
        $outputhtml .= $game->level . '</strong></div></div></div>';
    }
    $outputhtml .= '</div><div class="col- ml-5" style="min-width: 165px; font-size:12px;">';
    $outputhtml .= '<strong>' . get_string('score_detail', 'block_game') . '</strong><br/>';
    $outputhtml .= '<table width="100%"class="generaltable">';
    $outputhtml .= '<tr><td>' . get_string('score_atv', 'block_game') . ':</td>';
    $outputhtml .= '<td class="text-right"><strong>' . $game->score_activities;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
    $outputhtml .= '<tr><td>' . get_string('score_section', 'block_game') . ':</td>';
    $outputhtml .= '<td class="text-right"><strong>' . $game->score_section;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
    $outputhtml .= '<tr><td>' . get_string('score_bonus_day', 'block_game') . ':</td>';
    $outputhtml .= '<td class="text-right"><strong>' . $game->score_bonus_day;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
    if ($game->courseid == SITEID) {
        $outputhtml .= '<tr><td>' . get_string('label_badge', 'block_game') . ':</td>';
        $outputhtml .= '<td class="text-right"><strong>' . $game->score_badges;
        $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
    }
    $outputhtml .= '</table>';
    // Progress Bar.
    $game = block_game_get_percente_level($game);
    $xlevel = 'level_up' . ($game->level + 1);

    $outputhtml .= '<div class="progress" title="' . get_string('help_progress_level_text', 'block_game') . '">';
    $percent = round($game->percent, 1);
    $outputhtml .= '<div class="progress-bar" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="';
    $outputhtml .= $percent . '" aria-valuemin="0" aria-valuemax="100">';
    $outputhtml .= $percent . '%';
    $outputhtml .= '</div></div>';

    $outputhtml .= '<div class="w-100 text-right" title="' . get_string('help_progress_level_text', 'block_game') . '">';
    $outputhtml .= get_string('next_level', 'block_game') . ' =>' . $game->config->$xlevel;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</div>';
    $outputhtml .= '</div></div>';
    $outputhtml .= '<hr/>';
    $outputhtml .= '<h4>' . get_string('label_badge', 'block_game') . '</h4><br/>';
    if ($game->badges != "") {
        $badges = explode(",", $game->badges);
        foreach ($badges as $badge) {
            $coursebadge = $DB->get_record('course', array('id' => $badge));
            $outputhtml .= '<img src="' . $CFG->wwwroot;
            $outputhtml .= '/blocks/game/pix/badge.svg" height="80" width="80" align="center" hspace="12"/>';
            $outputhtml .= '<strong style="font-size:14px;">' . $coursebadge->fullname . '</strong> ';
        }
    }
    $outputhtml .= '<hr/>';
}
$outputhtml .= '</div></div>';
echo $outputhtml;
echo $OUTPUT->footer();
