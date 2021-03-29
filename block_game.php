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
require_once($CFG->dirroot . '/blocks/game/libgame.php');
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

        $game = load_game($game);
        $game->config = $this->config;

        if ($COURSE->id == 1) {
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
        $showidentity = !isset($game->config->show_identity) || $game->config->show_identity == 1;
        $showrank = !isset($game->config->show_rank) || $game->config->show_rank == 1;
        $showrankgroup = !isset($game->config->show_rank_group) || $game->config->show_rank_group == 1;
        $rankgroupcalc = !isset($game->config->rank_group_calc) || $game->config->rank_group_calc == 1;
        $showinfo = !isset($game->config->show_info) || $game->config->show_info == 1;
        $showscore = !isset($game->config->show_score) || $game->config->show_score == 1;
        $showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;
        $scoreactivities = !isset($game->config->score_activities) || $game->config->score_activities == 1;

        $scoreok = true;
        // If of course score oly student.
        if ($COURSE->id > 1 && is_student_user($USER->id, $COURSE->id) == 0) {
            $scoreok = false;
        }

        $levelnumber = 0;
        // Config level up.
        if ($showlevel && isset($game->config->show_level)) {
            $levelnumber = (int) $game->config->level_number;
            $levelup[0] = (int) $game->config->level_up1;
            $levelup[1] = (int) $game->config->level_up2;
            $levelup[2] = (int) $game->config->level_up3;
            $levelup[3] = (int) $game->config->level_up4;
            $levelup[4] = (int) $game->config->level_up5;
            $levelup[5] = (int) $game->config->level_up6;
            $levelup[6] = (int) $game->config->level_up7;
            $levelup[7] = (int) $game->config->level_up8;
            $levelup[8] = (int) $game->config->level_up9;
            $levelup[9] = (int) $game->config->level_up10;
            $levelup[10] = (int) $game->config->level_up11;
            $levelup[11] = (int) $game->config->level_up12;
        }
        if ($COURSE->id > 1) {
            // Sum score sections complete.
            $sections = get_sections_course($COURSE->id);
            $scoresections = 0;
            foreach ($sections as $section) {
                $txtsection = "section_" . $section->section;
                if (is_check_section($USER->id, $COURSE->id, $section->id)) {
                    $scoresections += (int) $game->config->$txtsection;
                }
            }
            if ($scoreok) {
                score_section($game, $scoresections);
                $game->score_section = $scoresections;
            }
        }
        // Bonus of day.
        if (isset($game->config->bonus_day)) {
            $addbonusday = $game->config->bonus_day;
        } else {
            $addbonusday = 0;
        }
        if ($addbonusday > 0 && $scoreok) {
            bonus_of_day($game, $addbonusday);
        }

        // Bonus of badge.
        if (isset($cfggame->bonus_badge)) {
            $bonusbadge = $cfggame->bonus_badge;
            if ($scoreok) {
                $game = score_badge($game, $bonusbadge);
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
            score_activities($game);
            $game = ranking($game, $groupid);
            if ($showlevel && isset($game->config->show_level)) {
                $game = set_level($game, $levelup, $levelnumber);
            }
        } else {
            no_score_activities($game);
            $game = ranking($game, $groupid);
            if ($showlevel && isset($game->config->show_level)) {
                $game = set_level($game, $levelup, $levelnumber);
            }
        }

        $table = new html_table();
        $table->attributes = array('class' => 'gameTable', 'style' => 'width: 100%;');

        if ($USER->id != 0) {
            $row = array();
            $userpictureparams = array('size' => 20, 'link' => false, 'alt' => 'User');
            $userpicture = $OUTPUT->user_picture($USER, $userpictureparams);
            if ($showavatar) {
                if ($COURSE->id == 1 || $changeavatar) {
                    $userpicture = '<a href="' . $CFG->wwwroot
                            . '/blocks/game/set_avatar_form.php?id=' . $COURSE->id . '&avatar='
                            . $game->avatar . '">' . '<img hspace="5" src="' . $CFG->wwwroot . '/blocks/game/pix/a'
                            . $game->avatar . '.png" height="40" width="40"/></a>';
                } else {
                    $userpicture = '<img hspace="5" src="' . $CFG->wwwroot . '/blocks/game/pix/a'
                            . $game->avatar . '.png" height="40" width="40"/>';
                }
            }

            $resetgame = '';
            $context = context_course::instance($COURSE->id, MUST_EXIST);
            if (has_capability('moodle/course:update', $context, $USER->id)) {
                // Teacher.
                $resetgame = '<a title="' . get_string('reset_points_btn', 'block_game') . '" href="'
                        . $CFG->wwwroot . '/blocks/game/reset_points_course.php?id=' . $COURSE->id
                        . '"><img alt="' . get_string('reset_points_btn', 'block_game') . '" hspace="12" src="'
                        . $CFG->wwwroot . '/blocks/game/pix/reset.png"/></a>';
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
            if ($COURSE->id != 1 && $shownamecourse) {
                $coursetxt = '(' . $COURSE->shortname . ')';
                $row[] = $coursetxt;
                $table->data[] = $row;
            }
            if ($showrank) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/game/pix/rank.png" height="20" width="20"/>';
                $row[] = $icontxt . ' ' . get_string('label_rank', 'block_game')
                        . ': ' . $game->ranking . '&ordm; / ' . get_players($game->courseid, $groupid);
                $table->data[] = $row;
            }
            if ($showscore) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/game/pix/score.png" height="20" width="20"/>';
                $row[] = $icontxt . ' ' . get_string('label_score', 'block_game') . ': '
                        . (int) ($game->score + $game->score_bonus_day + $game->score_activities +
                        $game->score_badges + $game->score_section) . '';
                $table->data[] = $row;
            }
            if ($showlevel && isset($game->config->show_level)) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/game/pix/level.png" height="20" width="20"/>';
                $row[] = $icontxt . ' ' . get_string('label_level', 'block_game') . ': ' . $game->level . '';
                $table->data[] = $row;

                $percent = 0;
                $nextlevel = $game->level + 1;
                if ($nextlevel <= $levelnumber) {
                    $total = (int) ($game->score + $game->score_bonus_day +
                            $game->score_activities + $game->score_badges +
                            $game->score_section);
                    $percent = 0;
                    if ($total > 0) {
                        $percent = ($total * 100) / $levelup[$game->level];
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
                        . $CFG->wwwroot . '/blocks/game/pix/rank_list.png" height="24" width="24"/></a></td>';
            }
            if ($showrankgroup && $COURSE->id > 1) {
                $icontxtrank .= '<td align="center" width="33%"><a href="'
                        . $CFG->wwwroot . '/blocks/game/rank_group_game.php?id=' . $COURSE->id . '"><img alt="'
                        . get_string('label_rank_group', 'block_game') . '" title="'
                        . get_string('label_rank_group', 'block_game') . '" src="'
                        . $CFG->wwwroot . '/blocks/game/pix/rank_group_list.png" height="24" width="35"/></a></td>';
            }
            $icontxtrank .= '<td align="right" width="33%"><a href="' . $CFG->wwwroot . '/blocks/game/help_game.php?id='
                    . $COURSE->id . '"><img alt="' . get_string('help', 'block_game') . '" title="'
                    . get_string('help', 'block_game') . '" src="'
                    . $CFG->wwwroot . '/blocks/game/pix/help.png"  height="24" width="24"/></a></td>';
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
