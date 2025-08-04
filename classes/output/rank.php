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
 * Rank definition
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_game\output;

use renderable;
use templatable;
use renderer_base;
use moodle_url;

/**
 * Renderable class for the ranking page.
 *
 * @package    block_game
 * @copyright  2025 José Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rank implements renderable, templatable {
    /** @var course */
    protected $course;
    /** @var user */
    protected $user;
    /** @var config */
    protected $config;
    /** @var cfggame */
    protected $cfggame;
    /** @var context */
    protected $context;
    /** @var groupid */
    protected $groupid;
    /**
     * Rank constructor.
     *
     * @param stdClass $course
     * @param stdClass $user
     * @param stdClass $config
     * @param int $groupid
     */
    public function __construct($course, $user, $config, $groupid = 0) {
        $this->course = $course;
        $this->user = $user;
        $this->config = $config;
        $this->cfggame = get_config('block_game');
        $this->groupid = $groupid;
        $this->context = \context_course::instance($course->id);
    }
    /**
     * Export the data.
     * @param renderer_base $output
     * @return array|\stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $CFG;

        $templatecontext = [
            'coursefullname' => $this->course->fullname,
            'showgroupmenu' => $this->course->id != SITEID,
            'groupmenu' => groups_print_course_menu($this->course, '/blocks/game/rank_game.php?id=' . $this->course->id, true),
            'headers' => [],
            'gamers' => [],
        ];

        $limit = 0;
        if (!empty($this->config->show_rank) && $this->config->show_rank == 1) {
            $limit = isset($this->config->limit_rank) ? (int) $this->config->limit_rank : 0;
        }

        $showdetails = has_capability('moodle/course:update', $this->context, $this->user->id);
        $templatecontext['showdetails'] = $showdetails;

        // Cabeçalhos.
        $templatecontext['headers'] = $showdetails ? [
            ['label' => get_string('order', 'block_game'), 'class' => 'c0'],
            ['label' => get_string('name', 'block_game'), 'class' => 'c1'],
            ['label' => get_string('score_atv', 'block_game'), 'class' => 'c2'],
            ['label' => get_string('score_mod', 'block_game'), 'class' => 'c3'],
            ['label' => get_string('score_section', 'block_game'), 'class' => 'c4'],
            ['label' => get_string('score_bonus_day', 'block_game'), 'class' => 'c5'],
            ['label' => get_string('label_badge', 'block_game'), 'class' => 'c6'],
            ['label' => get_string('score_total', 'block_game'), 'class' => 'c7'],
        ] : [
            ['label' => get_string('order', 'block_game'), 'class' => 'c0'],
            ['label' => get_string('name', 'block_game'), 'class' => 'c1'],
            ['label' => get_string('score_total', 'block_game'), 'class' => 'c2'],
        ];

        // Ranking.
        $ranklist = block_game_rank_list($this->course->id, $this->groupid);
        $ord = 1;

        foreach ($ranklist as $gamer) {
            if ($limit > 0 && $ord > $limit) {
                break;
            }
            $avatar = '';
            if (!empty($this->cfggame->use_avatar)) {
                $imgurl = $CFG->wwwroot . '/blocks/game/pix/a' . block_game_get_avatar_user($gamer->userid) . '.svg';
                $avatar = '<img src="' . $imgurl . '" width="40" height="40" />';
            }

            $iscurrent = $gamer->userid == $this->user->id;

            $usertext = $avatar . ' ' . $gamer->firstname . ' ' . $gamer->lastname;
            $scoretext = $gamer->pt . get_string('abbreviate_score', 'block_game');
            $ordtext = $ord . '&ordm;';

            if ($iscurrent) {
                $usertext = '<strong>' . $usertext . '</strong>';
                $scoretext = '<strong>' . $scoretext . '</strong>';
                $ordtext = '<strong>' . $ordtext . '</strong>';
            }

            $row = [
                'ord' => $ordtext,
                'user' => $usertext,
                'score' => $scoretext,
            ];

            if ($showdetails) {
                $row['sum_score_activities'] = isset($gamer->sum_score_activities) ? (int) $gamer->sum_score_activities : 0;
                $row['sum_score_module_completed'] = isset($gamer->sum_score_module_completed) ?
                        (int) $gamer->sum_score_module_completed : 0;
                $row['sum_score_section'] = isset($gamer->sum_score_section) ? (int) $gamer->sum_score_section : 0;
                $row['sum_score_bonus_day'] = isset($gamer->sum_score_bonus_day) ? (int) $gamer->sum_score_bonus_day : 0;
                $row['sum_score_badges'] = isset($gamer->sum_score_badges) ? (int) $gamer->sum_score_badges : 0;
            }

            $templatecontext['gamers'][] = $row;
            $ord++;
        }

        // Contar jogadores que não começaram.
        $notstarted = block_game_get_no_players($this->course->id, $this->groupid);
        if ($notstarted > 0) {
            $templatecontext['notstarted'] = true;
            $templatecontext['notstartedtext'] = $notstarted == 1
                ? get_string('not_start_game', 'block_game')
                : get_string('not_start_game_s', 'block_game');
            $templatecontext['notstarted'] = $notstarted;
        }
        return $templatecontext;
    }
}
