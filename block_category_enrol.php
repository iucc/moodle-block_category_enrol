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
 * Block category_enrol is defined here.
 *
 * @package block_category_enrol
 * @author Nadav Kavalerchik <nadavkav@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

class block_category_enrol extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_category_enrol');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $DB, $COURSE, $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $roles = $DB->get_records('role');
        $userroles = array();
        foreach($roles as $r)
            $userroles[$r->id] = (!empty($r->name)) ? $r->name : $r->shortname;

        if (!empty($this->config->roleid_incourse) && !empty($this->config->roleid_atcategory)) {
            $a = new stdClass;
            $a->courserole = $userroles[$this->config->roleid_incourse];
            $a->categoryrole = $userroles[$this->config->roleid_atcategory];
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $this->content->text = get_string('roleautoassignmentteacher', 'block_category_enrol', $a);
            } else {
                $this->content->text = get_string('roleautoassignmentstudent', 'block_category_enrol', $a);
            }
            unset($a);
            // If user has proper roles... allow to create a course and link to the category.
            if (has_capability('moodle/course:create', context_coursecat::instance($COURSE->category))) {
                $a = new stdClass;
                $a->cancreatecourse = '<a href="'.$CFG->wwwroot.'/course/edit.php?category='.$COURSE->category.'">'.get_string("addnewcourse").'</a>';
                $coursecategory = $DB->get_record('course_categories', array('id' => $COURSE->category));
                $a->categorylink = '<a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$COURSE->category.'">'.$coursecategory->name.'</a>';
                $this->content->text .= get_string('cancreateacourse', 'block_category_enrol', $a);
            }
        } else {
            $text = get_string('setupblock', 'block_category_enrol');
            $this->content->text = $text;
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediatly after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_category_enrol');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return false;
    }

    public function instance_allow_config() {
        return true;
    }
    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array('all' => false, 'course' => true);
    }

}
