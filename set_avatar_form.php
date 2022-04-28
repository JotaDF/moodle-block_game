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
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php' );

global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;

$courseid = required_param('id', PARAM_INT);

$avatar = optional_param('avatar', 0, PARAM_INT);
$back = optional_param('back', 0, PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$game = $DB->get_record('block_game', array('courseid' => $courseid, 'userid' => $USER->id));

require_login($course);

$changeavatar = !isset($cfggame->change_avatar_course) || $cfggame->change_avatar_course == 1;
if ($courseid == SITEID) {
    $config = get_config('block_game');
} else {
    $config = block_game_get_config_block($courseid);
}

if ($avatar > 0) {
    $gamenew = new stdClass();
    $gamenew->id = $game->id;
    $gamenew->userid = $USER->id;
    $gamenew->avatar = $avatar;
    block_game_update_avatar_game($gamenew);
    if ($back > 0) {
        redirect($CFG->wwwroot . "/course/view.php?id=" . $courseid);
    }
}

$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/set_avatar_form.php', array('id' => $courseid, 'back' => $back, 'avatar' => $avatar));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('set_avatar_title', 'block_game'));
$PAGE->set_heading(get_string('set_avatar_title', 'block_game'));
echo $OUTPUT->header();
$outputhtml = "";
if ($changeavatar || $courseid == SITEID) {
    $outputhtml .= '<table style="max-width: 750px;" border="0">';
    $outputhtml .= '<tr>';
    $contlevel = 0;
    $imgsize = ' height="100" width="100" ';
    for ($i = 1; $i < 69; $i++) {
        $outputhtml .= '<td width="25%">';
        $outputhtml .= '<form action="" method="post">';
        $outputhtml .= '<input name="id" type="hidden" value="' . $courseid . '"/>';
        $outputhtml .= '<input name="avatar" type="hidden" value="' . $i . '"/>';
        $outputhtml .= '<input name="back" type="hidden" value="1"/>';

        $fs = get_file_storage();
        if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $i . '.svg')) {
            $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $i);
        } else {
            $img = $CFG->wwwroot . "/blocks/game/pix/a" . $i . ".svg";
        }

        $border = '';
        if ($i == $avatar) {
            $border = ' border="1" ';
        }

        if ($i <= 8) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 8 && $i <= 12 && $game->level < 1) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_1_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_1_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 8 && $i <= 12 && $game->level >= 1) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 12 && $i <= 16 && $game->level < 2) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_2_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_2_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 12 && $i <= 16 && $game->level >= 2) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 16 && $i <= 20 && $game->level < 3) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_3_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_3_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 16 && $i <= 20 && $game->level >= 3) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 20 && $i <= 24 && $game->level < 4) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_4_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_4_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 20 && $i <= 24 && $game->level >= 4) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 24 && $i <= 28 && $game->level < 5) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_5_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_5_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 24 && $i <= 28 && $game->level >= 5) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 28 && $i <= 32 && $game->level < 6) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_6_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_6_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 28 && $i <= 32 && $game->level >= 6) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 32 && $i <= 36 && $game->level < 7) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_7_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_7_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 32 && $i <= 36 && $game->level >= 7) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 36 && $i <= 40 && $game->level < 8) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);"';
            $outputhtml .= ' class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_8_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_8_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 36 && $i <= 40 && $game->level >= 8) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 40 && $i <= 44 && $game->level < 9) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);"';
            $outputhtml .= ' class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_9_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_9_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 40 && $i <= 44 && $game->level >= 9) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 44 && $i <= 48 && $game->level < 10) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);"';
            $outputhtml .= ' class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_10_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_10_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 44 && $i <= 48 && $game->level >= 10) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 48 && $i <= 52 && $game->level < 11) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);"';
            $outputhtml .= ' class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_11_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_11_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 48 && $i <= 52 && $game->level >= 11) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 52 && $i <= 56 && $game->level < 12) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" ';
            $outputhtml .= 'class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_12_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_12_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 52 && $i <= 56 && $game->level >= 12) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 56 && $i <= 60 && $game->level < 13) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" ';
            $outputhtml .= 'class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_13_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_13_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 56 && $i <= 60 && $game->level >= 13) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 60 && $i <= 64 && $game->level < 14) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" ';
            $outputhtml .= 'class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_14_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_14_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 60 && $i <= 64 && $game->level >= 14) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }
        if ($i > 64 && $i <= 68 && $game->level < 15) {
            $outputhtml .= ' <img class="img-fluid" style="filter: grayscale(100%);" ';
            $outputhtml .= 'class="img-fluid" style="filter: grayscale(100%);" src="';
            $outputhtml .= $img . '" title="' . get_string('level_15_required', 'block_game');
            $outputhtml .= '" alt="' . get_string('level_15_required', 'block_game') . '" ' . $imgsize . '/> ';
        } else if ($i > 64 && $i <= 68 && $game->level >= 15) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }

        $outputhtml .= '</form>';
        $outputhtml .= '</td>';
        if ($i % 4 == 0 && $i < 68) {
            $outputhtml .= '</tr><tr>';
            $contlevel++;
        } else if ($i == 68) {
            $outputhtml .= '</tr>';
        }
        if ($contlevel == ($config->level_number + 2)) {
            break;
        }
    }
    $outputhtml .= '</table>';
}
echo $outputhtml;

echo $OUTPUT->footer();
