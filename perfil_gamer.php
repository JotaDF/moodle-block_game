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
require_once($CFG->libdir . '/filelib.php' );
require_once($CFG->libdir . '/badgeslib.php');

global $USER, $COURSE, $OUTPUT, $CFG;

$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);

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
    $outputhtml .= '<div>';
    if ($showavatar == 1) {
        $outputhtml .= '<div class="boxgame text-center">';
        $outputhtml .= '<img  align="center" hspace="12" height="140" width="140" src="';
        $fs = get_file_storage();
        if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $game->avatar . '.svg')) {
            $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $game->avatar);
        } else {
            $img = $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.svg';
        }
        $outputhtml .= $img . '" title="avatar"/>';
    } else {
        $outputhtml .= '<div class="boxgame">' . $OUTPUT->user_picture($USER, array('size' => 80, 'hspace' => 12));
    }
    $outputhtml .= '  <strong style="font-size:14px;">' . $USER->firstname . ' ' . $USER->lastname . '</strong></div>';
    $outputhtml .= '<hr/>';

    $outputhtml .= '<table class="generaltable" width="100%">';
    $outputhtml .= '<tr class="">';
    $outputhtml .= '<td width="30%" align="center" class="cell " style=""><strong>'
            . get_string('course') . '</strong></td>';
    $outputhtml .= '<td width="9%" class="cell " style=""><strong>'
            . get_string('label_rank', 'block_game') . '</strong></td>';
    $outputhtml .= '<td width="9%" align="center" class="cell " style=""><strong>'
            . get_string('label_level', 'block_game') . '</strong></td>';
    $outputhtml .= '<td width="9%" align="center" class="cell " style=""><strong>'
            . get_string('next_level', 'block_game') . '</strong></td>';
    $outputhtml .= '<td width="9%" align="center" class="cell " style=""><strong>'
            . get_string('score_atv', 'block_game') . '</strong></td>';
    $outputhtml .= '<td width="9%" align="center" class="cell" style=""><strong>'
            . get_string('score_mod', 'block_game') . '</strong></td>';
    $outputhtml .= '<td width="9%" align="center" class="cell" style=""><strong>'
            . get_string('score_section', 'block_game') . '</strong></td>';
    $outputhtml .= '<td width="15%" align="center" class="cell" style=""><strong>'
            . get_string('score_bonus_day', 'block_game') . '</strong></td>';
    $outputhtml .= '<td width="9%" align="center" class="cell" style=""><strong>'
            . get_string('label_badge', 'block_game') . '</strong></td>';
    $outputhtml .= '<td width="9%" align="center" class="cell" style=""><strong>'
            . get_string('score_total', 'block_game') . '</strong></td>';
    $outputhtml .= '</tr>';

    $rs = block_game_get_games_user($USER->id);
    $fullpoints = 0;
    $fullpointsbonus = 0;
    $fullpointsactivities = 0;
    $fullpointsmodulecompleted = 0;
    $fullpointssection = 0;
    $fullpointsbadges = 0;
    foreach ($rs as $gameuser) {
        $fullpoints = ($fullpoints + ($gameuser->score + $gameuser->score_bonus_day + $gameuser->score_activities
                + $gameuser->score_badges + $game->score_module_completed + $gameuser->score_section));
        $fullpointsbonus += $gameuser->score_bonus_day;
        $fullpointsactivities += $gameuser->score_activities;
        $fullpointsmodulecompleted += $gameuser->score_module_completed;
        $fullpointssection += $gameuser->score_section;
        $fullpointsbadges += $gameuser->score_badges;

        $outputhtml .= '<tr class="">';
        $course = $DB->get_record('course', array('id' => $gameuser->courseid));
        if ($gameuser->courseid != SITEID) {
            $outputhtml .= '<td width="30%" align="left" class="cell small" style=""><strong>'
                    . $course->fullname . '</strong></td>';
        } else {
            $outputhtml .= '<td width="30%" align="left" class="cell small" style=""><strong>'
                    . get_string('general', 'block_game') . '</strong></td>';
        }

        if ($showrank == 1) {
            $outputhtml .= '<td width="9%" align="center" class="cell small" style="">'
                    . $gameuser->ranking . '&ordm; / '
                    . block_game_get_players($gameuser->courseid) . '</td>';
        }
        if ($showlevel == 1) {
            // Progress Bar.
            $gameuser = block_game_get_percente_level($gameuser);
            if ($gameuser->courseid == SITEID) {
                $gameuser->score_activities = $fullpointsactivities;
                $gameuser->score_module_completed = $fullpointsmodulecompleted;
                $gameuser->score_section = $fullpointssection;
                $gameuser->score_bonus_day = $fullpointsbonus;
                $gameuser->score_badges = $fullpointsbadges;
                $gameuser = block_game_get_percente_level($gameuser);
            }
            $xlevel = 'level_up' . ($gameuser->level + 1);
            $maxok = false;
            if ($gameuser->level == $game->config->level_number) {
                $xlevel = 'level_up' . $gameuser->level;
                $maxok = true;
            }
            $progressalt = '';
            if ($maxok) {
                $progressalt .= get_string('level_max_ok', 'block_game');
            } else {
                $progressalt .= get_string('next_level', 'block_game') . ' =>' . $game->config->$xlevel;
                $progressalt .= get_string('abbreviate_score', 'block_game');
            }
            $progress = '<div class="progress" title="' . $progressalt . '">';
            $percent = round($gameuser->percent, 1);
            $progress .= '<div class="progress-bar" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="';
            $progress .= $percent . '" aria-valuemin="0" aria-valuemax="100">';
            $progress .= $percent . '%';
            $progress .= '</div></div>';
            $progress .= '</div></div></div></div>';
            $imglv = '<img src="';
            $fs = get_file_storage();
            if ($fs->file_exists(1, 'block_game', 'imagens_levels', 0, '/', 'lv' . $gameuser->level . '.svg')) {
                $imglv .= block_game_pix_url(1, 'imagens_levels', 'lv' . $gameuser->level);
            } else {
                $imglv .= $CFG->wwwroot . '/blocks/game/pix/lv' . $gameuser->level . '.svg';
            }
            $imglv .= '" title="' . get_string('label_level', 'block_game') . ' '
                    . $gameuser->level . '" height="40" width="40" align="center" hspace="12"/>';
            $outputhtml .= '<td width="9%" align="center" class="cell small" style="">'
                    . $imglv . '</td>';
            $outputhtml .= '<td width="9%" align="center" class="cell small" style="">'
                    . $progress . '</td>';
        }
        if ($showscore == 1) {
            if ($gameuser->courseid != SITEID) {
                $outputhtml .= '<td width="9%" align="center" class="cell small" style="">'
                        . $gameuser->score_activities . '</td>';
                $outputhtml .= '<td width="9%" align="center" class="cell small" style="">'
                        . $game->score_module_completed . '</td>';
                $outputhtml .= '<td width="9%" align="center" class="cell small" style="">'
                        . $gameuser->score_section . '</td>';
                $outputhtml .= '<td width="15%" align="center" class="cell small" style="">'
                        . $gameuser->score_bonus_day . '</td>';
                $outputhtml .= '<td width="9%" align="center" class="cell small" style="">'
                        . $gameuser->score_badges . '</td>';
                $outputhtml .= '<td width="9%" align="center" class="cell small" style=""><strong>'
                        . ($gameuser->score + $gameuser->score_bonus_day + $gameuser->score_activities
                        + $game->score_module_completed + $gameuser->score_section + $gameuser->score_badges) . '</strong></td>';
            } else {
                $outputhtml .= '<td width="9%" align="center" class="cell small" style=""> - </td>';
                $outputhtml .= '<td width="9%" align="center" class="cell small" style=""> - </td>';
                $outputhtml .= '<td width="9%" align="center" class="cell small" style=""> - </td>';
                $outputhtml .= '<td width="15%" align="center" class="cell small" style="">'
                        . $gameuser->score_bonus_day . '</td>';
                $outputhtml .= '<td width="9%" align="center" class="cell small" style=""> '
                        . $gameuser->score_badges . ' </td>';
                $outputhtml .= '<td width="9%" align="center" class="cell small" style=""><strong>'
                        . $fullpoints . '</strong></td>';
            }
        }
        $outputhtml .= '</tr>';
    }
    $outputhtml .= '</table>';

    if (!empty($CFG->enablebadges)) {
        $outputhtml .= '<br/><h4>' . get_string('label_badge', 'block_game') . '</h4><br/>';
        $badges = (array) badges_get_user_badges($game->userid);
        foreach ($badges as $badge) {
            $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badge->courseid);
            $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
            $url = new moodle_url('/badges/badge.php', array('hash' => $badge->uniquehash));
            $outputhtml .= '<img src="' . $imageurl . '"  height="35" width="35" class="badge-image">';
            $outputhtml .= '<span style="font-size:14px;"> <a href="' . $url . '">' . $badge->name . '</a> </span> ';
        }
        $outputhtml .= '<hr/>';
    } else {
        $outputhtml .= get_string('badgesdisabled', 'badges');
    }
} else {
    $outputhtml .= '<div>';
    $game->config = block_game_get_config_block($courseid);
    $outputhtml .= '<h3>( ' . $course->fullname . ' )</h3><br/>';
    if ($showavatar == 1) {
        $outputhtml .= '<div class="boxgame">';
        $outputhtml .= '<img  align="center" hspace="12" height="140" width="140" src="';
        $fs = get_file_storage();
        if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $game->avatar . '.svg')) {
            $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $game->avatar);
        } else {
            $img = $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.svg';
        }
        $outputhtml .= $img . '" title="avatar"/>';
    } else {
        $outputhtml .= '<div class="boxgame">' . $OUTPUT->user_picture($USER, array('size' => 80, 'hspace' => 12));
    }
    $outputhtml .= '  <strong style="font-size:14px;">' . $USER->firstname . ' ' . $USER->lastname . '</strong></div>';

    $outputhtml .= '<div class="boxgame"><div class="row"><div class="col- ">';
    if ($showrank == 1) {
        $outputhtml .= '<div class="container boxgame"><div class="row">';
        $outputhtml .= '<div class="col- "><img src="';
        $outputhtml .= $CFG->wwwroot . '/blocks/game/pix/rank.svg" height="65" width="65" align="center" hspace="12"/>';
        $outputhtml .= '</div><div class="col- text-center" style="width:105px;"><br/>' . get_string('label_rank', 'block_game');
        $outputhtml .= '<br/><strong style="font-size:14px;">' . $game->ranking . '&ordm; / '
                . block_game_get_players($game->courseid) . '</strong></div></div></div>';
    }
    if ($showscore == 1) {
        if ($game->courseid != SITEID) {
            $outputhtml .= '<div class="container boxgame"><div class="row">';
            $outputhtml .= '<div class="col- "><img src="' . $CFG->wwwroot;
            $outputhtml .= '/blocks/game/pix/score.svg" height="65" width="65" align="center" hspace="12"/>';
            $outputhtml .= '</div><div class="col- text-center" style="width:105px;"><br/>'
                    . get_string('label_score', 'block_game');
            $outputhtml .= '<br/><strong style="font-size:14px;">';
            $outputhtml .= ($game->score + $game->score_bonus_day + $game->score_activities
                    + $game->score_module_completed + $game->score_section) . '</strong></div></div></div>';
        } else {
            $outputhtml .= '<div class="container boxgame"><div class="row">';
            $outputhtml .= '<div class="col- "><img src="' . $CFG->wwwroot;
            $outputhtml .= '/blocks/game/pix/score.svg" height="65" width="65" align="center" hspace="12"/>';
            $outputhtml .= '</div><div class="col- text-center" style="width:105px;"><br/>'
                    . get_string('label_score', 'block_game');
            $outputhtml .= '<br/><strong style="font-size:14px;">' . $fullpoints . '</strong></div></div></div>';
        }
    }
    if ($showlevel == 1) {
        $outputhtml .= '<div class="container boxgame"><div class="row">';
        $outputhtml .= '<div class="col- "><img src="';
        $fs = get_file_storage();
        if ($fs->file_exists(1, 'block_game', 'imagens_levels', 0, '/', 'lv' . $game->level . '.svg')) {
            $imglv = block_game_pix_url(1, 'imagens_levels', 'lv' . $game->level);
        } else {
            $imglv = $CFG->wwwroot . '/blocks/game/pix/lv' . $game->level . '.svg';
        }
        $outputhtml .= $imglv . '" height="65" width="65" align="center" hspace="12"/>';
        $outputhtml .= '</div><div class="col- text-center" style="width:105px;"><br/>' . get_string('label_level', 'block_game');
        $outputhtml .= '<br/><strong style="font-size:14px;">';
        $outputhtml .= $game->level . '</strong></div></div></div>';
    }
    $outputhtml .= '</div><div class="col- ml-4" style="min-width: 165px; font-size:12px;">';
    $outputhtml .= '<strong>' . get_string('score_detail', 'block_game') . '</strong><br/>';
    $outputhtml .= '<table width="100%"class="generaltable">';
    $outputhtml .= '<tr><td style="font-size:12px;">' . get_string('score_atv', 'block_game') . ':</td>';
    $outputhtml .= '<td class="text-right"><strong>' . $game->score_activities;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
    $outputhtml .= '<tr><td style="font-size:12px;">' . get_string('score_mod', 'block_game') . ':</td>';
    $outputhtml .= '<td class="text-right"><strong>' . $game->score_module_completed;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
    $outputhtml .= '<tr><td style="font-size:12px;">' . get_string('score_section', 'block_game') . ':</td>';
    $outputhtml .= '<td class="text-right"><strong>' . $game->score_section;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';
    $outputhtml .= '<tr><td style="font-size:12px;">' . get_string('score_bonus_day', 'block_game') . ':</td>';
    $outputhtml .= '<td class="text-right"><strong>' . $game->score_bonus_day;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';

    $outputhtml .= '<tr><td style="font-size:12px;">' . get_string('label_badge', 'block_game') . ':</td>';
    $outputhtml .= '<td class="text-right"><strong>' . $game->score_badges;
    $outputhtml .= get_string('abbreviate_score', 'block_game') . '</strong></td></tr>';

    $outputhtml .= '</table>';
    // Progress Bar.
    $game = block_game_get_percente_level($game);
    $xlevel = 'level_up' . ($game->level + 1);
    $maxok = false;
    if ($game->level == $game->config->level_number) {
        $xlevel = 'level_up' . $game->level;
        $maxok = true;
    }
    $outputhtml .= '<div class="progress" title="' . get_string('help_progress_level_text', 'block_game') . '">';
    $percent = round($game->percent, 1);
    $outputhtml .= '<div class="progress-bar" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="';
    $outputhtml .= $percent . '" aria-valuemin="0" aria-valuemax="100">';
    $outputhtml .= $percent . '%';
    $outputhtml .= '</div></div>';

    $outputhtml .= '<div class="w-100 text-right" title="' . get_string('help_progress_level_text', 'block_game') . '">';
    if ($maxok) {
        $outputhtml .= get_string('level_max_ok', 'block_game') . '</div>';
    } else {
        $outputhtml .= get_string('next_level', 'block_game') . ' =>' . $game->config->$xlevel;
        $outputhtml .= get_string('abbreviate_score', 'block_game') . '</div>';
    }
    $outputhtml .= '</div></div>';
    $outputhtml .= '<hr/>';
    if (!empty($CFG->enablebadges)) {
        $outputhtml .= '<h4>' . get_string('label_badge', 'block_game') . '</h4><br/>';
        $badges = (array) badges_get_user_badges($game->userid, $game->courseid, null, null, null, true);
        foreach ($badges as $badge) {
            $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badge->courseid);
            $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
            $url = new moodle_url('/badges/badge.php', array('hash' => $badge->uniquehash));
            $outputhtml .= '<img src="' . $imageurl . '"  height="35" width="35" class="badge-image">';
            $outputhtml .= '<span style="font-size:14px;"> <a href="' . $url . '">' . $badge->name . '</a> </span> ';
        }
        $outputhtml .= '<hr/>';
    } else {
        $outputhtml .= get_string('badgesdisabled', 'badges');
    }
}
$outputhtml .= '</div></div>';
echo $outputhtml;
echo $OUTPUT->footer();
