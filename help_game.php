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
$cfggame = get_config('block_game');

require_login($course);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/help_game.php', array('id' => $courseid));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('help_game_title', 'block_game'));
$PAGE->set_heading(get_string('help_game_title', 'block_game'));


echo $OUTPUT->header();

$outputhtml = '<div class="rank">';
if ($courseid != 1) {
    $outputhtml .= '<h2>( '.$course->fullname.' )</h2><br/>';
} else {
    $outputhtml .= '<h2>( '.get_string('general', 'block_game').' )</h2><hr/><br/>';
}
$outputhtml .= '<table border="0">';
if ($cfggame->use_avatar == 1) {
    $outputhtml .= '<tr>';
    $outputhtml .= '<td colspan="2" align="center"><h3>'.get_string('help_avatar_titulo', 'block_game').'</h3></td>';
    $outputhtml .= '</tr><tr>';
    $outputhtml .= '<td valign="bottom"><img src="'.$CFG->wwwroot.'/blocks/game/pix/a0.png" align="center" hspace="12"/><hr/></td>';
    $outputhtml .= '<td valign="bottom"><p align="justify">'.get_string('help_avatar_text', 'block_game').'</p><hr/></td>';
    $outputhtml .= '</tr>';
}

if ($game->config->show_info == 1) {
    $outputhtml .= '<tr>';
    $outputhtml .= '<td colspan="2" align="center"><h3>'.get_string('help_info_user_titulo', 'block_game').'</h3></td>';
    $outputhtml .= '</tr><tr>';
    $outputhtml .= '<td valign="bottom">';
    $outputhtml .= '<img src="'.$CFG->wwwroot.'/blocks/game/pix/big_info.png" align="center" hspace="12"/><hr/></td>';
    $outputhtml .= '<td valign="bottom"><p align="justify">'.get_string('help_info_user_text', 'block_game').'</p><hr/></td>';
    $outputhtml .= '</tr>';
}
if ($game->config->show_score == 1) {
    $outputhtml .= '<tr>';
    $outputhtml .= '<td colspan="2" align="center"><h3>'.get_string('help_score_titulo', 'block_game').'</h3></td>';
    $outputhtml .= '</tr><tr>';
    $outputhtml .= '<td valign="bottom">';
    $outputhtml .= '<img src="'.$CFG->wwwroot.'/blocks/game/pix/big_score.png" align="center" hspace="12"/><hr/></td>';
    $outputhtml .= '<td valign="bottom"><p align="justify">'.get_string('help_score_text', 'block_game').'</p>';

    if ($game->config->score_activities == 1) {
        $outputhtml .= '<p align="justify">'.get_string('help_score_activities_text', 'block_game').'</p>';
    }
    if ($game->config->bonus_day > 0) {
        $outputhtml .= '<p align="justify">'.get_string('help_bonus_day_text', 'block_game');
        $outputhtml .= ' '.get_string('help_bonus_day_text_value', 'block_game');
        $outputhtml .= '<strong>'.$game->config->bonus_day.'pts</strong><br/></p>';
    }
    if ($cfggame->bonus_badge >= 1) {
        $outputhtml .= '<p align="justify">'.get_string('help_bonus_badge_text', 'block_game');
        $outputhtml .= ' '.get_string('help_bonus_badge_text_value', 'block_game');
        $outputhtml .= '<strong>'.$cfggame->bonus_badge.'pts</strong><br/></p>';
    }
    $outputhtml .= '<hr/></td></tr>';
}

if ($game->config->show_rank == 1) {
    $outputhtml .= '<tr>';
    $outputhtml .= '<td colspan="2" align="center"><h3>'.get_string('help_rank_titulo', 'block_game').'</h3></td>';
    $outputhtml .= '</tr><tr>';
    $outputhtml .= '<td valign="bottom">';
    $outputhtml .= '<img src="'.$CFG->wwwroot.'/blocks/game/pix/big_rank.png" align="center" hspace="12"/><hr/></td>';
    $outputhtml .= '<td valign="bottom"><p align="justify">'.get_string('help_rank_text', 'block_game').'</p>';

    if ($game->config->show_identity == 0) {
        $outputhtml .= '<p align="justify">'.get_string('help_rank_list_restrict_text', 'block_game').'</p>';
    } else {
        $outputhtml .= '<p align="justify">'.get_string('help_rank_list_text', 'block_game').'</p>';
    }
    $outputhtml .= '<p align="justify">'.get_string('help_rank_criterion_text', 'block_game').'</p>';
    $outputhtml .= '<hr/></td></tr>';
}

if ($game->config->show_level == 1) {
    $outputhtml .= '<tr>';
    $outputhtml .= '<td colspan="2" align="center"><h3>'.get_string('help_level_titulo', 'block_game').'</h3></td>';
    $outputhtml .= '</tr><tr>';
    $outputhtml .= '<td valign="bottom">';
    $outputhtml .= '<img src="'.$CFG->wwwroot.'/blocks/game/pix/big_level.png" align="center" hspace="12"/><hr/></td>';
    $outputhtml .= '<td valign="bottom"><p align="justify">'.get_string('help_level_text', 'block_game').'</p>';
    $levelup[0]   = (int)$game->config->level_up1;
    $levelup[1]   = (int)$game->config->level_up2;
    $levelup[2]   = (int)$game->config->level_up3;
    $levelup[3]   = (int)$game->config->level_up4;
    $levelup[4]   = (int)$game->config->level_up5;
    $levelup[5]   = (int)$game->config->level_up6;
    $levelup[6]   = (int)$game->config->level_up7;
    $levelup[7]   = (int)$game->config->level_up8;
    $levelup[8]   = (int)$game->config->level_up9;
    $levelup[9]   = (int)$game->config->level_up10;
    $levelup[10]   = (int)$game->config->level_up11;
    $levelup[11]   = (int)$game->config->level_up12;
    $levelup[12]   = (int)$game->config->level_up13;
    $levelup[13]   = (int)$game->config->level_up14;
    $levelup[14]   = (int)$game->config->level_up15;
    $outputhtml .= '<p>';
    for ($i = 0; $i < $game->config->level_number; $i++) {
        $outputhtml .= ' - ' . get_string('label_level', 'block_game') . ' ' . ($i + 1) . ': '.$levelup[$i] .'pts <br/>';
    }
    $outputhtml .= '</p>';
    $outputhtml .= '<p>'. get_string('help_progress_level_text', 'block_game') . '<br/>';
    $outputhtml .= '<img src="'.$CFG->wwwroot.'/blocks/game/pix/help_progress_level.png" align="center" hspace="2"/>';
    $outputhtml .= '</p><hr/></td></tr>';
    
}
$outputhtml .= '</table>';
$outputhtml .= '</div>';
echo $outputhtml;
echo $OUTPUT->footer();