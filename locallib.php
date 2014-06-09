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
 * Defines classes for use in the metalink block
 *
 * @package    block_metalink
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/enrol/meta/locallib.php');

/**
 * Validates and processes files for the metalink block
 */
class block_metalink_handler {

    /**
     * The path of the cron file, or ID of the file uploaded through the form
     *
     * @var string
     */
    private $filename;

    /**
     * Constructor, sets the filename
     *
     * @param string $filename
     */
    public function __construct($filename) {
        $this->filename = $filename;
    }

    /**
     * Attempts to open the file
     *
     * Check if the filename is a path to a cron file. If so, open it normally.
     * If not, assume it's an uploaded file and open it using the File API.
     * Return the file handler.
     *
     * @throws metalink_exception if the file can't be opened for reading
     * @global object $USER
     * @return object File handler
     */
    public function open_file() {
        global $USER;
        if (is_file($this->filename)) {
            if (!$file = fopen($this->filename, 'r')) {
                throw new metalink_exception('cantreadcsv', '', 500);
            }
        } else {
            $fs = get_file_storage();
            $context = context_user::instance($USER->id);
            $files = $fs->get_area_files($context->id,
                                         'user',
                                         'draft',
                                         $this->filename,
                                         'id DESC',
                                         false);
            if (!$files) {
                throw new metalink_exception('cantreadcsv', '', 500);
            }
            $file = reset($files);
            if (!$file = $file->get_content_file_handle()) {
                throw new metalink_exception('cantreadcsv', '', 500);
            }
        }
        return $file;
    }

    /**
     * Checks that the file is valid CSV in the expected format
     *
     * Opens the file, then checks each row contains 3 comma-separated values
     *
     * @see open_file()
     * @throws metalink_exeption if there are the wrong number of columns
     * @return true on success
     */
    public function validate() {
        $line = 0;
        $file = $this->open_file();
        while ($csvrow = fgetcsv($file)) {
            $line++;
            if (count($csvrow) < 3) {
                throw new metalink_exception('toofewcols', $line, 415);
            }
            if (count($csvrow) > 3) {
                throw new metalink_exception('toomanycols', $line, 415);
            }
        }
        fclose($file);
        return true;
    }

    /**
     * Processes the file to assign a user to another user's context
     *
     * Opens the file, loops through each row. Cleans the values in each column,
     * checks that the operation is valid and the user's exist. If all is well,
     * assigns or removes the user in column 2 to/from the user is column 3's
     * context as specified.
     * Returns a report of successess and failures.
     *
     * @see open_file()
     * @uses enrol_meta_sync() Meta plugin function for syncing users
     * @global object $DB Database interface
     * @param bool $plaintext Return report as plain text, rather than HTML?
     * @return string A report of successes and failures.S
     */
    public function process($plaintext = false) {
        global $DB;
        // Get the block's configuration, so we know the ID of the role we're assigning.
        $cfg_metalink = get_config('block/metalink');
        $report = array();
        // Set the newline character.
        if ($plaintext) {
            $nl = "\n";
        } else {
            $nl = '<br />';
        }

        // Set a counter so we can report line numbers for errors.
        $line = 0;

        // Open the file.
        $file = $this->open_file();

        // Loop through each row of the file.
        while ($csvrow = fgetcsv($file)) {
            $line++;
            // Clean idnumbers to prevent sql injection.
            $op = clean_param($csvrow[0], PARAM_ALPHANUM);
            $parent_idnum = clean_param($csvrow[1], PARAM_TEXT);
            $child_idnum = clean_param($csvrow[2], PARAM_TEXT);
            $strings = new stdClass;
            $strings->line = $line;
            $strings->op = $op;

            // Need to check the line is valid. If not, add a message to the
            // report and skip the line.

            // Check we've got a valid operation.
            if (!in_array($op, array('add', 'del'))) {
                $report[] = get_string('invalidop', 'block_metalink', $strings);
                continue;
            }
            // Check the user we're assigning exists.
            if (!$parent = $DB->get_record('course', array('idnumber' => $parent_idnum))) {
                $report[] = get_string('parentnotfound', 'block_metalink', $strings);
                continue;
            }
            // Check the user we're assigning to exists.
            if (!$child = $DB->get_record('course', array('idnumber' => $child_idnum))) {
                $report[] = get_string('childnotfound', 'block_metalink', $strings);
                continue;
            }

            $strings->parent = $parent->shortname;
            $strings->child = $child->shortname;

            $enrol = enrol_get_plugin('meta');

            if ($op == 'del') {
                // If we're deleting, check the parent is already linked to the
                // child, and remove the link.  Skip the line if they're not.
                $instanceparams = array(
                    'courseid' => $parent->id,
                    'customint1' => $child->id,
                    'enrol' => 'meta'
                );
                if ($instance = $DB->get_record('enrol', $instanceparams)) {
                    $enrol->delete_instance($instance);
                    $report[] =  get_string('reldeleted', 'block_metalink', $strings);
                } else {
                    $report[] =  get_string('reldoesntexist', 'block_metalink', $strings);
                }
            } else {
                // If we're adding, check that the parent is not already linked
                // to the child, and add them. Skip the line if they are.
                $instanceparams1 = array(
                    'courseid' => $child->id,
                    'customint1' => $parent->id,
                    'enrol' => 'meta'
                );
                $instanceparams2 = array(
                    'courseid' => $parent->id,
                    'customint1' => $child->id,
                    'enrol' => 'meta'
                );
                if ($instance = $DB->get_record('enrol', $instanceparams1)) {
                    $report[] = get_string('childisparent', 'block_metalink', $strings);
                } else if ($instance = $DB->get_record('enrol', $instanceparams2)) {
                    $report[] = get_string('relalreadyexists', 'block_metalink', $strings);
                } else if ($enrol->add_instance($parent, array('customint1' => $child->id))) {
                    enrol_meta_sync($parent->id);
                    $report[] = get_string('reladded', 'block_metalink', $strings);
                } else {
                    $report[] = get_string('reladderror', 'block_metalink', $strings);
                }
            }
        }
        fclose($file);
        return implode($nl, $report);
    }
}

/**
 * An exception for reporting errors when processing metalink files
 *
 * Extends the moodle_exception with an http property, to store an HTTP error
 * code for responding to AJAX requests.
 */
class metalink_exception extends moodle_exception {

    /**
     * Stores an HTTP error code
     *
     * @var int
     */
    public $http;

    /**
     * Constructor, creates the exeption from a string identifier, string
     * parameter and HTTP error code.
     *
     * @param string $errorcode
     * @param string $a
     * @param int $http
     */
    public function __construct($errorcode, $a = null, $http = 200) {
        parent::__construct($errorcode, 'block_metalink', '', $a);
        $this->http = $http;
    }
}
