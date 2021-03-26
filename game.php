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
 * Game block language strings
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/game/libgame.php');
require_once($CFG->libdir . '/completionlib.php');

require_login();

global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;

$op = optional_param('op', '', PARAM_ALPHA);
$game = new stdClass();

switch ($op) {
    case "load":
        $game->courseid = $SESSION->game->courseid;
        $game->userid   = $USER->id;
        $game = load_game($game);
        $game->url_avatar = $CFG->wwwroot."/blocks/game/pix/a".$game->avatar.".png";
        $json = array(
                'game' => array(
                'id' => $game->id,
                'courseid' => $game->courseid,
                'userid' => $game->userid,
                'username' => $USER->firstname,
                'avatar' => $game->avatar,
                'score' => $game->score,
                'level' => $game->level,
                'ranking' => $game->ranking,
                'url_avatar' => $game->url_avatar,
                'frame' => $game->frame,
                'bonus_day' => $game->bonus_day,
                'achievements' => explode( ',', $game->achievements),
                'phases' => explode( ',', $game->phases),
                'rewards' => explode( ',', $game->rewards)
                )
                );
        echo json_encode($json);
        break;
    case "update":

        $id             = optional_param('id', '0', PARAM_INT);
        $userid         = optional_param('userid', '0', PARAM_INT);
        $courseid       = optional_param('courseid', '0', PARAM_INT);
        $score          = optional_param('score', '0', PARAM_INT);
        $achievements   = optional_param('achievements', '', PARAM_SEQUENCE);
        $rewards        = optional_param('rewards', '', PARAM_SEQUENCE);
        $phases         = optional_param('phases', '', PARAM_SEQUENCE);
        $frame          = optional_param('frame ', '', PARAM_SEQUENCE);

        $game->id           = $id;
        $game->userid       = $userid;
        $game->courseid     = $courseid;
        $game->score        = $score;
        $game->achievements = $achievements;
        $game->rewards      = $rewards;
        $game->phases       = $phases;
        $game->frame        = $frame;

        echo update_game($game);
        break;
    case "avatar":

        $id             = optional_param('id', '0', PARAM_INT);
        $userid         = optional_param('userid', '0', PARAM_INT);
        $avatar         = optional_param('avatar', '0', PARAM_INT);

        $game->id       = $id;
        $game->userid   = $userid;
        $game->avatar   = $avatar;

        echo update_avatar_game($game);
        break;
    case "score":

        $id            = optional_param('id', '0', PARAM_INT);
        $score         = optional_param('score', '0', PARAM_INT);

        $game->id      = $id;
        $game->score   = $score;

        echo update_score_game($game);
        break;
    case "level":

        $id           = optional_param('id', '0', PARAM_INT);
        $level        = optional_param('level', '0', PARAM_INT);

        $game->id     = $id;
        $game->level  = $level;

        echo update_level_game($game);
        break;

    case "achievements":

        $id              = optional_param('id', '0', PARAM_INT);
        $achievements    = optional_param('achievements', '', PARAM_SEQUENCE);

        $game->id            = $id;
        $game->achievements  = $achievements;

        echo update_achievements_game($game);
        break;
    case "rewards":

        $id         = optional_param('id', '0', PARAM_INT);
        $rewards    = optional_param('rewards', '', PARAM_SEQUENCE);

        $game->id           = $id;
        $game->rewards  = $rewards;

        echo update_rewards_game($game);
        break;

    case "phases":

        $id       = optional_param('id', '0', PARAM_INT);
        $phases   = optional_param('phases', '', PARAM_SEQUENCE);

        $game->id     = $id;
        $game->phases = $phases;

        echo update_phases_game($game);
        break;

    case "frame":

        $id      = optional_param('id', '0', PARAM_INT);
        $frame   = optional_param('frame', '', PARAM_SEQUENCE);

        $game->id     = $id;
        $game->frame  = $frame;

        echo update_frame_game($game);
        break;

    default:
        break;
}