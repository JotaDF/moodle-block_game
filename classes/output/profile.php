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
use context_system;
use context_course;
use moodle_url;

/**
 * Ranking block renderable class.
 *
 * @package    block_game
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile implements renderable, templatable {
    /** @var user */
    protected $user;
    /** @var id course */
    protected $courseid;
    /** @var stdClass|null */
    public $course = null;

    /**
     * Profile constructor.
     *
     * @param stdClass $user
     * @param int $courseid
     */
    public function __construct($user = null, $courseid = null) {
        global $USER, $COURSE;

        $this->user = !$user ? $USER : $user;
        $this->courseid = !$courseid ? $COURSE->id : $courseid;
        $this->course = $COURSE;
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
        global $CFG, $DB;
        $game = new \stdClass();
        $game->courseid = $this->courseid;
        $game->userid = $this->user->id;

        $game = block_game_load_game($game);
        $cfggame = get_config('block_game');

        $game->config = $cfggame;
        if ($game->courseid != SITEID) {
            $game->config = block_game_get_config_block($game->courseid);
        }

        $showavatar = !isset($game->config->use_avatar) || $game->config->use_avatar == 1;
        $showrank = !isset($game->config->show_rank) || $game->config->show_rank == 1;
        $showscore = !isset($game->config->show_score) || $game->config->show_score == 1;
        $showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;

        $coursename = $this->course->fullname;
        $imgavatar = $output->user_picture($this->user, array('size' => 80, 'hspace' => 12));
        if ($showavatar == 1) {
            $imgavatar = '<img  align="center" hspace="12" height="140" width="140" src="';
            $fs = get_file_storage();
            if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $game->avatar . '.svg')) {
                $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $game->avatar);
            } else {
                $img = $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.svg';
            }
            $imgavatar .= $img . '" title="avatar"/>';
        }
        $username = $this->user->firstname . ' ' . $this->user->lastname;

        // Get strings table.
        $courselabel = get_string('course');
        $scorelabelfull = get_string('score_total', 'block_game');
        $scoreatvlabel = get_string('score_atv', 'block_game');
        $scoremodlabel = get_string('score_mod', 'block_game');
        $scoresectionlabel = get_string('score_section', 'block_game');
        $scorebonuslabel = get_string('score_bonus_day', 'block_game');
        $scorebadgelabel = get_string('label_badge', 'block_game');
        $ranklabel = get_string('label_rank', 'block_game');
        $levellabel = get_string('label_level', 'block_game');
        $nextlevellabel = get_string('next_level', 'block_game');

        $rs = block_game_get_games_user($game->userid);
        $fullpointsall = 0;
        $fullpoints = 0;
        $fullpointsbonus = 0;
        $fullpointsactivities = 0;
        $fullpointsmodulecompleted = 0;
        $fullpointssection = 0;
        $fullpointsbadges = 0;
        $courses = [];
        $hascourses = false;
        foreach ($rs as $gameuser) {
            $hascourses = true;
            $course = $DB->get_record('course', array('id' => $gameuser->courseid));
            if ($gameuser->courseid != SITEID) {
                $coursename = $course->fullname;
            } else {
                $coursename = get_string('environment', 'admin');
            }

            $rank = 0;
            $fullrank = 0;
            if ($showrank) {
                $rank = $gameuser->ranking;
                $fullrank = block_game_get_players($gameuser->courseid);
            }

            $imglevel = '';
            $level = 0;
            $nextlevel = '';
            $helpprogresslevel = get_string('help_progress_level_text', 'block_game');
            $percent = 0;
            if ($showlevel) {
                $gameuser = block_game_get_percente_level($gameuser);
                $xlevel = 'level_up' . ($gameuser->level + 1);
                $maxok = false;
                if ($gameuser->level == $game->config->level_number) {
                    $xlevel = 'level_up' . $gameuser->level;
                    $maxok = true;
                }

                $fs = get_file_storage();
                if ($fs->file_exists(1, 'block_game', 'imagens_levels', 0, '/', 'lv' . $gameuser->level . '.svg')) {
                    $imglv = block_game_pix_url(1, 'imagens_levels', 'lv' . $gameuser->level);
                } else {
                    $imglv = $CFG->wwwroot . '/blocks/game/pix/lv' . $gameuser->level . '.svg';
                }
                $imglevel = '<img title="' . get_string('label_level', 'block_game');
                $imglevel .= '" src="' . $imglv . '" height="40" width="40"/>';
                $level = $gameuser->level;

                // Progress Bar.
                $xlevel = 'level_up' . ($gameuser->level + 1);
                $maxok = false;
                if ($gameuser->level == $game->config->level_number) {
                    $xlevel = 'level_up' . $gameuser->level;
                    $maxok = true;
                }
                $percent = round($gameuser->percent, 1);
                if ($maxok) {
                    $nextlevel = get_string('level_max_ok', 'block_game');
                } else {
                    $nextlevel = get_string('next_level', 'block_game') . ' =>'
                            . $game->config->$xlevel . get_string('abbreviate_score', 'block_game');
                }
            }

            $scorefull = 0;
            $scoreatv = '-';
            $scoremod = '-';
            $scoresection = '-';
            $scorebonus = '-';
            $scorbadge = '-';
            if ($showscore) {
                $scoreatv = $gameuser->score_activities . get_string('abbreviate_score', 'block_game');
                $scoremod = $gameuser->score_module_completed . get_string('abbreviate_score', 'block_game');
                $scoresection = $gameuser->score_section . get_string('abbreviate_score', 'block_game');
                $scorebonus = $gameuser->score_bonus_day . get_string('abbreviate_score', 'block_game');
                $scorbadge = $gameuser->score_badges . get_string('abbreviate_score', 'block_game');
                $scorefull = ($gameuser->score + $gameuser->score_bonus_day + $gameuser->score_activities
                        + $gameuser->score_module_completed + $gameuser->score_section + $gameuser->score_badges);
            }
            if ($gameuser->courseid == SITEID) {
                $rank = '';
                $fullrank = '';
                $imglevel = '';
                $level = '';
                $nextlevel = '';
                $helpprogresslevel = '';
            }
            $courses[] = [
                'coursename' => $coursename,
                'scorefull' => $scorefull . get_string('abbreviate_score', 'block_game'),
                'rank' => $rank,
                'fullrank' => $fullrank,
                'imglevel' => $imglevel,
                'level' => $level,
                'nextlevel' => $nextlevel,
                'helpprogresslevel' => $helpprogresslevel,
                'percent' => $percent,
                'scoreatv' => $scoreatv,
                'scoremod' => $scoremod,
                'scoresection' => $scoresection,
                'scorebonus' => $scorebonus,
                'scorbadge' => $scorbadge,
                'awarded' => true
            ];
            $fullpointsall = ($fullpointsall + ($gameuser->score + $gameuser->score_bonus_day
                    + $gameuser->score_activities + $gameuser->score_badges
                    + $gameuser->score_module_completed + $gameuser->score_section));
            $fullpointsbonus += $gameuser->score_bonus_day;
            $fullpointsactivities += $gameuser->score_activities;
            $fullpointsmodulecompleted += $gameuser->score_module_completed;
            $fullpointssection += $gameuser->score_section;
            $fullpointsbadges += $gameuser->score_badges;
        }

        if ($showlevel) {
            $gameall = new \stdClass();
            $gameall->courseid = SITEID;
            $gameall->userid = $this->user->id;

            $gameall = block_game_load_game($gameall);
            $gameall->score_bonus_day = $fullpointsbonus;
            $gameall->score_activities = $fullpointsactivities;
            $gameall->score_module_completed = $fullpointsmodulecompleted;
            $gameall->score_section = $fullpointssection;
            $gameall->score_badges = $fullpointsbadges;

            $gameall = block_game_get_percente_level($gameall);

            $xlevel = 'level_up' . ($gameall->level + 1);
            $maxok = false;
            if ($gameall->level == $gameall->config->level_number) {
                $xlevel = 'level_up' . $gameall->level;
                $maxok = true;
            }

            $fs = get_file_storage();
            if ($fs->file_exists(1, 'block_game', 'imagens_levels', 0, '/', 'lv' . $gameall->level . '.svg')) {
                $imglv = block_game_pix_url(1, 'imagens_levels', 'lv' . $gameall->level);
            } else {
                $imglv = $CFG->wwwroot . '/blocks/game/pix/lv' . $gameall->level . '.svg';
            }
            $imglevel = '<img title="' . get_string('label_level', 'block_game');
            $imglevel .= '" src="' . $imglv . '" height="40" width="40"/>';
            $level = $gameall->level;

            // Progress Bar.
            $xlevel = 'level_up' . ($gameall->level + 1);
            $maxok = false;
            if ($gameall->level == $gameall->config->level_number) {
                $xlevel = 'level_up' . $gameall->level;
                $maxok = true;
            }
            $percent = round($gameall->percent, 1);
            if ($maxok) {
                $nextlevel = get_string('level_max_ok', 'block_game');
            } else {
                $nextlevel = get_string('next_level', 'block_game') . ' =>'
                        . $gameall->config->$xlevel . get_string('abbreviate_score', 'block_game');
            }
        }
        $rank = 0;
        $fullrank = 0;
        if ($showrank) {
            $rank = $gameall->ranking;
            $fullrank = block_game_get_players($gameall->courseid);
        }

        $courses[] = [
            'coursename' => '<strong>' . get_string('general', 'block_game') . '</strong>',
            'scorefull' => $fullpointsall . get_string('abbreviate_score', 'block_game'),
            'rank' => $rank,
            'fullrank' => $fullrank,
            'imglevel' => $imglevel,
            'level' => $level,
            'nextlevel' => $nextlevel,
            'helpprogresslevel' => $nextlevel,
            'percent' => $percent,
            'scoreatv' => '<strong>' . $fullpointsactivities . get_string('abbreviate_score', 'block_game') . '</strong>',
            'scoremod' => '<strong>' . $fullpointsmodulecompleted . get_string('abbreviate_score', 'block_game') . '</strong>',
            'scoresection' => '<strong>' . $fullpointssection . get_string('abbreviate_score', 'block_game') . '</strong>',
            'scorebonus' => '<strong>' . $fullpointsbonus . get_string('abbreviate_score', 'block_game') . '</strong>',
            'scorbadge' => '<strong>' . $fullpointsbadges . get_string('abbreviate_score', 'block_game') . '</strong>',
            'awarded' => true
        ];

        $labelbadges = get_string('label_badge', 'block_game');
        $outputbadges = '';
        $badgedisabled = '';
        if (!empty($CFG->enablebadges)) {
            $badges = (array) badges_get_user_badges($game->userid);
            foreach ($badges as $badge) {
                $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance()
                        : context_course::instance($badge->courseid);
                $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
                $url = new moodle_url('/badges/badge.php', array('hash' => $badge->uniquehash));
                $outputbadges .= '<img src="' . $imageurl . '"  height="80" width="80" class="badge-image">';
                $outputbadges .= '<span> <a href="' . $url . '">' . $badge->name . '</a> </span> ';
            }
        } else {
            $badgedisabled = get_string('badgesdisabled', 'badges');
        }

        return [
            'imgavatar' => $imgavatar,
            'username' => $username,
            'courselabel' => $courselabel,
            'scorelabelfull' => $scorelabelfull,
            'scoreatvlabel' => $scoreatvlabel,
            'scoremodlabel' => $scoremodlabel,
            'scoresectionlabel' => $scoresectionlabel,
            'scorebonuslabel' => $scorebonuslabel,
            'scorebadgelabel' => $scorebadgelabel,
            'ranklabel' => $ranklabel,
            'levellabel' => $levellabel,
            'nextlevellabel' => $nextlevellabel,
            'hascourses' => $hascourses,
            'courses' => $courses,
            'labelbadges' => $labelbadges,
            'outputbadges' => $outputbadges,
            'badgedisabled' => $badgedisabled
        ];
    }

}
