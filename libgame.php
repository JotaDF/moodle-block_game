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
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__) . '/../../config.php');
require_login();

// Load game user of de block.
/**
 * Return the game user
 *
 * @param stdClass $game
 * @return mixed
 */
function load_game($game) {
    global $DB;
    if (!empty($game->userid) && !empty($game->courseid)) {
        $sql = 'SELECT count(*) as total  FROM {block_game} WHERE courseid=? AND userid=?';
        $busca = $DB->get_record_sql($sql, array($game->courseid, $game->userid));
        if ($busca->total > 0) {
            $gamedb = $DB->get_record('block_game', array('courseid' => $game->courseid, 'userid' => $game->userid));
            return $gamedb;
        } else {
            $newgame = new stdClass();
            $newgame->courseid = $game->courseid;
            $newgame->userid = $game->userid;
            $newgame->avatar = get_avatar_user($game->userid);
            $newgame->score = 0;
            $newgame->score_activities = 0;
            $newgame->score_badges = 0;
            $newgame->level = 0;
            $newgame->rank = 0;
            $newgame->achievements = "";
            $newgame->rewards = "";
            $newgame->phases = "";
            $newgame->badges = "";
            $newgame->frame = "";
            $newgame->bonus_day = null;
            $lastinsertid = $DB->insert_record('block_game', $newgame);

            $newgame->id = $lastinsertid;

            return $newgame;
        }
    }
    return false;
}

// Get games user.
/**
 * Return the games user
 *
 * @param int $userid
 * @return mixed
 */
function get_games_user($userid) {
    global $DB;
    if (!empty($userid)) {
        $games = $DB->get_records_sql('SELECT * FROM {block_game} WHERE userid=? ORDER BY courseid DESC', array($userid));
        return $games;
    }
    return false;
}

// Update game user.
/**
 * Return update game user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_game($game) {
    global $DB;

    if (!empty($game->id) && !empty($game->userid) && !empty($game->courseid)) {
        if (empty($game->avatar)) {
            $game->avatar = 0;
        }
        if (empty($game->score)) {
            $game->score = 0;
        }
        if (empty($game->level)) {
            $game->level = 0;
        }
        if (empty($game->rank)) {
            $game->rank = 0;
        }
        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->courseid = $game->courseid;
        $savegame->userid = $game->userid;
        $savegame->avatar = $game->avatar;
        $savegame->score = $game->score;
        $savegame->level = $game->level;
        $savegame->rank = $game->rank;
        $savegame->achievements = $game->achievements;
        $savegame->rewards = $game->rewards;
        $savegame->phases = $game->phases;
        $savegame->badges = $game->badges;
        $savegame->frame = $game->frame;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

// Update avatar user.
/**
 * Return update avatar user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_avatar_game($game) {
    global $DB;

    if (!empty($game->userid) && !empty($game->avatar)) {

        $DB->execute("UPDATE {block_game} SET avatar=? WHERE userid=?", array($game->avatar, $game->userid));

        return true;
    }
    return false;
}

// Update score game.
/**
 * Return update score user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_score_game($game) {
    global $DB;

    if (!empty($game->id) && !empty($game->score)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->score = $game->score;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

// Update level game.
/**
 * Return update level user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_level_game($game) {
    global $DB;

    if (!empty($game->id) && !empty($game->level)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->level = $game->level;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

// Update achievements game.
/**
 * Return update achievements user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_achievements_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->achievements = $game->achievements;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

// Update rewards game.
/**
 * Return update rewards user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_rewards_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->rewards = $game->rewards;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

// Update phases game.
/**
 * Return update phases user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_phases_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->phases = $game->phases;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

// Update badges game.
/**
 * Return update badge user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_badges_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->badges = $game->badges;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

// Update frame game.
/**
 * Return update frame user
 *
 * @param stdClass $game
 * @return boolean
 */
function update_frame_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->frame = $game->frame;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

// Update bonus of the day game.
/**
 * Return update bonus of day user
 *
 * @param stdClass $game
 * @param int $bonus
 * @return boolean
 */
function bonus_of_day($game, $bonus) {
    global $DB, $CFG;
    if (!empty($game->id)) {
        $sql = 'SELECT CURRENT_DATE as hoje, bonus_day  FROM {block_game} WHERE courseid=? AND userid=?';
        $busca = $DB->get_record_sql($sql, array($game->courseid, $game->userid));
        if ($busca->bonus_day == null || $busca->bonus_day < $busca->hoje) {
            $game->score = ((int) $game->score + (int) $bonus);
            $game->bonus_day = $busca->hoje;
            $DB->update_record('block_game', $game);
        }
        return ((int) $game->score + (int) $bonus);
    }
    return false;
}

