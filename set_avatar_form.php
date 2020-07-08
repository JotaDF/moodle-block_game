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


$couseid = required_param('id', PARAM_INT);

$avatar = optional_param('avatar', 0, PARAM_INT);
$back = optional_param('back', 0, PARAM_INT);
$course = $DB->get_record('course', array('id' => $couseid), '*', MUST_EXIST);
$game = $DB->get_record('block_game', array('courseid' => $couseid, 'userid' => $USER->id));

$cfggame = get_config('block_game');
$changeavatar = !isset($cfggame->change_avatar_course) || $cfggame->change_avatar_course == 1;
if ($avatar > 0) {
    $gamenew = new stdClass();
    $gamenew->id = $game->id;
    $gamenew->userid = $USER->id;
    $gamenew->avatar = $avatar;
    update_avatar_game($gamenew);
    if ($back > 0) {
        redirect($CFG->wwwroot . "/course/view.php?id=" . $couseid);
    }
}
require_login($course);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/set_avatar_form.php', array('id' => $couseid, 'back' => $back, 'avatar' => $avatar));
$PAGE->set_context(context_course::instance($couseid));
$PAGE->set_title(get_string('set_avatar_title', 'block_game'));
$PAGE->set_heading(get_string('set_avatar_title', 'block_game'));
echo $OUTPUT->header();
$outputhtml = '';
if ($changeavatar || $couseid == 1) {
    $outputhtml .= '<table order="0">';
    $outputhtml .= '<tr>';
    for ($i = 1; $i < 45; $i++) {
        $outputhtml .= '<td>';
        $outputhtml .= '<form action="" method="post">';
        $outputhtml .= '<input name="id" type="hidden" value="' . $couseid . '"/>';
        $outputhtml .= '<input name="avatar" type="hidden" value="' . $i . '"/>';
        $outputhtml .= '<input name="back" type="hidden" value="1"/>';
        $img = $CFG->wwwroot . "/blocks/game/pix/a" . $i . ".png";
        $border = '';
        if ($i == $avatar) {
            $border = ' border="1" ';
        }

        if ($i <= 8) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        if ($i > 8 && $i <= 12 && $game->level < 1) {
            $outputhtml .= ' <img style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_1_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_1_required', 'block_game') . '" height="80" width="80"/> ';
        } else if ($i > 8 && $i <= 12 && $game->level >= 1) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        if ($i > 12 && $i <= 16 && $game->level < 2) {
            $outputhtml .= ' <img style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_2_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_2_required', 'block_game') . '" height="80" width="80"/> ';
        } else if ($i > 12 && $i <= 16 && $game->level >= 2) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        if ($i > 16 && $i <= 20 && $game->level < 3) {
            $outputhtml .= ' <img style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_3_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_3_required', 'block_game') . '" height="80" width="80"/> ';
        } else if ($i > 16 && $i <= 20 && $game->level >= 3) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        if ($i > 20 && $i <= 25 && $game->level < 4) {
            $outputhtml .= ' <img style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_4_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_3_required', 'block_game') . '" height="80" width="80"/> ';
        } else if ($i > 20 && $i <= 25 && $game->level >= 4) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        if ($i > 25 && $i <= 30 && $game->level < 5) {
            $outputhtml .= ' <img style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_5_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_3_required', 'block_game') . '" height="80" width="80"/> ';
        } else if ($i > 25 && $i <= 30 && $game->level >= 5) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        if ($i > 30 && $i <= 35 && $game->level < 6) {
            $outputhtml .= ' <img style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_6_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_3_required', 'block_game') . '" height="80" width="80"/> ';
        } else if ($i > 30 && $i <= 35 && $game->level >= 6) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        if ($i > 35 && $i <= 40 && $game->level < 7) {
            $outputhtml .= ' <img style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_7_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_3_required', 'block_game') . '" height="80" width="80"/> ';
        } else if ($i > 35 && $i <= 40 && $game->level >= 7) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        if ($i > 40 && $i <= 44 && $game->level < 8) {
            $outputhtml .= ' <img style="filter: grayscale(100%);" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_8_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_3_required', 'block_game') . '" height="80" width="80"/> ';
        } else if ($i > 40 && $i <= 44 && $game->level >= 8) {
            $outputhtml .= ' <input type="image" ' . $border . ' src="' . $img . '" height="80" width="80"/> ';
        }
        $outputhtml .= '</form>';
        $outputhtml .= '</td>';
        if ($i % 4 == 0 && $i < 44) {
            $outputhtml .= '</tr><tr>';
        } else if ($i == 44) {
            $outputhtml .= '</tr>';
        }
    }
    $outputhtml .= '</table>';
}
echo $outputhtml;

echo $OUTPUT->footer();
