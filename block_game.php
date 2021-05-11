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
 * Game block definition
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/game/lib.php');

/**
 *  Block Game config form definition class
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_game extends block_base {
    /**
     * Sets the block title
     *
     * @return none
     */
    public function init() {
        $this->title = get_string('game_title_default', 'block_game');
    }

    /**
     * Controls the block title based on instance configuration
     *
     * @return bool
     */
    public function specialization() {
        global $course;

        // Need the bigger course object.
        $this->course = $course;

        // Override the block title if an alternative is set.
        if (isset($this->config->game_title) && trim($this->config->game_title) != '') {
            $this->title = format_string($this->config->game_title);
        }
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view' => true,
            'site-index' => true,
            'mod' => true,
            'my' => true
        );
    }

    /**
     * Controls global configurability of block
     *
     * @return bool
     */
    public function instance_allow_config() {
        return false;
    }

    /**
     * Controls global configurability of block
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Controls if a block header is shown based on instance configuration
     *
     * @return bool
     */
    public function hide_header() {
        return isset($this->config->show_header) && $this->config->show_header == 0;
    }

    /**
     * Creates the block's main content
     *
     * @return string
     */
    public function get_content() {
        global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;
        // Load Game of user.
        $game = new stdClass();
        $game->courseid = $COURSE->id;
        $game->userid = $USER->id;
        $game = block_game_load_game($game);

        if ($game) {
            $game->config = $this->config;
            // Get block ranking configuration.
            $cfggame = get_config('block_game');
            if ($COURSE->id == SITEID) {
                $game->config = $cfggame;
            }

            if (isset($this->content)) {
                return $this->content;
            }

            // Start the content, which is primarily a table.
            $this->content = new stdClass;
            $this->content->text = '';
            $this->content->footer = '';
            $showavatar = !isset($cfggame->use_avatar) || $cfggame->use_avatar == 1;
            $changeavatar = !isset($cfggame->change_avatar_course) || $cfggame->change_avatar_course == 1;
            $shownamecourse = !isset($game->config->show_name_course) || $game->config->show_name_course == 1;
            $showrank = !isset($game->config->show_rank) || $game->config->show_rank == 1;
            $showrankgroup = !isset($game->config->show_rank_group) || $game->config->show_rank_group == 1;
            $showinfo = !isset($game->config->show_info) || $game->config->show_info == 1;
            $showscore = !isset($game->config->show_score) || $game->config->show_score == 1;
            $showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;
            $scoreactivities = !isset($game->config->score_activities) || $game->config->score_activities == 1;

            $scoreok = true;
            // If of course score oly student.
            if ($COURSE->id != SITEID && block_game_is_student_user($USER->id, $COURSE->id) == 0) {
                $scoreok = false;
            }
            $game = block_game_process_game($game, $scoreok, $showlevel, $scoreactivities, $cfggame);
            $table = new html_table();
            $table->attributes = array('class' => 'gameTable', 'style' => 'width: 100%;');
            if ($USER->id != 0) {
                // Links info and reset.
                $showlinks = false;
                $resetgame = '';
                $context = context_course::instance($COURSE->id, MUST_EXIST);
                if (has_capability('moodle/course:update', $context, $USER->id)) {
                    // Teacher.
                    if (isset($USER->editing) && $USER->editing && $COURSE->id != SITEID) {
                        $resetgame = '<a title="' . get_string('reset_points_btn', 'block_game') . '" href="';
                        $resetgame .= $CFG->wwwroot . '/blocks/game/reset_points_course.php?id=' . $COURSE->id;
                        $resetgame .= '"><img alt="' . get_string('reset_points_btn', 'block_game') . '" hspace="5" src="';
                        $resetgame .= $CFG->wwwroot . '/blocks/game/pix/reset.svg" height="24" width="24"/></a>';
                        $showlinks = true;
                    }
                }
                $linkinfo = '';
                if ($showinfo) {
                    $linkinfo = '<a href="' . $CFG->wwwroot . '/blocks/game/perfil_gamer.php?id=';
                    $linkinfo .= $COURSE->id . '">' . '<img title="';
                    $linkinfo .= get_string('help_info_user_titulo', 'block_game') . '" hspace="5" src="';
                    $linkinfo .= $CFG->wwwroot . '/blocks/game/pix/info.svg" height="24" width="24"/></a>';
                    $showlinks = true;
                }
                $userpictureparams = array('size' => 80, 'link' => false, 'alt' => 'User');
                $userpicture = $OUTPUT->user_picture($USER, $userpictureparams);
                if ($showavatar) {
                    if ($COURSE->id == SITEID || $changeavatar) {
                        $userpicture = '<a title="' . get_string('set_avatar_title', 'block_game') . '" href="' . $CFG->wwwroot;
                        $userpicture .= '/blocks/game/set_avatar_form.php?id=' . $COURSE->id . '&avatar=';
                        $userpicture .= $game->avatar . '">' . '<img hspace="5" src="' . $CFG->wwwroot . '/blocks/game/pix/a';
                        $userpicture .= $game->avatar . '.svg" height="140" width="140"/></a>';
                    } else {
                        $userpicture = '<img title="' . get_string('label_avatar', 'block_game');
                        $userpicture .= '" hspace="5" src="' . $CFG->wwwroot . '/blocks/game/pix/a';
                        $userpicture .= $game->avatar . '.svg" height="140" width="140"/>';
                    }
                }
                $row = array();
                $div = '<div class="t-100 text-center border-bottom">';
                if ($showlinks) {
                    $div .= '<div class="text-right" style="position: absolute; top: 50px; right: 25px;">';
                    $div .= $linkinfo . ' ' . $resetgame . '</div>';
                }
                $div .= $userpicture . '</div>';
                $row[] = $div;
                $table->data[] = $row;

                $row = array();
                $icontxt = $OUTPUT->pix_icon('logo', '', 'theme');
                if ($COURSE->id != 1 && $shownamecourse) {
                    $row[] = '(' . $COURSE->shortname . ')';
                    $table->data[] = $row;
                }
                $row = array();
                $div = '<div class="container" style="font-size:11px; background-color: #F7F9F9;"><div class="row">';
                if ($showscore) {
                    $div .= '<div class="col- text-center" style="min-width:33%; max-width:100%;">';
                    $icontxt = '<img title="' . get_string('label_score', 'block_game') . '" src="';
                    $icontxt .= $CFG->wwwroot . '/blocks/game/pix/score.svg" height="65" width="65"/>';
                    $div .= $icontxt . '<br/>' . get_string('label_score', 'block_game') . '<br/><strong style="font-size:14px;">';
                    $div .= $game->scorefull . '</strong>';
                    $div .= '</div>';
                }
                if ($showrank) {
                    $div .= '<div class="col- text-center" style="min-width:33%; max-width:100%;">';
                    $icontxt = '<img title="' . get_string('label_rank', 'block_game') . '" src="';
                    $icontxt .= $CFG->wwwroot . '/blocks/game/pix/rank.svg" height="65" width="65"/>';
                    $div .= $icontxt . '<br/>' . get_string('label_rank', 'block_game');
                    $div .= '<br/><strong style="font-size:14px;">' . $game->ranking . '&ordm; / ';
                    $div .= block_game_get_players($game->courseid, $game->groupid) . '</strong>';
                    $div .= '</div>';
                }
                if ($showlevel && isset($game->config->show_level)) {
                    $div .= '<div class="col- text-center" style="min-width:33%; max-width:100%;">';
                    $icontxt = '<img title="' . get_string('label_level', 'block_game');
                    $icontxt .= '" src="' . $CFG->wwwroot . '/blocks/game/pix/level.svg" height="65" width="65"/>';
                    $div .= $icontxt . '<br/>' . get_string('label_level', 'block_game');
                    $div .= '<br/><strong style="font-size:14px;">' . $game->level . '</strong>';
                    $div .= '</div>';
                    // Progress Bar.
                    $xlevel = 'level_up' . ($game->level + 1);
                    $div .= '</div>';
                    $div .= '<div class="row">';
                    $div .= '<div class="col-sm">';
                    $div .= '<div class="progress" title="' . get_string('help_progress_level_text', 'block_game') . '">';
                    $percent = round($game->percent, 1);
                    $div .= '<div class="progress-bar" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="';
                    $div .= $percent . '" aria-valuemin="0" aria-valuemax="100">';
                    $div .= $percent . '%';
                    $div .= '</div></div></div></div>';
                    $div .= '<div class="row">';
                    $div .= '<div class="col-sm text-right" title="' . get_string('help_progress_level_text', 'block_game') . '">';
                    $div .= get_string('next_level', 'block_game') . ' =>' . $game->config->$xlevel;
                    $div .= get_string('abbreviate_score', 'block_game') . '</div>';
                    $div .= '</div>';
                }
                $div .= '</div></div>';
                $row[] = $div;
                $table->data[] = $row;
                $widthtools = '100%';
                if ($showrank) {
                    $widthtools = '50%';
                }
                if ($showrankgroup && $COURSE->id != SITEID) {
                    $widthtools = '33%';
                }
                $row = array();
                $toolsbar = '<div class="container pt-2 pb-2 border-top"  style="background-color: #F7F9F9;"><div class="row">';
                if ($showrank) {
                    $linkgroup = '';
                    if ($COURSE->groupmode == 1 || $COURSE->groupmode == 2) {
                        $linkgroup = '&group=' . $game->groupid;
                    }
                    $toolsbar .= '<div class="pl-3 col- text-left" style="min-width:' . $widthtools . ';">';
                    $toolsbar .= '<a href="'
                            . $CFG->wwwroot . '/blocks/game/rank_game.php?id=' . $COURSE->id . $linkgroup . '"><img alt="'
                            . get_string('label_rank', 'block_game') . '" title="'
                            . get_string('label_rank', 'block_game') . '" src="'
                            . $CFG->wwwroot . '/blocks/game/pix/rank_list.svg" height="28" width="28"/></a>';
                    $toolsbar .= '</div>';
                }
                if ($showrankgroup && $COURSE->id != SITEID) {
                    $toolsbar .= '<div class="col- text-center" style="min-width:' . $widthtools . ';">';
                    $toolsbar .= '<a href="'
                            . $CFG->wwwroot . '/blocks/game/rank_group_game.php?id=' . $COURSE->id . '"><img alt="'
                            . get_string('label_rank_group', 'block_game') . '" title="'
                            . get_string('label_rank_group', 'block_game') . '" src="'
                            . $CFG->wwwroot . '/blocks/game/pix/rank_group_list.svg" height="28" width="41"/></a>';
                    $toolsbar .= '</div>';
                }
                $toolsbar .= '<div class="pr-3  col- text-right" style="min-width:' . $widthtools . ';">';
                $toolsbar .= '<a href="' . $CFG->wwwroot . '/blocks/game/help_game.php?id='
                        . $COURSE->id . '"><img alt="' . get_string('help', 'block_game') . '" title="'
                        . get_string('help', 'block_game') . '" src="'
                        . $CFG->wwwroot . '/blocks/game/pix/help.svg"  height="28" width="28"/></a>';
                $toolsbar .= '</div>';
                $toolsbar .= '</div></div>';
                $row[] = $toolsbar;
                $table->data[] = $row;
            } else {
                $row[] = '';
                $table->data[] = $row;
            }
            $this->content->text .= HTML_WRITER::table($table);
            $this->content->footer = '';
            return $this->content;
        } else {
            return '';
        }
    }

}
