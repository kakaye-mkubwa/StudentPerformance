<?php
use Faker;
use App\Processors\AdminFunctions;

class FakeData{
    private $faker;
    private $adminFunctions;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
        $this->faker->addProvider(new Faker\Provider\Address($this->faker));
        $this->faker->addProvider(new Faker\Provider\en_HK\Address($this->faker));
        $this->adminFunctions = new AdminFunctions();

    }

    public function addCourse(){
        $i = 0;
        while ($i < 20){
            $id = rand(1,10);
            $this->adminFunctions->addCourse($this->faker->city,$id);
            $i++;
        }
    }

    public function addDepartments(){
        $i = 0;

        while ($i < 10) {
            $this->adminFunctions->addDepartment($this->faker->city);
            $i++;
        }
    }

    public function addStudents(){
        $i = 0;
        while ($i < 150){
            $id = rand(1,20);
            $this->adminFunctions->registerStudent($this->faker->name,$this->faker->date(),$id,$this->faker->date());
            $i++;
        }
    }

    public function addStudentGrade(){
        $i = 0;
        while ($i < 150){
            $studID = rand(1,150);
            $unitID = rand(1,10);
            $coursePeriod = rand(1,4);
            $gradesString = "ABCDF";
            $grade = $gradesString[rand(0,strlen($gradesString) - 1)];
            $this->adminFunctions->addStudentGrade($studID,$unitID,$grade,$coursePeriod);
            $i++;
        }
    }

    public function addUnits(){
        $i = 0;
        while ($i < 50){
            $courseCode = rand(1,10);
            $this->adminFunctions->addUnits($this->faker->company,$courseCode);
            $i++;
        }
    }
    public function editCourse(){

    }

    public function addAtendance(){
        $unitID = rand(1,10);
        $unitDate = $this->faker->date();
        $i = 0;

        while ($i < 30){
            $studID = rand(1,150);
            $attendStatus = rand(0,1);
            $this->adminFunctions->recordAttendance($studID,$unitID,$attendStatus,$unitDate);
            $i++;
        }
    }

    public function addStaff(){
        $i = 0;

        while ($i < 30){
            $staffName = $this->faker->name;
            $departmentID = rand(1,10);
            $this->adminFunctions->registerStaff($staffName,$departmentID);
            $i++;
        }
    }

    public function printAttendance(){
        echo $this->adminFunctions->printAttendance(8,"1979-10-16");
    }

    public function printPerformancePerCourse(){
        echo $this->adminFunctions->printResultsPerCourse(10);
    }

    public function printCourses(){
        echo $this->adminFunctions->printCourses();
    }

    public function printStaff(){
        echo $this->adminFunctions->printStaff();
    }

    public function printStudentsPerCourse(){
        echo $this->adminFunctions->printStudentsPerCourse(2);
    }

    public function printPerformancePerCourseAndPeriod(){
        $response = json_decode($this->adminFunctions->printPerformancePerCourseAndYear(10,3),true);
//        $response['message'];
        foreach ($response['message'] as $output){
//            echo $output['student_name'];
            var_dump($output);
        }
    }
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../vendor/autoload.php';

$fakeAdmin = new FakeData();
//$fakeAdmin->addDepartments();
//$fakeAdmin->addCourse();
//$fakeAdmin->addStudents();
//$fakeAdmin->addUnits();
//$fakeAdmin->addStudentGrade();
//$fakeAdmin->addAtendance();
//$fakeAdmin->addStaff();


$fakeAdmin->printAttendance();
//$fakeAdmin->printPerformancePerCourse();
//$fakeAdmin->printCourses();
//$fakeAdmin->printStaff();
//$fakeAdmin->printStudentsPerCourse();
//$fakeAdmin->printPerformancePerCourseAndPeriod();