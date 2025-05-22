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
 * show files that are present in one plugin and are not present in another
 *
 * @author Marcelo Augusto Rauh Schmit
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');

global $CFG;

$filesindividualfeedback = listAllFiles($CFG->dirroot.'/mod/individualfeedback/feedback_orig/');
$filesindividualfeedback = str_replace($CFG->dirroot.'/mod/individualfeedback/feedback_orig/', '', $filesindividualfeedback);
$filesfeedback = listAllFiles($CFG->dirroot.'/mod/feedback/');
$filesfeedback = str_replace($CFG->dirroot.'/mod/feedback/', '', $filesfeedback);

foreach($filesindividualfeedback AS $filename) {
    if(!is_dir($CFG->dirroot.'/mod/individualfeedback/feedback_orig/' . $filename)) {
        $md5file1 =  md5_file($CFG->dirroot.'/mod/individualfeedback/feedback_orig/' . $filename);
        $md5file2 =  md5_file($CFG->dirroot.'/mod/individualfeedback/' . $filename);
        if($md5file1 != $md5file2) {
            echo "*** File $filename is different from original plugin.\n";
        }
    }
}



function listAllFiles($dir) {
    global $CFG;

    $array = array_diff(scandir($dir), array('.', '..'));

    foreach ($array as &$item) {
        $item = $dir . $item;
    }
    unset($item);
    foreach ($array as $item) {
        if (is_dir($item)
            && $item != $CFG->dirroot.'/mod/individualfeedback/feedback_orig'
            && $item != $CFG->dirroot.'/mod/individualfeedback/cli'
            && $item != $CFG->dirroot.'/mod/individualfeedback/.git'
            && $item != $CFG->dirroot.'/mod/individualfeedback/.idea') {
            $array = array_merge($array, listAllFiles($item . DIRECTORY_SEPARATOR));
        }
    }
    return $array;
}