// Score activity notes.
/**
 * Return update score activities user
 *
 * @param stdClass $game
 * @return boolean
 */
function score_activities($game) {
    global $DB, $CFG;

    if (!empty($game->id)) {
        $sql = "SELECT SUM(COALESCE(g.finalgrade,0)) as score_activities"
                . " FROM {grade_grades} g INNER JOIN {grade_items} i ON g.itemid=i.id"
                . " WHERE i.courseid=? AND i.itemtype='mod' AND g.userid=?";
        $busca = $DB->get_record_sql($sql, array($game->courseid, $game->userid));

        if ($busca->score_activities == "" || empty($busca->score_activities)) {
            $game->score_activities = 0;
        } else {
            $game->score_activities = (int) $busca->score_activities;
        }
        $DB->execute("UPDATE {block_game} SET score_activities=? WHERE id=?", array((int) $game->score_activities, $game->id));

        return true;
    }
    return false;
}

// No score not activity notes.
/**
 * Return update game user
 *
 * @param stdClass $game
 * @return boolean
 */
function no_score_activities($game) {
    global $DB;
    if (!empty($game->id)) {
        $DB->execute("UPDATE {block_game} SET score_activities=0 WHERE id=?", array($game->id));
        return true;
    }
    return false;
}

// Score badges of course completed.
/**
 * Return update game user
 *
 * @param stdClass $game
 * @param int $value
 * @return mixed
 */
function score_badge($game, $value) {
    global $DB, $CFG;

    $badges = array();
    if (!empty($game->userid)) {
        $sql = "SELECT cc.userid, cc.course, COALESCE(cc.timecompleted, 0) timecompleted
                FROM {course_completions} cc
                WHERE cc.userid = ?
                AND timecompleted<>0";

        $rs = $DB->get_records_sql($sql, array($game->userid));
        $nbadges = 0;
        foreach ($rs as $c) {
            $badges[$nbadges] = $c->course;
            $nbadges++;
        }

        $game->badges = "" . implode(",", $badges) . "";
        $game->score_badges = ($nbadges * $value);
        $sql = 'UPDATE {block_game} SET badges=?,score_badges=? WHERE userid=? AND courseid=?';
        $DB->execute($sql, array($game->badges, (int) $game->score_badges, $game->userid, 1));

        return $game;
    }
    return $game;
}

// Ranking user.
/**
 * Return update ranking game
 *
 * @param stdClass $game
 * @return mixed
 */
function ranking($game) {
    global $DB, $CFG;

    if (!empty($game->id)) {
        if ($game->courseid == 1) {

            $sql = 'SELECT g.userid, u.firstname,SUM(g.score) sum_score,'
                    . ' SUM(COALESCE(g.score_activities, 0)) sum_score_activities,'
                    . ' SUM(COALESCE(g.score_badges, 0)) sum_score_badges,'
                    . ' (SUM(score)+SUM(COALESCE(score_activities, 0))+SUM(COALESCE(score_badges, 0))) pt'
                    . ' FROM {block_game} g, {user} u'
                    . ' WHERE u.id=g.userid GROUP BY g.userid, u.firstname'
                    . ' ORDER BY pt DESC,sum_score_badges DESC,sum_score_activities DESC,sum_score DESC, g.userid ASC';

            $ranking = $DB->get_records_sql($sql);
            $poisicao = 1;
            foreach ($ranking as $rs) {
                if ($rs->userid == $game->userid) {
                    $game->rank = $poisicao;
                    $game->score = $rs->sum_score;
                    $game->score_activities = $rs->sum_score_activities;
                    break;
                }
                $poisicao++;
            }
        } else {
            $sql = 'SELECT g.userid, u.firstname,SUM(g.score) sum_score,'
                    . ' SUM(COALESCE(g.score_activities, 0)) sum_score_activities,'
                    . ' (SUM(score)+SUM(COALESCE(score_activities, 0))) pt'
                    . ' FROM {block_game} g, {user} u'
                    . ' WHERE u.id=g.userid AND courseid=?'
                    . ' GROUP BY g.userid, u.firstname'
                    . ' ORDER BY pt DESC, sum_score_activities DESC,sum_score DESC, g.userid ASC';

            $ranking = $DB->get_records_sql($sql, array($game->courseid));
            $poisicao = 1;
            foreach ($ranking as $rs) {
                if ($rs->userid == $game->userid) {
                    $game->rank = $poisicao;
                    break;
                }
                $poisicao++;
            }
        }
        $DB->execute("UPDATE {block_game} SET rank=? WHERE id=?", array($game->rank, $game->id));
    }
    return $game;
}

