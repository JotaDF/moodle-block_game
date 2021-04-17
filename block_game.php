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
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/game/lib.php');
require_login();

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
        $game->config = $this->config;

        if ($COURSE->id == SITEID) {
            $game->config = get_config('block_game');
        }
        $SESSION->game = $game;

        // Get block ranking configuration.
        $cfggame = get_config('block_game');

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
        $levelnumber = 0;
        // Config level up.
        if ($showlevel && isset($game->config->show_level)) {
            $levelnumber = (int) $game->config->level_number;
            $levelup = array();
            for ($i = 1; $i <= $game->config->level_number; $i++) {
                $xlevel = 'level_up' . $i;
                $levelup[] = (int) $game->config->$xlevel;
            }
        }
        if ($COURSE->id != SITEID) {
            // Sum score sections complete.
            $sections = block_game_get_sections_course($COURSE->id);
            $scoresections = 0;
            foreach ($sections as $section) {
                $txtsection = "section_" . $section->section;
                if (block_game_is_check_section($USER->id, $COURSE->id, $section->id)) {
                    if (isset($game->config->$txtsection)) {
                        $scoresections += (int) $game->config->$txtsection;
                    }
                }
            }
            if ($scoreok) {
                block_game_score_section($game, $scoresections);
                $game->score_section = $scoresections;
            }
        }
        // Bonus of day.
        $addbonusday = 0;
        if (isset($game->config->bonus_day)) {
            $addbonusday = $game->config->bonus_day;
        }
        if ($addbonusday > 0 && $scoreok) {
            block_game_bonus_of_day($game, $addbonusday);
        }
        // Bonus of badge.
        if (isset($cfggame->bonus_badge)) {
            $bonusbadge = $cfggame->bonus_badge;
            if ($scoreok) {
                $game = block_game_score_badge($game, $bonusbadge);
            }
        }
        $groupid = 0;
        if ($COURSE->groupmode == 1 || $COURSE->groupmode == 2) {
            $groups = groups_get_all_groups($COURSE->id, $USER->id);
            foreach ($groups as $group) {
                $groupid = $group->id;
            }
        }
        if ($scoreactivities && $scoreok) {
            block_game_score_activities($game);
            $game = block_game_ranking($game, $groupid);
            if ($showlevel && isset($game->config->show_level)) {
                $game = block_game_set_level($game, $levelup, $levelnumber);
            }
        } else {
            block_game_no_score_activities($game);
            $game = block_game_ranking($game, $groupid);
            if ($showlevel && isset($game->config->show_level)) {
                $game = block_game_set_level($game, $levelup, $levelnumber);
            }
        }
        $table = new html_table();
        $table->attributes = array('class' => 'gameTable', 'style' => 'width: 100%;');
        if ($USER->id != 0) {
            $row = array();
            $userpictureparams = array('size' => 20, 'link' => false, 'alt' => 'User');
            $userpicture = $OUTPUT->user_picture($USER, $userpictureparams);
            if ($showavatar) {
                if ($COURSE->id == SITEID || $changeavatar) {
                    $userpicture = '<a href="' . $CFG->wwwroot
                            . '/blocks/game/set_avatar_form.php?id=' . $COURSE->id . '&avatar='
                            . $game->avatar . '">' . '<img hspace="5" src="' . $CFG->wwwroot . '/blocks/game/pix/a'
                            . $game->avatar . '.png" height="80" width="80"/></a>';
                } else {
                    $userpicture = '<img hspace="5" src="' . $CFG->wwwroot . '/blocks/game/pix/a'
                            . $game->avatar . '.png" height="80" width="80"/>';
                }
            }
            $resetgame = '';
            $context = context_course::instance($COURSE->id, MUST_EXIST);
            if (has_capability('moodle/course:update', $context, $USER->id)) {
                // Teacher.
                if (isset($USER->editing) && $USER->editing && $COURSE->id > 1) {
                    $resetgame = '<a title="' . get_string('reset_points_btn', 'block_game') . '" href="'
                            . $CFG->wwwroot . '/blocks/game/reset_points_course.php?id=' . $COURSE->id
                            . '"><img alt="' . get_string('reset_points_btn', 'block_game') . '" hspace="12" src="'
                            . $CFG->wwwroot . '/blocks/game/pix/reset.png"/></a>';
                }
            }
            $linkinfo = '';
            if ($showinfo) {
                $linkinfo = '<a href="' . $CFG->wwwroot . '/blocks/game/perfil_gamer.php?id='
                        . $COURSE->id . '">' . '<img hspace="12" src="'
                        . $CFG->wwwroot . '/blocks/game/pix/info.png"/></a>';
            }
            $row[] = $userpicture . get_string('label_you', 'block_game') . $linkinfo . ' ' . $resetgame;
            $table->data[] = $row;
            $row = array();
            $icontxt = $OUTPUT->pix_icon('logo', '', 'theme');
            if ($COURSE->id != SITEID && $shownamecourse) {
                $row[] = '(' . $COURSE->shortname . ')';
                $table->data[] = $row;
            }
            if ($showrank) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/game/pix/rank.png" height="30" width="30"/>';
                $row[] = $icontxt . ' ' . get_string('label_rank', 'block_game')
                        . ': ' . $game->ranking . '&ordm; / ' . block_game_get_players($game->courseid, $groupid);
                $table->data[] = $row;
            }
            $scorefull = (int) ($game->score + $game->score_bonus_day + $game->score_activities +
                    $game->score_badges + $game->score_section);
            if ($COURSE->id != SITEID) {
                $scorefull = (int) ($game->score + $game->score_bonus_day + $game->score_activities + $game->score_section);
            }
            if ($showscore) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/game/pix/score.png" height="30" width="30"/>';
                $row[] = $icontxt . ' ' . get_string('label_score', 'block_game') . ': ' . $scorefull . '';
                $table->data[] = $row;
            }
            if ($showlevel && isset($game->config->show_level)) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/game/pix/level.png" height="30" width="30"/>';
                $row[] = $icontxt . ' ' . get_string('label_level', 'block_game') . ': ' . $game->level . '';
                $table->data[] = $row;

                $percent = 0;
                $nextlevel = $game->level + 1;
                if ($nextlevel <= $levelnumber) {
                    $percent = 0;
                    if ($scorefull > 0) {
                        $percent = ($scorefull * 100) / $levelup[$game->level];
                    }
                }
                $row = array();
                $progressbar = '<div style="height:12px; padding:2px; background-color:#ccc; text-align:right; font-size:12px;">';
                $progressbar .= '<div style="height: 8px; width:' . $percent;
                $progressbar .= '%; padding: 0px; background-color: #356ebc;"></div>';
                $progressbar .= get_string('next_level', 'block_game') . ' =>' . $levelup[$game->level] . '</div>';
                $row[] = $progressbar;
                $table->data[] = $row;
            }
            $row = array();
            $icontxtrank = '<hr/><table border="0" width="100%"><tr>';
            if ($showrank) {
                $linkgroup = '';
                if ($COURSE->groupmode == 1 || $COURSE->groupmode == 2) {
                    $linkgroup = '&group=' . $groupid;
                }
                $icontxtrank .= '<td align="left" width="33%"><a href="'
                        . $CFG->wwwroot . '/blocks/game/rank_game.php?id=' . $COURSE->id . $linkgroup . '"><img alt="'
                        . get_string('label_rank', 'block_game') . '" title="'
                        . get_string('label_rank', 'block_game') . '" src="'
                        . $CFG->wwwroot . '/blocks/game/pix/rank_list.png" height="28" width="28"/></a></td>';
            }
            if ($showrankgroup && $COURSE->id > 1) {
                $icontxtrank .= '<td align="center" width="33%"><a href="'
                        . $CFG->wwwroot . '/blocks/game/rank_group_game.php?id=' . $COURSE->id . '"><img alt="'
                        . get_string('label_rank_group', 'block_game') . '" title="'
                        . get_string('label_rank_group', 'block_game') . '" src="'
                        . $CFG->wwwroot . '/blocks/game/pix/rank_group_list.png" height="28" width="41"/></a></td>';
            }
            $icontxtrank .= '<td align="right" width="33%"><a href="' . $CFG->wwwroot . '/blocks/game/help_game.php?id='
                    . $COURSE->id . '"><img alt="' . get_string('help', 'block_game') . '" title="'
                    . get_string('help', 'block_game') . '" src="'
                    . $CFG->wwwroot . '/blocks/game/pix/help.svg"  height="28" width="28"/></a></td>';
            $icontxtrank .= '</tr></table>';
            $row[] = $icontxtrank;
            $table->data[] = $row;
        } else {
            $row[] = '';
            $table->data[] = $row;
        }
        $this->content->text .= HTML_WRITER::table($table);
        $this->content->footer = '';
        return $this->content;
    }

}
