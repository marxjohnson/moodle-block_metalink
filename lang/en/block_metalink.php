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
 * Defines the Lang strings for the Metacourse Link block
 *
 * @package    block_metalink
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['blocksettings'] = 'Block Settings';
$string['cantremoveold'] = 'Old cron file $a couldn\'t be removed. Please check file permissions.';
$string['cantreadcsv']  = 'Unable to read CSV file';
$string['childisparent'] = '{$a->child} is a parent of {$a->parent}, so cannot be added as its child.';
$string['csvfile'] = 'Select CSV file';
$string['csv'] = 'CSV File';
$string['csv_help'] = 'The file should be in CSV (comma-seperated value) format. Each assignment should
    be on 1 row, with 3 columns: operation (add or del), parent course idnumber, child course idnumber).
    For example, to add child course with idnumber 1234 to parent course with idnumber 4321, the line would
    read: add, 4321, 1234';
$string['cronfile'] = 'Location of file for automatic processing';
$string['cronfiledesc'] = 'If you enter a file location in here, it will be periodically
    checked for a file to process automatically.';
$string['cronprocessed'] = 'Processed file location';
$string['cronmoved'] = '{$a->old} moved to {$a->new}';
$string['cronnotmoved'] = '{$a->old} couldn\'t be moved to {$a->new}. Please check folder permissions.';
$string['invalidop'] = 'Line {$a->line}: Invalid operation {$a->op}';
$string['keepprocessed'] = 'Keep Processed files';
$string['keepprocessedlong'] = 'If checked, processed files will be stored in the location below.';
$string['keepprocessedfor'] = 'Days to keep processed files for';
$string['metadisabled'] = 'The Metacourse enrolment plugin is disabled. Enable it to use this block.';
$string['musthavefile'] = 'You must select a file';
$string['nocronfile'] = 'Cron file doesn\'t exist.';
$string['nodir'] = '{$a} does not exist or is not writable. Please check folder permissions.';
$string['nopermission'] = 'You do not have permission to upload tutor relationships.';
$string['pluginname'] = 'Upload Metacourse links';
$string['pluginnameplural'] = 'Upload Metacourse links';
$string['reldoesntexist'] = '{$a->child} not linked to {$a->parent}, so can\'t be removed';
$string['reladded'] = '{$a->child} sucessfully linked to {$a->parent}';
$string['relalreadyexists'] = '{$a->child} already linked to {$a->parent}';
$string['reladderror'] = 'Error linking {$a->child} to {$a->parent}';
$string['reldeleted'] = '{$a->child} unlinked from {$a->parent}';
$string['removedold'] = 'Removed {$a} old cron files';
$string['toofewcols'] = 'CSV file has too few columns on line {$a}, expecting 3.';
$string['toomanycols'] = 'CSV file has too many columns on line {$a}, expecting 3.';
$string['parentnotfound'] = 'Line {$a->line}: Parent Course not found';
$string['childnotfound'] = 'Line {$a->line}: Child Course not found';
$string['metalink_log'] = 'Flatfile MetaLink Log';
$string['metalink:use'] = 'Use MetaLink block';
$string['metalink:addinstance'] = 'Add a new MetaLink block';
$string['metalink:myaddinstance'] = 'Add a new MetaLink block to My Home Page';