// Seting new level.
/**
 * Return set level user
 *
 * @param stdClass $game
 * @param int $levelup
 * @param int $levelnumber
 * @return stdClass $game
 */
function set_level($game, $levelup, $levelnumber) {
    global $DB, $CFG;

    if (!empty($game->id)) {
        $pt = $game->score + $game->score_activities;
        if (sets_level($pt, $levelup) >= $levelnumber) {
            $level = $levelnumber;
        } else {
            $level = sets_level($pt, $levelup);
        }
        $game->level = $level;
    }
    $DB->execute("UPDATE {block_game} SET level=? WHERE id=?", array($game->level, $game->id));
    return $game;
}

// Seting level.
/**
 * Return set level user
 *
 * @param int $scorefull
 * @param array $levelup
 * @return int
 */
function sets_level($scorefull, $levelup) {
    $level = 0;
    foreach ($levelup as $levelvalue) {
        if ($scorefull >= $levelvalue) {
            $level++;
        }
    }
    return $level;
}

/**
 * Return number of players of course.
 *
 * @param int $courseid
 * @return int
 */
function get_players($courseid) {
    global $DB;
    if (!empty($courseid)) {
        if ($courseid == 1) {
            $sql = 'SELECT count(*) as total FROM {user} '
                    . 'WHERE confirmed=1 AND deleted=0 AND suspended=0 AND id > 0';
            $busca = $DB->get_record_sql($sql);
            return $busca->total;
        } else {
            $sql = 'SELECT count(*) as total FROM {role_assignments} rs,'
                    . ' {user} u, {context} e WHERE u.id=rs.userid AND rs.contextid=e.id '
                    . 'AND e.contextlevel=50 AND e.instanceid=?';
            $busca = $DB->get_record_sql($sql, array($courseid));
            return $busca->total;
        }
    }
    return false;
}

/**
 * Return number not players of course.
 *
 * @param int $courseid
 * @return int
 */
function get_no_players($courseid) {
    global $DB;
    if (!empty($courseid)) {
        if ($courseid == 1) {
            $sql = 'SELECT count(*) as total FROM {user} '
                    . 'WHERE confirmed=1 AND deleted=0 AND suspended=0 AND id > 0 '
                    . 'AND id NOT IN(SELECT userid FROM {block_game})';
            $busca = $DB->get_record_sql($sql);
            return $busca->total;
        } else {
            $sql = 'SELECT count(*) as total FROM {role_assignments} rs, {user} u, {context} e '
                    . 'WHERE u.id=rs.userid AND rs.contextid=e.id AND e.contextlevel=50 AND e.instanceid=?'
                    . ' AND u.id NOT IN(SELECT userid FROM {block_game})';
            $busca = $DB->get_record_sql($sql, array($courseid));
            return $busca->total;
        }
    }
    return false;
}

/**
 * Return ranking list.
 *
 * @param int $courseid
 * @return mixed
 */
function rank_list($courseid) {
    global $DB, $CFG;

    if (!empty($courseid)) {
        if ($courseid == 1) {
            $sql = 'SELECT g.userid, u.firstname, u.lastname ,SUM(g.score) sum_score,'
                    . ' SUM(COALESCE(g.score_activities, 0)) sum_score_activities,'
                    . ' SUM(COALESCE(g.score_badges, 0)) sum_score_badges,'
                    . ' (SUM(score)+SUM(COALESCE(score_activities, 0))+SUM(COALESCE(score_badges, 0))) pt'
                    . ' FROM {block_game} g, {user} u WHERE u.id=g.userid GROUP BY g.userid, u.firstname '
                    . 'ORDER BY pt DESC,sum_score_badges DESC,sum_score_activities DESC,sum_score DESC, g.userid ASC';

            $ranking = $DB->get_records_sql($sql);
            return $ranking;
        } else {
            $sql = 'SELECT g.userid, u.firstname, u.lastname, g.avatar,SUM(g.score) sum_score,'
                    . ' SUM(COALESCE(g.score_activities, 0)) sum_score_activities,'
                    . ' (SUM(score)+SUM(COALESCE(score_activities, 0))) pt'
                    . ' FROM {block_game} g, {user} u WHERE u.id=g.userid AND courseid=?'
                    . ' GROUP BY g.userid, u.firstname, g.avatar'
                    . ' ORDER BY pt DESC, sum_score_activities DESC,sum_score DESC, g.userid ASC';

            $ranking = $DB->get_records_sql($sql, array($courseid));
            return $ranking;
        }
    }
    return false;
}

