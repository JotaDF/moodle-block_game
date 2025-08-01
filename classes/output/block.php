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
 * Game block
 *
 * @package    block_game
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_game\output;

use renderable;
use templatable;
use renderer_base;

/**
 * Ranking block renderable class.
 *
 * @package    block_game
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block implements renderable, templatable {
    /** @var config */
    protected $config;
    /** @var user */
    protected $user;
    /** @var id course */
    protected $courseid;
    /** @var stdClass|null */
    public $course = null;

    /**
     * Block constructor.
     *
     * @param stdClass $config
     * @param stdClass $user
     * @param stdClass $course
     */
    public function __construct($config, $user = null, $course = null) {
        global $USER, $COURSE;

        $this->config = $config;
        $this->user = !$user ? $USER : $user;
        $this->course = !$course ? $COURSE : $course;
        $this->courseid = $this->course->id;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \coding_exception
     *
     * @throws \dml_exception
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        // Load Game of user.
        $game = new \stdClass();
        $game->courseid = $this->courseid;
        $game->userid = $this->user->id;

        $game = block_game_load_game($game);

        if (!$game) {
            return [];
        }

        $game->config = $this->config;
        // Get block ranking configuration.
        $cfggame = get_config('block_game');
        if ($this->courseid == SITEID) {
            $game->config = $cfggame;
        }

        $showavatar = !isset($cfggame->use_avatar) || $cfggame->use_avatar == 1;
        $changeavatar = !isset($cfggame->change_avatar_course) || $cfggame->change_avatar_course == 1;
        $shownamecourse = !isset($game->config->show_name_course) || $game->config->show_name_course == 1;
        $showrank = !isset($game->config->show_rank) || $game->config->show_rank == 1;
        $showrankgroup = !isset($game->config->show_rank_group) || $game->config->show_rank_group == 1;
        $showinfo = !isset($game->config->show_info) || $game->config->show_info == 1;
        $showscore = !isset($game->config->show_score) || $game->config->show_score == 1;
        $showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;
        $scoreactivities = !isset($game->config->score_activities) || $game->config->score_activities == 1;

        $coursedata = block_game_get_course_activities($this->courseid);
        $activities = $coursedata['activities'];
        $atvscheck = [];
        foreach ($activities as $activity) {
            $atvcheck = 'atv' . $activity['id'];
            if (isset($this->config->$atvcheck) && $this->config->$atvcheck > 0) {
                $atvscheck[] = $activity;
            }
        }

        $scoreok = true;
        // If of course score oly student.
        if ($this->courseid != SITEID && block_game_is_student_user($this->user->id, $this->courseid) == 0) {
            $scoreok = false;
        }
        $game = block_game_process_game($game, $scoreok, $showlevel, $scoreactivities, $atvscheck, $cfggame);

        if ($this->user->id != 0) {
            // Links info and reset.
            $showlinks = false;
            $resetgame = '';
            $context = \context_course::instance($this->courseid);
            if (has_capability('moodle/course:update', $context, $this->user->id)) {
                // Teacher.
                if (isset($this->user->editing) && $this->user->editing && $this->courseid != SITEID) {
                    $resetgame = '<a title="' . get_string('reset_points_btn', 'block_game') . '" href="';
                    $resetgame .= $CFG->wwwroot . '/blocks/game/reset_points_course.php?id=' . $this->courseid;
                    $resetgame .= '"><img alt="' . get_string('reset_points_btn', 'block_game') . '" hspace="5" src="';
                    $resetgame .= $CFG->wwwroot . '/blocks/game/pix/reset.svg" height="24" width="24"/></a>';
                    $showlinks = true;
                }
            }
            $linkinfo = '';
            if ($showinfo) {
                $linkinfo = '<a href="' . $CFG->wwwroot . '/blocks/game/perfil_gamer.php?id=';
                $linkinfo .= $this->courseid . '">' . '<img title="';
                $linkinfo .= get_string('help_info_user_titulo', 'block_game') . '" hspace="5" src="';
                $linkinfo .= $CFG->wwwroot . '/blocks/game/pix/info.svg" height="24" width="24"/></a>';
                $showlinks = true;
            }
            $userpictureparams = ['size' => 80, 'link' => false, 'alt' => 'User',];
            $userpicture = $output->user_picture($this->user, $userpictureparams);
            if ($showavatar) {
                $img = $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.svg"';
                $fs = get_file_storage();
                if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $game->avatar . '.svg')) {
                    $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $game->avatar);
                }
                if ($this->courseid == SITEID || $changeavatar) {
                    $userpicture = '<form action="' . $CFG->wwwroot;
                    $userpicture .= '/blocks/game/set_avatar_form.php" method="get">';
                    $userpicture .= '<input name="id" type="hidden" value="' . $this->courseid . '">';
                    $userpicture .= '<input name="avatar" type="hidden" value="' . $game->avatar . '">';
                    $userpicture .= ' <input class="img-fluid" type="image" src="' . $img . '" height="140" width="140" /> ';
                    $userpicture .= '</form>';
                } else {
                    $userpicture = '<img title="' . get_string('label_avatar', 'block_game');
                    $userpicture .= '" hspace="5" src="' . $img . '" height="140" width="140"/>';
                }
            }

            $coursename = '';
            if ($this->courseid != SITEID && $shownamecourse) {
                $coursename = '(' . $this->course->shortname . ')';
            }
            $imgscore = '';
            $labelscore = '';
            $score = 0;
            if ($showscore) {
                $imgscore = '<img title="' . get_string('label_score', 'block_game') . '" src="';
                $imgscore .= $CFG->wwwroot . '/blocks/game/pix/score.svg" height="65" width="65"/>';
                $labelscore .= get_string('label_score', 'block_game');
                $score = $game->scorefull;
            }
            $imgrank = '';
            $labelrank = '';
            $rank = 0;
            $fullrank = 0;
            if ($showrank) {
                $imgrank = '<img title="' . get_string('label_rank', 'block_game') . '" src="';
                $imgrank .= $CFG->wwwroot . '/blocks/game/pix/rank.svg" height="65" width="65"/>';
                $labelrank = get_string('label_rank', 'block_game');
                $rank = $game->ranking;
                $fullrank = block_game_get_players($game->courseid, $game->groupid);
            }
            $imglevel = '';
            $labellevel = '';
            $level = 0;
            $nextlevel = '';
            $helpprogresslevel = get_string('help_progress_level_text', 'block_game');
            $percent = 0;
            if ($showlevel && isset($game->config->show_level)) {
                $fs = get_file_storage();
                if ($fs->file_exists(1, 'block_game', 'imagens_levels', 0, '/', 'lv' . $game->level . '.svg')) {
                    $imglv = block_game_pix_url(1, 'imagens_levels', 'lv' . $game->level);
                } else {
                    $imglv = $CFG->wwwroot . '/blocks/game/pix/lv' . $game->level . '.svg';
                }
                $imglevel = '<img title="' . get_string('label_level', 'block_game');
                $imglevel .= '" src="' . $imglv . '" height="65" width="65"/>';
                $labellevel = get_string('label_level', 'block_game');
                $level = $game->level;

                // Progress Bar.
                $xlevel = 'level_up' . ($game->level + 1);
                $maxok = false;
                if ($game->level == $game->config->level_number) {
                    $xlevel = 'level_up' . $game->level;
                    $maxok = true;
                }
                $percent = round($game->percent, 1);
                if ($maxok) {
                    $nextlevel = get_string('level_max_ok', 'block_game') . '</div>';
                } else {
                    $nextlevel = get_string('next_level', 'block_game') . ' =>'
                            . $game->config->$xlevel . get_string('abbreviate_score', 'block_game');
                }
            }

            $widthtools = '100%';
            if ($showrank) {
                $widthtools = '50%';
            }
            if ($showrankgroup && $this->courseid != SITEID) {
                $widthtools = '33%';
            }

            $rankgame = '';
            if ($showrank) {
                $linkgroup = '';
                if ($this->course->groupmode == 1 || $this->course->groupmode == 2) {
                    $linkgroup = '&group=' . $game->groupid;
                }
                $rankgame = '<a href="'
                        . $CFG->wwwroot . '/blocks/game/rank_game.php?id=' . $this->courseid . $linkgroup . '"><img alt="'
                        . get_string('label_rank', 'block_game') . '" title="'
                        . get_string('label_rank', 'block_game') . '" src="'
                        . $CFG->wwwroot . '/blocks/game/pix/rank_list.svg" height="28" width="28"/></a>';
            }
            $rankgroup = '';
            if ($showrankgroup && $this->courseid != SITEID) {
                $rankgroup = '<a href="'
                        . $CFG->wwwroot . '/blocks/game/rank_group_game.php?id=' . $this->courseid . '"><img alt="'
                        . get_string('label_rank_group', 'block_game') . '" title="'
                        . get_string('label_rank_group', 'block_game') . '" src="'
                        . $CFG->wwwroot . '/blocks/game/pix/rank_group_list.svg" height="28" width="41"/></a>';
            }
            $help = '<a href="' . $CFG->wwwroot . '/blocks/game/help_game.php?id='
                    . $this->courseid . '"><img alt="' . get_string('help', 'block_game') . '" title="'
                    . get_string('help', 'block_game') . '" src="'
                    . $CFG->wwwroot . '/blocks/game/pix/help.svg"  height="28" width="28"/></a>';
        }
        $data = [
            'userpicture' => $userpicture,
            'resetgame' => $resetgame,
            'linkinfo' => $linkinfo,
            'coursename' => $coursename,
            'imgscore' => $imgscore,
            'labelscore' => $labelscore,
            'score' => $score,
            'imgrank' => $imgrank,
            'labelrank' => $labelrank,
            'rank' => $rank,
            'fullrank' => $fullrank,
            'imglevel' => $imglevel,
            'labellevel' => $labellevel,
            'level' => $level,
            'nextlevel' => $nextlevel,
            'helpprogresslevel' => $helpprogresslevel,
            'percent' => $percent,
            'widthtools' => $widthtools,
            'rankgame' => $rankgame,
            'rankgroup' => $rankgroup,
            'help' => $help,
        ];
        return $data;
    }
}
