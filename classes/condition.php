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

namespace availability_enrol_dates;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for enrol dates condition.
 * Restriction by Enrol Dates (timestart/timeend) or Course Dates (timestart/timeend) condition
 *
 * Documentation: {@link https://moodledev.io/docs/apis/plugintypes/availability}
 *
 * @package    availability_enrol_dates
 * @copyright  2026 LeoDG <callme@leodg.dev>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

        // Direction options
    const DIRECTION_BEFORE = 'before';
    const DIRECTION_AFTER = 'after';

    // Time check options (what date to compare against)
    const TIME_COURSE_START = 'coursetimestart';
    const TIME_COURSE_END = 'coursetimeend';
    const TIME_ENROL_START = 'enroltimestart';
    const TIME_ENROL_END = 'enroltimeend';

    // Period options (how much time to add/subtract)
    const PERIOD_HOURS = 'hours';
    const PERIOD_DAYS = 'days';
    const PERIOD_MONTHS = 'months';


    /** @var string Direction of the condition (before/after) */
    protected $direction;

    /** @var string Time value to check (Course TimeStart, CourseTimeEnd, Enrol TimeStart, Enrol TimeEnd) */
    protected $timevaluecheck;

    /** @var int Time number for comparison */
    protected $timenumber;

    /** @var string Time period (Hours, Days, Months) */
    protected $timeperiod;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \core\exception\coding_exception If invalid data structure.
     */
    public function __construct($structure) {

        // Get direction.
        if (isset($structure->direction) && in_array($structure->direction,
                array(self::DIRECTION_BEFORE, self::DIRECTION_AFTER))) {
            $this->direction = $structure->direction;
        } else {
            throw new \core\exception\coding_exception('Missing or invalid ->direction for date condition');
        }

        // Get time value check.
        if (isset($structure->timevaluecheck) && in_array($structure->timevaluecheck,
                array(self::TIME_COURSE_START, self::TIME_COURSE_END, self::TIME_ENROL_START, self::TIME_ENROL_END))) {
            $this->timevaluecheck = $structure->timevaluecheck;
        } else {
            throw new \core\exception\coding_exception('Missing or invalid ->timevaluecheck for date condition');
        }

        // Get time number.
        if (isset($structure->timenumber) && is_numeric($structure->timenumber)) {
            $this->timenumber = (int)$structure->timenumber;
        } else {
            throw new \core\exception\coding_exception('Missing or invalid ->timenumber for date condition');
        }

        // Get time period.
        if (isset($structure->timeperiod) && in_array($structure->timeperiod,
                array(self::PERIOD_HOURS, self::PERIOD_DAYS, self::PERIOD_MONTHS))) {
            $this->timeperiod = $structure->timeperiod;
        } else {
            throw new \core\exception\coding_exception('Missing or invalid ->timeperiod for date condition');
        }
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return \stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        return (object)array(
            'type' => 'enrol_dates',
            'direction' => $this->direction,
            'timevaluecheck' => $this->timevaluecheck,
            'timenumber' => $this->timenumber,
            'timeperiod' => $this->timeperiod
        );
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param string $direction DIRECTION_xx constant
     * @param int $timenumber Number of time units
     * @param string $timevaluecheck Time value to check
     * @param string $timeperiod Time period
     * @return \stdClass Object representing condition
     */
    public static function get_json($direction, $timevaluecheck, $timenumber, $timeperiod) {
        return (object)array('type' => 'enrol_dates', 'direction' => $direction, 'timevaluecheck' => $timevaluecheck, 'timenumber' => (int)$timenumber, 'timeperiod' => $timeperiod);
    }

/**
     * Check if user meets condition.
     *
     * @param int $userid User ID
     * @param bool $not Set true if we are inverting the condition
     * @param bool $ajax Set true if we are in AJAX context
     * @param stdClass $course Course object
     * @return bool Whether user meets condition
     */
    public function is_available($userid, $not = false, $ajax = false, $course = null) {
        global $DB, $USER;

        // Get the user enrolment information

        $enrol = $DB->get_record('enrol', array('courseid' => $course, 'enrol' => 'manual'), '*', MUST_EXIST);
        $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $enrol->id, 'userid' => $userid), '', 'id, timestart, timeend');

        if (empty($enrolments)) {
            return false;
        }

        // Get the first enrolment (assuming single enrolment)
        $enrolment = reset($enrolments);

        // Get course information
        $courseinfo = $DB->get_record('course', array('id' => $course), 'timestart, timeend', MUST_EXIST);

        // Calculate the check time based on the selected time value
        $checktime = 0;
        switch ($this->timevaluecheck) {
            case self::TIME_COURSE_START:
                $checktime = $courseinfo->timestart;
                break;
            case self::TIME_COURSE_END:
                $checktime = $courseinfo->timeend;
                break;
            case self::TIME_ENROL_START:
                $checktime = $enrolment->timestart;
                break;
            case self::TIME_ENROL_END:
                $checktime = $enrolment->timeend;
                break;
        }

        // Calculate the comparison time
        $comparisontime = $this->calculate_time($checktime, $this->timenumber, $this->timeperiod, $this->direction);

        // Check if current time is within the condition
        $currenttime = time();

        $available = false;
        if ($this->direction === self::DIRECTION_BEFORE) {
            // Allow access before the calculated time
            $available = $currenttime <= $comparisontime;
        } else {
            // Allow access after the calculated time
            $available = $currenttime >= $comparisontime;
        }

        return $not ? !$available : $available;
    }

    /**
     * Calculate time based on direction, number, and period.
     *
     * @param int $base Base time
     * @param int $number Time number
     * @param string $period Time period (hours, days, months)
     * @param string $direction Direction (before, after)
     * @return int Calculated time
     */
    private function calculate_time($base, $number, $period, $direction) {
        $multiplier = $direction === self::DIRECTION_BEFORE ? -1 : 1;

        switch ($period) {
            case self::PERIOD_HOURS:
                return $base + ($multiplier * $number * 3600);
            case self::PERIOD_DAYS:
                return $base + ($multiplier * $number * 86400);
            case self::PERIOD_MONTHS:
                $year = date('Y', $base);
                $month = date('m', $base);
                $day = date('d', $base);

                // Adjust month and year
                $month += $multiplier * $number;
                while ($month < 1) {
                    $month += 12;
                    $year--;
                }
                while ($month > 12) {
                    $month -= 12;
                    $year++;
                }

                // Handle day overflow
                $lastday = date('t', mktime(0, 0, 0, $month, 1, $year));
                $day = min($day, $lastday);

                return mktime(0, 0, 0, $month, $day, $year);
            default:
                return $base;
        }
    }


    /**
     * Get information about what this condition is.
     *
     * @param bool $not Set true if we are inverting the condition
     * @param \stdClass $course Course object
     * @param bool $urlshow Whether to include links in the description
     * @return array Information about condition
     */
    public function get_description($not = false, $course = null, $urlshow = true) {
        $timevalue = $this->timevaluecheck;
        $number = $this->timenumber;
        $period = $this->timeperiod;

        $direction = $this->direction === 'before' ? get_string('before', 'availability_enrol_dates') : get_string('after', 'availability_enrol_dates');

        // Map the time value check to readable string
        $timevaluestring = '';
        switch ($timevalue) {
            case 'coursetimestart':
                $timevaluestring = get_string('coursetimestart', 'availability_enrol_dates');
                break;
            case 'coursetimeend':
                $timevaluestring = get_string('coursetimeend', 'availability_enrol_dates');
                break;
            case 'enroltimestart':
                $timevaluestring = get_string('enroltimestart', 'availability_enrol_dates');
                break;
            case 'enroltimeend':
                $timevaluestring = get_string('enroltimeend', 'availability_enrol_dates');
                break;
        }

        // Map the period to readable string
        $periodstring = '';
        switch ($period) {
            case 'hours':
                $periodstring = get_string('hours', 'availability_enrol_dates');
                break;
            case 'days':
                $periodstring = get_string('days', 'availability_enrol_dates');
                break;
            case 'months':
                $periodstring = get_string('months', 'availability_enrol_dates');
                break;
        }

        // Create a more descriptive string
        $description = 'Access is ' . $direction . ' ' . $timevaluestring . ' ' . $number . ' ' . $periodstring;

        return $description;
    }

    /**
     * Get required fields.
     *
     * @return array List of required fields
     */
    public function get_required_fields() {
        return array('direction', 'timevaluecheck', 'timenumber', 'timeperiod');
    }

    /**
     * Get a list of all available time values.
     *
     * @return array List of time values
     */
    public static function get_time_value_options() {
        return array(
            'coursetimestart' => get_string('coursetimestart', 'availability_enrol_dates'),
            'coursetimeend' => get_string('coursetimeend', 'availability_enrol_dates'),
            'enroltimestart' => get_string('enroltimestart', 'availability_enrol_dates'),
            'enroltimeend' => get_string('enroltimeend', 'availability_enrol_dates')
        );
    }

    /**
     * Get a list of all available time periods.
     *
     * @return array List of time periods
     */
    public static function get_time_period_options() {
        return array(
            'hours' => get_string('hours', 'availability_enrol_dates'),
            'days' => get_string('days', 'availability_enrol_dates'),
            'months' => get_string('months', 'availability_enrol_dates')
        );
    }

    /**
     * Gets a string describing the condition for debugging.
     *
     * @return string
     */
    protected function get_debug_string() {
        return "Enrol dates condition: {$this->direction} {$this->timenumber} {$this->timeperiod} from {$this->timevaluecheck}";
    }
}
