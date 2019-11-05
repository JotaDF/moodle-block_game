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
require_once($CFG->libdir . '/completionlib.php');

require_login();

global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;


$couseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $couseid), '*', MUST_EXIST);

$game = new stdClass();
$game = $SESSION->game;

$showavatar = !isset($game->config->use_avatar) || $game->config->use_avatar == 1;
$showrank = !isset($game->config->show_rank) || $game->config->show_rank == 1;
$showscore = !isset($game->config->show_score) || $game->config->show_score == 1;
$showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;
require_login($course);

$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/perfil_gamer.php', array('id' => $couseid));
$PAGE->set_context(context_course::instance($couseid));
$PAGE->set_title(get_string('perfil_gamer_title', 'block_game'));
$PAGE->set_heading(get_string('perfil_gamer_title', 'block_game'));

echo $OUTPUT->header();
$outputhtml .= '<div class="boxs">';

if ($couseid == 1) {
    if ($showavatar == 1) {
        $outputhtml .= '<div class="boxgame">';
        $outputhtml .= '<img  align="center" hspace="12" src="';
        $outputhtml .= $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.png" title="avatar"/>';
    } else {
        $outputhtml .= '<div class="boxgame">' . $OUTPUT->user_picture($USER, array('size' => 80, 'hspace' => 12));
    }
    $outputhtml .= '  <strong>' . $USER->firstname . '</strong></div>';
    $outputhtml .= '<hr/>';
    $rs = get_games_user($USER->id);
    $fullpoints = 0;
    foreach ($rs as $gameuser) {
        $fullpoints = ($fullpoints + ($gameuser->score + $gameuser->score_activities + $gameuser->score_badges));
        $course = $DB->get_record('course', array('id' => $gameuser->courseid));
        if ($gameuser->courseid != 1) {
            $outputhtml .= '<h3>( ' . $course->fullname . ' )</h3><br/>';
        } else {
            $outputhtml .= '<h3>( ' . get_string('general', 'block_game') . ' )</h3><br/>';
        }
        $outputhtml .= '<div class="boxgame">';
        if ($showrank == 1) {
            $outputhtml .= '<div class="boxgame"><img src="';
            $outputhtml .= $CFG->wwwroot . '/blocks/game/pix/big_rank.png" align="center" hspace="12"/>';
            $outputhtml .= '<strong>' . get_string('label_rank', 'block_game');
            $outputhtml .= ': ' . $gameuser->rank . '&ordm; / ' . get_players($gameuser->courseid) . '</strong></div>';
        }
        if ($showscore == 1) {
            if ($gameuser->courseid != 1) {
                $outputhtml .= '<div class="boxgame">';
                $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/big_score.png" align="center" hspace="12"/>';
                $outputhtml .= '<strong>' . get_string('label_score', 'block_game');
                $outputhtml .= ': ' . ($gameuser->score + $gameuser->score_activities) . '</strong></div>';
            } else {
                $outputhtml .= '<div class="boxgame">';
                $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/big_score.png" align="center" hspace="12"/>';
                $outputhtml .= '<strong>' . get_string('label_score', 'block_game') . ': ' . $fullpoints . '</strong></div>';
            }
        }
        if ($showlevel == 1) {
            $outputhtml .= '<div class="boxgame">';
            $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/big_level.png" align="center" hspace="12"/>';
            $outputhtml .= '<strong>' . get_string('label_level', 'block_game') . ': ' . $gameuser->level . '</strong><div>';
        }
        $outputhtml .= '<hr/>';
    }

    $outputhtml .= '<h4>' . get_string('label_badge', 'block_game') . '</h4><br/>';
    if ($game->badges != "") {
        $badges = explode(",", $game->badges);
        foreach ($badges as $badge) {
            $coursebadge = $DB->get_record('course', array('id' => $badge));
            $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/big_badge.png" align="center" hspace="12"/>';
            $outputhtml .= '<strong>' . $coursebadge->fullname . '</strong> ';
        }
    }
    $outputhtml .= '<hr/>';
} else {
    $outputhtml .= '<table border="0">';
    $outputhtml .= '<tr>';
    $outputhtml .= '<td>';

    $outputhtml .= '<h3>( ' . $course->fullname . ' )</h3><br/>';
    if ($showavatar == 1) {
        $outputhtml .= '<img  align="center" hspace="12" ';
        $outputhtml .= 'src="' . $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.png" title="avatar"/>';
    } else {
        $outputhtml .= $OUTPUT->user_picture($USER, array('size' => 80, 'hspace' => 12));
    }
    $outputhtml .= '  <strong>' . $USER->firstname . '</strong><br/>';
    if ($showrank == 1) {
        $outputhtml .= '<br/><img src="' . $CFG->wwwroot . '/blocks/game/pix/big_rank.png" align="center" hspace="12"/><strong>';
        $outputhtml .= get_string('label_rank', 'block_game');
        $outputhtml .= ': ' . $game->rank . '&ordm; / ' . get_players($game->courseid) . '</strong><br/>';
    }
    if ($showscore == 1) {
        $outputhtml .= '<br/><img src="' . $CFG->wwwroot . '/blocks/game/pix/big_score.png" align="center" hspace="12"/><strong>';
        $outputhtml .= get_string('label_score', 'block_game') . ': ' . ($game->score + $game->score_activities) . '</strong><br/>';
    }
    if ($showlevel == 1) {
        $outputhtml .= '<br/><img src="' . $CFG->wwwroot . '/blocks/game/pix/big_level.png" align="center" hspace="12"/><strong>';
        $outputhtml .= get_string('label_level', 'block_game') . ': ' . $game->level . '</strong><br/>';
    }
    $outputhtml .= '</td>';
    $outputhtml .= '</tr>';
    $outputhtml .= '</table>';
}
$outputhtml .= '</div>';
echo $outputhtml;
echo $OUTPUT->footer();
