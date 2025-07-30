<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/admin/tool/gradebookgenerator/index.php');
$PAGE->set_title(get_string('gradebookgenerator', 'tool_gradebookgenerator'));
$PAGE->set_heading(get_string('gradebookgenerator', 'tool_gradebookgenerator'));

const WEIGHTED_MEAN_OF_GRADES = 10;
echo $OUTPUT->header();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        // Retrieve the value of the 'id' parameter
        $id = $_POST['id'];

        // Use the $id variable as needed
        $root_category = grade_category::fetch_course_category($id);
        $root_category->aggregation = WEIGHTED_MEAN_OF_GRADES;
        $root_category->update();
        // Here you can perform actions based on the received ID
        $course = get_course($id);
        if (!$course){
            die("Course with id $id does not exist.");
        }
        // Retrieve sections for the course

        $grade_items = grade_item::fetch_all(array('courseid' => $id, 'itemtype' => 'mod'));
        foreach ($grade_items as $grade_item){
            $grade_item->set_parent($root_category->id);
        }

        $grade_categories = grade_category::fetch_all(array('courseid' => $id, 'depth' => 2)) ;
        foreach ($grade_categories as $category){
            $category->delete();
        }


        $sections = $DB->get_records('course_sections', array('course' => $id), 'section');
        // Loop through each section
        $sortorder = 0;
        $modinfo = get_fast_modinfo($id);
        $course_modules = $modinfo->get_cms();
        foreach ($sections as $section) {
            // Output section details
            $new_category = new grade_category();
            $new_category->courseid = $id;
            $new_category->fullname = $section->name;
            $new_category->timemodified = time();
            $new_category->aggregation = WEIGHTED_MEAN_OF_GRADES;


            $new_category_id = $new_category->insert();



            $has_grade_items = false;
            foreach ($course_modules as $module_instance) {
                if ($module_instance->section != $section->id){
                    continue;
                }
                $sortorder+=10;
                // Retrieve the module instance
                //$module_instance = $DB->get_record('course_modules', array('id' => $cm->id));
                // Output the module name
                $mod = $modinfo->cms[$module_instance->id];
                $item = grade_item::fetch(array('iteminstance' => $mod->instance, 'itemmodule' => $mod->modname));
                if ($item){
                    $has_grade_items=true;
                    // Update the category ID to point to the root category
                    $item->sortorder = $sortorder;
                    $item->aggregationcoef = 1;
                    $item->set_parent($new_category_id);
                }
            }
            if(!$has_grade_items){
                grade_category::fetch(array('id' => $new_category_id))->delete();
            }
            else{
                $new_category_item = grade_item::fetch(array('itemtype' => "category", 'iteminstance' => $new_category_id));
                $new_category_item->aggregationcoef = 1;
                $new_category_item->grademax = 100;
                $new_category_item->grademin = 0;
                $new_category_item->update();
            }

        }
        $root_category_item = grade_item::fetch(array('itemtype' => "course", 'courseid' => $id));
        $root_category_item->grademax = 100;
        $root_category_item->grademin = 0;
        $root_category_item->update();
        echo "<h4>Gradebook for course $course->fullname successfully recreated !</h4><br/><br/>";
        $url = new moodle_url('/grade/edit/tree/index.php', array('id' => $id));
        echo "<script>window.open('" . $url->out() . "', '_newtab');</script>";


    } else {
        // If the 'id' parameter is not found in the POST request
        echo "Error: ID parameter not found in the POST request.";
    }
}

// Get all courses
$courses = get_courses();

echo "<h4>Click on one of the courses below to recreate its gradebook</h4>";
echo "<h5>Be careful, this operation will completely delete the current gradebook and recreate one according the the sections / activities in the course.</h5>";


if (!empty($courses)) {
    echo '<ul>';
    foreach ($courses as $course) {
        ?>
      <form action="index.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $course->id; ?>">
        <button type="submit" class="list-group-item list-group-item-action"><?php echo $course->fullname; ?></button>
      </form>
      <?php
    }
    echo '</ul>';
} else {
    echo '<p>No courses found.</p>';
}

echo $OUTPUT->footer();
?>
