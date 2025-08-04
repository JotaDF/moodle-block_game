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
 * Course profile
 *
 * @package    block_game
 * @copyright  2025 José Wilson
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
 * Course profile renderable class.
 *
 * @package    block_game
 * @copyright  2025 José Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profilecourse implements renderable, templatable {
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
        global $CFG;
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
        $imgavatar = $output->user_picture($this->user, ['size' => 80, 'hspace' => 12]);
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

        $imgscore = '';
        $labelscore = '';
        $score = 0;
        if ($showscore) {
            $imgscore = '<img title="' . get_string('label_score', 'block_game') . '" src="';
            $imgscore .= $CFG->wwwroot . '/blocks/game/pix/score.svg" height="65" width="65"/>';
            $labelscore .= get_string('label_score', 'block_game');
            $score = $game->score;
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
            $fullrank = block_game_get_players($game->courseid);
        }
        $imglevel = '';
        $labellevel = '';
        $level = 0;
        $nextlevel = '';
        $helpprogresslevel = get_string('help_progress_level_text', 'block_game');
        $percent = 0;
        if ($showlevel && isset($game->config->show_level)) {
            $game = block_game_get_percente_level($game);
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
                $nextlevel = get_string('level_max_ok', 'block_game');
            } else {
                $nextlevel = get_string('next_level', 'block_game') . ' =>'
                        . $game->config->$xlevel . get_string('abbreviate_score', 'block_game');
            }
        }

        $scorelabeldetail = get_string('score_detail', 'block_game');
        $scoreatvlabel = get_string('score_atv', 'block_game');
        $scoreatv = $game->score_activities . get_string('abbreviate_score', 'block_game');
        $scoremodlabel = get_string('score_mod', 'block_game');
        $scoremod = $game->score_module_completed . get_string('abbreviate_score', 'block_game');
        $scoresectionlabel = get_string('score_section', 'block_game');
        $scoresection = $game->score_section . get_string('abbreviate_score', 'block_game');
        $scorebonuslabel = get_string('score_bonus_day', 'block_game');
        $scorebonus = $game->score_bonus_day . get_string('abbreviate_score', 'block_game');
        $scorebadgelabel = get_string('label_badge', 'block_game');
        $scorbadge = $game->score_badges . get_string('abbreviate_score', 'block_game');
        $scorefull = ($game->score + $game->score_bonus_day + $game->score_activities
                        + $game->score_module_completed + $game->score_section + $game->score_badges);

        $labelbadges = get_string('label_badge', 'block_game');
        $outputbadges = '';
        $badgedisabled = '';
        if (!empty($CFG->enablebadges)) {
            $badges = (array) badges_get_user_badges($game->userid, $game->courseid, null, null, null, true);
            foreach ($badges as $badge) {
                $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance()
                        : context_course::instance($badge->courseid);
                $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
                $url = new moodle_url('/badges/badge.php', ['hash' => $badge->uniquehash]);
                $outputbadges .= '<img src="' . $imageurl . '"  height="50" width="50" class="badge-image">';
                $outputbadges .= '<span> <a href="' . $url . '">' . $badge->name . '</a> </span> ';
            }
        } else {
            $badgedisabled = get_string('badgesdisabled', 'badges');
        }

        return [
            'coursename' => $coursename,
            'imgavatar' => $imgavatar,
            'username' => $username,
            'imgscore' => $imgscore,
            'labelscore' => $labelscore,
            'score' => $score,
            'scorefull' => $scorefull . get_string('abbreviate_score', 'block_game'),
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
            'scorelabeldetail' => $scorelabeldetail,
            'scoreatvlabel' => $scoreatvlabel,
            'scoreatv' => $scoreatv,
            'scoremodlabel' => $scoremodlabel,
            'scoremod' => $scoremod,
            'scoresectionlabel' => $scoresectionlabel,
            'scoresection' => $scoresection,
            'scorebonuslabel' => $scorebonuslabel,
            'scorebonus' => $scorebonus,
            'scorebadgelabel' => $scorebadgelabel,
            'scorbadge' => $scorbadge,
            'labelbadges' => $labelbadges,
            'outputbadges' => $outputbadges,
            'badgedisabled' => $badgedisabled,
        ];
    }
}