/**
 * Get the avatar the user.
 *
 * @param int $userid
 * @return int the total time spent in seconds
 */
function get_avatar_user($userid) {
    global $DB;
    if (!empty($userid)) {
        $sql = 'SELECT MAX(avatar) avatar FROM {block_game} '
                . 'WHERE userid=' . $userid . ' AND avatar > 0';
        $busca = $DB->get_record_sql($sql);
        if (isset($busca->avatar)) {
            return $busca->avatar;
        } else {
            return 0;
        }
    }
    return 0;
}

/**
 * Get the time the user has spent in the course.
 *
 * @param int $courseid
 * @param int $userid
 * @return int the total time spent in seconds
 */
function get_course_time($courseid, $userid = 0) {
    global $CFG, $DB, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $logmanager = get_log_manager();
    $readers = $logmanager->get_readers();
    $enabledreaders = get_config('tool_log', 'enabled_stores');
    if (empty($enabledreaders)) {
        return 0;
    }
    $enabledreaders = explode(',', $enabledreaders);

    // Go through all the readers until we find one that we can use.
    foreach ($enabledreaders as $enabledreader) {
        $reader = $readers[$enabledreader];
        if ($reader instanceof \logstore_legacy\log\store) {
            $logtable = 'log';
            $coursefield = 'course';
            $timefield = 'time';
            break;
        } else if ($reader instanceof \core\log\sql_internal_table_reader) {
            $logtable = $reader->get_internal_log_table_name();
            $coursefield = 'courseid';
            $timefield = 'timecreated';
            break;
        }
    }

    // If we didn't find a reader then return 0.
    if (!isset($logtable)) {
        return 0;
    }

    $sql = "SELECT id, $timefield
                  FROM {{$logtable}}
                 WHERE userid = :userid
                   AND $coursefield = :courseid
              ORDER BY $timefield ASC";
    $params = array('userid' => $userid, 'courseid' => $courseid);
    $totaltime = 0;
    if ($logs = $DB->get_recordset_sql($sql, $params)) {
        foreach ($logs as $log) {
            if (!isset($login)) {
                // For the first time $login is not set so the first log is also the first login.
                $login = $log->$timefield;
                $lasthit = $log->$timefield;
                $totaltime = 0;
            }
            $delay = $log->$timefield - $lasthit;
            if ($delay > ($CFG->sessiontimeout * 60)) {
                // The difference between the last log and the current log is more than
                // the timeout Register session value so that we have found a session!
                $login = $log->$timefield;
            } else {
                $totaltime += $delay;
            }
            // Now the actual log became the previous log for the next cycle.
            $lasthit = $log->$timefield;
        }

        return $totaltime;
    }

    return 0;
}

/**
 * Get the time the user registration in the course.
 *
 * @param int $courseid
 * @param int $userid
 * @return int timestamp
 */
function get_registration_course_time($courseid, $userid = 0) {
    global $CFG, $DB, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $sql = "SELECT ue.timestart total FROM {enrol} e INNER JOIN {user_enrolments} ue ON e.id=ue.enrolid "
            . "INNER JOIN {user} u ON u.id=ue.userid WHERE u.id=? AND e.courseid=?";
    $params = array('userid' => $userid, 'courseid' => $courseid);
    $busca = $DB->get_record_sql($sql, $params);
    $datatimeenrol = $busca->total;

    $time = new DateTime("now", core_date::get_user_timezone_object());
    $timestamp = $time->getTimestamp();

    $timeenrol = new DateTime();
    $timeenrol->setTimestamp($datatimeenrol);

    $totaltime = 0;
    if ($datatimeenrol > 0) {
        $diff = $timeenrol->diff($time);
        $horas = $diff->h + ($diff->days * 24);
        $totaltime = $horas;
    }

    return $totaltime;
}
