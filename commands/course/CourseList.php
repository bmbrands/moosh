<?php
/**
 * moosh - Moodle Shell
 *
 * @copyright  2012 onwards Tomasz Muras
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class CourseList extends MooshCommand
{
    public function __construct()
    {
        parent::__construct('list', 'course');

        $this->addArgument('search');

        $this->maxArguments = 255;
    }

    public function execute()
    {
        global $CFG, $DB;

        require_once $CFG->dirroot . '/course/lib.php';

        foreach ($this->arguments as $argument) {
            $this->expandOptionsManually(array($argument));
            $options = $this->expandedOptions;

            $search = mysql_real_escape_string($argument);
            $sql = "SELECT id,category,shortname,fullname FROM {course} WHERE shortname LIKE '%$search%' OR fullname LIKE '%$search%'";
            $courses = $DB->get_records_sql($sql);

            $outputheader = $outputcontent = "";
            $doheader = 0;
            foreach ($courses as $course) {
                foreach ($course as $field => $value ) {
                    if ($doheader == 0) {
                        $outputheader .= str_pad ($field, 15);
                    }
                    if ($field == "category" && $value > 0 ) {
                        $value = $this->get_parent($value);
                    } elseif($field == "parent") {
                        $value = "Top";
                    }

                    $outputcontent.= str_pad ($value, 15);
                }
                $outputcontent .= "\n";
                $doheader++;
            }
            echo $outputheader . "\n";
            echo $outputcontent;

        }
    }
    private function get_parent($id,$parentname = NULL) {
        global $DB;

        if ($parentcategory = $DB->get_record('course_categories',array("id"=>$id))) {
            if ($parentcategory->parent > 0 ) {
                $parentname .= $this->get_parent($parentcategory->parent,$parentname);
            } else {
                $parentname .= "Top";
            }
            $parentname .= "/" . $parentcategory->name;
        }
        return $parentname;

    }
}

