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

echo $OUTPUT->header();

$outputhtml = '<div class="help">';

$game = new stdClass();

$cfggame = get_config('block_game');

if ($courseid != SITEID) {
    $outputhtml .= '<h2>( ' . $course->fullname . ' )</h2><br/>';
    $game->config = block_game_get_config_block($courseid);
} else {
    $outputhtml .= '<h2>( ' . get_string('general', 'block_game') . ' )</h2><hr/><br/>';
    $game->config = $cfggame;
}

if (!isset($game->config->show_info) && $courseid != SITEID) {
    $outputhtml = "... <br/><br/>";
    $context = context_course::instance($courseid, MUST_EXIST);
    if (has_capability('moodle/course:update', $context, $USER->id)) {
        $outputhtml .= get_string('not_initial_config_game', 'block_game');
    }
} else {
    $outputhtml .= '<table border="0">';
    if ($cfggame->use_avatar == 1) {
        $outputhtml .= '<tr>';
        $outputhtml .= '<td colspan="2" align="center"><h3>' . get_string('help_avatar_titulo', 'block_game') . '</h3></td>';
        $outputhtml .= '</tr><tr>';
        $outputhtml .= '<td valign="top">';
        $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/a0.svg"';
        $outputhtml .= ' height="80" width="80" align="center" hspace="12"/></td>';
        if ($cfggame->change_avatar_course == 1 && $courseid != SITEID) {
            $outputhtml .= '<td valign="top"><p align="justify">'
                    . get_string('help_avatar_text_course', 'block_game') . '</p><hr/></td>';
        } else {
            $outputhtml .= '<td valign="top"><p align="justify">'
                    . get_string('help_avatar_text', 'block_game') . '</p><hr/></td>';
        }
        $outputhtml .= '</tr>';
    }

    if (isset($game->config->show_info) && $game->config->show_info == 1) {
        $outputhtml .= '<tr>';
        $outputhtml .= '<td colspan="2" align="center"><h3>' . get_string('help_info_user_titulo', 'block_game') . '</h3></td>';
        $outputhtml .= '</tr><tr>';
        $outputhtml .= '<td valign="top">';
        $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/info.svg"';
        $outputhtml .= ' height="60" width="60" align="center" hspace="12"/></td>';
        $outputhtml .= '<td valign="top"><p align="justify">' . get_string('help_info_user_text', 'block_game') . '</p><hr/></td>';
        $outputhtml .= '</tr>';
    }
    if (isset($game->config->show_score) && $game->config->show_score == 1) {
        $outputhtml .= '<tr>';
        $outputhtml .= '<td colspan="2" align="center"><h3>' . get_string('help_score_titulo', 'block_game') . '</h3></td>';
        $outputhtml .= '</tr><tr>';
        $outputhtml .= '<td valign="top">';
        $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/score.svg"';
        $outputhtml .= ' height="90" width="90" align="center" hspace="12"/></td>';
        $outputhtml .= '<td valign="top"><p align="justify">' . get_string('help_score_text', 'block_game') . '</p>';

        if (isset($game->config->score_activities) && $game->config->score_activities == 1) {
            $outputhtml .= '<p align="justify">' . get_string('help_score_activities_text', 'block_game') . '</p>';
        }
        $outputhtml .= '<p align="justify">' . get_string('help_score_modules_text', 'block_game') . '</p>';
        if ($COURSE->id != SITEID) {
            // Sum score sections complete.
            $sections = block_game_get_sections_course($COURSE->id);
            $scoresections = 0;
            $outputhtmlsecion = get_string('help_score_sections_text', 'block_game');
            foreach ($sections as $section) {
                $txtsection = "section_" . $section->section;
                if (isset($game->config->$txtsection) && $game->config->$txtsection > 0) {
                    $outputhtmlsecion .= ' - ' . get_string('section', 'block_game') . ' '
                            . $section->section . ': <strong>' . $game->config->$txtsection . 'pts</strong><br/>';
                    $scoresections += (int) $game->config->$txtsection;
                }
            }
            if ($scoresections > 0) {
                $outputhtml .= $outputhtmlsecion . '<br/>';
            }
        }

        if (isset($game->config->bonus_day) && $game->config->bonus_day > 0) {
            $outputhtml .= '<p align="justify">' . get_string('help_bonus_day_text', 'block_game');
            $outputhtml .= ' ' . get_string('help_bonus_day_text_value', 'block_game');
            $outputhtml .= '<strong>' . $game->config->bonus_day . 'pts</strong><br/></p>';
        }
        if (isset($cfggame->bonus_badge) && $cfggame->bonus_badge >= 1) {
            $outputhtml .= '<p align="justify">' . get_string('help_bonus_badge', 'block_game');
            $outputhtml .= ' ' . get_string('help_bonus_badge_value', 'block_game');
            $outputhtml .= '<strong>' . $cfggame->bonus_badge . 'pts</strong><br/></p>';
        }
        $outputhtml .= '<hr/></td></tr>';
    }

    if (isset($game->config->show_rank) && $game->config->show_rank == 1) {
        $outputhtml .= '<tr>';
        $outputhtml .= '<td colspan="2" align="center"><h3>' . get_string('help_rank_titulo', 'block_game') . '</h3></td>';
        $outputhtml .= '</tr><tr>';
        $outputhtml .= '<td valign="top">';
        $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/rank.svg"';
        $outputhtml .= ' height="90" width="90" align="center" hspace="12"/></td>';
        $outputhtml .= '<td valign="top"><p align="justify">' . get_string('help_rank_text', 'block_game') . '</p>';

        if (isset($game->config->show_identity) && $game->config->show_identity == 0) {
            $outputhtml .= '<p align="justify">' . get_string('help_rank_list_restrict_text', 'block_game') . '</p>';
        } else {
            $outputhtml .= '<p align="justify">' . get_string('help_rank_list_text', 'block_game') . '</p>';
        }
        $outputhtml .= '<p align="justify">' . get_string('help_rank_criterion_text', 'block_game') . '</p>';
        $outputhtml .= '<hr/></td></tr>';
    }

    if (isset($game->config->show_level) && $game->config->show_level == 1) {
        $outputhtml .= '<tr>';
        $outputhtml .= '<td colspan="2" align="center"><h3>' . get_string('help_level_titulo', 'block_game') . '</h3></td>';
        $outputhtml .= '</tr><tr>';
        $outputhtml .= '<td valign="top">';
        $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/level.svg"';
        $outputhtml .= ' height="90" width="90" align="center" hspace="12"/></td>';
        $outputhtml .= '<td valign="top"><p align="justify">' . get_string('help_level_text', 'block_game') . '</p>';

        $outputhtml .= '<p>';
        if (isset($game->config->level_number) && $game->config->level_number > 0) {
            for ($i = 1; $i <= $game->config->level_number; $i++) {
                $xlevel = 'level_up' . $i;
                $outputhtml .= ' - ' . get_string('label_level', 'block_game')
                        . ' ' . $i . ': ' . $game->config->$xlevel . 'pts <br/>';
            }
        }
        $outputhtml .= '</p>';
        $outputhtml .= '<p>' . get_string('help_progress_level_text', 'block_game') . '<br/>';
        $outputhtml .= '<img src="' . $CFG->wwwroot . '/blocks/game/pix/help_progress_level.svg"';
        $outputhtml .= ' height="45" width="280" align="center" hspace="2"/>';
        $outputhtml .= '</p><hr/></td></tr>';
    }
    $outputhtml .= '</table>';
}
$outputhtml .= '</div>';
echo $outputhtml;
echo $OUTPUT->footer();
