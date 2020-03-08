<?php
namespace App\Processors;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use App\Utils\DBConnect;

class AdminFunctions{

    private $log;
    private $dbConnect;

    public function __construct()
    {
        $this->log = new Logger("Admin Functions");

        $this->dbConnect = new DBConnect();

        $errorStreamHandler = new StreamHandler("../runtime/logs/error.log",Logger::ERROR);
        $infoStreamHandler = new StreamHandler("../runtime/logs/info.log",Logger::INFO);
        $debugStreamHandler = new StreamHandler("../runtime/logs/debug.log",Logger::DEBUG);

        $this->log->pushHandler($errorStreamHandler);
        $this->log->pushHandler($infoStreamHandler);
        $this->log->pushHandler($debugStreamHandler);
    }

    public function registerStudent($name,$admissionDate,$courseID,$dOB){
        $dbConnect = new DBConnect();
        $conn = $dbConnect->connection();

        $query = "INSERT INTO students(studentName,admissionDate,courseID,DOB) VALUES(?,?,?,?)";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'ssis',$paramStudentName,$paramAdmissionDate,$paramCourseID,$paramDOB);
            $paramStudentName = $name;
            $paramCourseID = $courseID;
            $paramAdmissionDate = $admissionDate;
            $paramDOB = $dOB;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Success");
            }else{
                $this->log->error("Register student failed ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Register student prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }
        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function addStudentGrade($studentID,$unitID,$grade,$coursePeriod){
        $dbConnect = new DBConnect();
        $conn = $dbConnect->connection();

        if ($this->confirmGradeExists($studentID,$unitID) == 0){
            $query = "INSERT INTO students_perfomance(studentID,unitID,grade,coursePeriod) VALUES(?,?,?,?)";

            if ($stmt = mysqli_prepare($conn,$query)){
                mysqli_stmt_bind_param($stmt,'iiss',$paramStudentID,$paramUnitID,$paramGrade,$paramCoursePeriod);
                $paramStudentID = $studentID;
                $paramCoursePeriod = $coursePeriod;
                $paramGrade = $grade;
                $paramUnitID = $unitID;

                if (mysqli_stmt_execute($stmt)){
                    $output = array("error"=>"false","message"=>"Success");
                }else{
                    $this->log->error("Adding grade failed ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                    $output = array("error"=>"true","message"=>"Failed");
                }
                mysqli_stmt_close($stmt);
            }else{
                $this->log->error("Prepare Failed ".mysqli_error($conn));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_close($conn);
            return json_encode($output,JSON_UNESCAPED_SLASHES);
        }else{
            $output = array("error"=>"true","message"=>"Marks already entered for unit");
            return json_encode($output,JSON_UNESCAPED_SLASHES);
        }
    }

    public function confirmGradeExists($studentID,$unitID){
        $dbConnect = new DBConnect();
        $conn = $dbConnect->connection();

        $query = "SELECT COUNT(*) FROM students_perfomance WHERE studentID = ? AND unitID = ?";
        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'ii',$paramStudentID,$paramUnitID);
            $paramStudentID = $studentID;
            $paramUnitID = $unitID;

            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$recordsBefore);
                while (mysqli_stmt_fetch($stmt)){
                    $recordsCount = $recordsBefore;
                }
                $output = array("error"=>"false","message"=>"recordsCount");
            }else{
                $this->log->error("Failed counting ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"-1");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"-1");
        }

        mysqli_close($conn);
        return $output["message"];
    }

    public function recordAttendance($studentID,$unitID,$attendStatus,$unitDate){
        $dbConnect = new DBConnect();
        $conn = $dbConnect->connection();

        if ($this->confirmDuplicateAttendance($studentID,$unitID,$unitDate) == 0){
            $query = "INSERT INTO attendance(studentID,unitID,attendance,unitDate) VALUES(?,?,?,?)";

            if ($stmt = mysqli_prepare($conn,$query)){
                mysqli_stmt_bind_param($stmt,'iiis',$paramStudentID,$paramUnitID,$paramAttendStatus,$paramDate);
                $paramUnitID = $unitID;
                $paramStudentID = $studentID;
                $paramAttendStatus = $attendStatus;
                $paramDate = $unitDate;

                if (mysqli_stmt_execute($stmt)){
                    $output = array("error"=>"false","message"=>"Success");
                }else{
                    $this->log->error("Recording attendance for $studentID failed ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                    $output = array("error"=>"true","message"=>"Failed");
                }
            }else{
                $this->log->error("Prepare failed ".mysqli_error($conn));
                $output = array("error"=>"true","message"=>"Failed");
            }

            return json_encode($output,JSON_UNESCAPED_SLASHES);
        }else{
            $output = array("error"=>"true","message"=>"Attendance already recorded for unit");
            return json_encode($output,JSON_UNESCAPED_SLASHES);
        }
    }

    public function confirmDuplicateAttendance($studentID,$unitID,$unitDate){
        $dbConnect = new DBConnect();
        $conn = $dbConnect->connection();

        $query = "SELECT COUNT(*) FROM attendance WHERE studentID = ? AND unitID = ? AND unitDate = ?";
        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'iis',$paramStudentID,$paramUnitID,$paramDate);
            $paramDate = $unitDate;
            $paramStudentID = $studentID;
            $paramUnitID = $unitID;

            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$recordsBefore);
                while (mysqli_stmt_fetch($stmt)){
                    $recordsCount = $recordsBefore;
                }
                $output = array("error"=>"false","message"=>"recordsCount");
            }else{
                $this->log->error("Failed counting ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"-1");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"-1");
        }

        mysqli_close($conn);
        return $output["message"];
    }

    public function addCourse($courseName,$departmentID){
        $conn = $this->dbConnect->connection();

        $query = "INSERT INTO courses(courseName,departmentCode) VALUES(?,?)";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'si',$paramCourseName,$paramDepartmentCode);
            $paramCourseName = $courseName;
            $paramDepartmentCode = $departmentID;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Success");
            }else{
                $this->log->error("Failed adding course ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function editCourse($courseID,$newDepartmentCode,$newCourseName){
        $conn = $this->dbConnect->connection();
        $query = "UPDATE course SET courseName = ?, departmentCode = ? WHERE courseCode = ?";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'sii',$paramCourseName,$paramDepartmentCode,$paramCourseCode);
            $paramDepartmentCode = $newDepartmentCode;
            $paramCourseName = $newCourseName;
            $paramCourseCode = $courseID;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Success");
            }else{
                $this->log->error("Prepare failed ".mysqli_stmt_error($stmt).' '.mysqli_error($conn));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function addDepartment($departmentName){
        $conn = $this->dbConnect->connection();

        $query = "INSERT INTO departments(departmentName) VALUES(?)";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'s',$paramDepartmentName);
            $paramDepartmentName = $departmentName;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Success");
            }else{
                $this->log->error("Failed adding course ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function editDepartments($departmentName,$departmentCode){
        $conn = $this->dbConnect->connection();
        $query = "UPDATE departments SET departmentName = ? WHERE departmentID = ?";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'si',$paramDepartmentName,$paramDepartmentID);
            $paramDepartmentID = $departmentCode;
            $paramDepartmentName = $departmentName;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Success");
            }else{
                $this->log->error("Failed editing department ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }

            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }
    public function addUnits($unitName,$courseCode){
        $conn = $this->dbConnect->connection();
        $query = "INSERT INTO units(unitName,courseCode) VALUES(?,?)";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'si',$paramUnitName,$paramCourseCode);
            $paramUnitName = $unitName;
            $paramCourseCode = $courseCode;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Success");
            }else{
                $this->log->error("Failed adding unit ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function deleteUnits($unitCode){
        $conn = $this->dbConnect->connection();
        $query = "DELETE units FROM units WHERE unitCode = ?";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'i',$paramUnitCode);
            $paramUnitCode = $unitCode;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Delete Success");
            }else{
                $this->log->error("Failed deleting unit ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Delete Failed");
            }

            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
            $output = array("error"=>"true","message"=>"Delete Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function registerStaff($staffName,$departmentID){
        $conn = $this->dbConnect->connection();
        $query = "INSERT INTO staff(staffName,departmentID) VALUES (?,?)";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'si',$paramStaffName,$paramDepartmentID);
            $paramDepartmentID = $departmentID;
            $paramStaffName = $staffName;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Success");
            }else{
                $this->log->error("Failed registering staff ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"false","message"=>"Success");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"false","message"=>"Success");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function deleteStaff($staffID){
        $conn = $this->dbConnect->connection();
        $query = "DELETE FROM staff WHERE staffNumber = ?";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'i',$paramStaffNumber);
            $paramStaffNumber = $staffID;

            if (mysqli_stmt_execute($stmt)){
                $output = array("error"=>"false","message"=>"Delete Success");
            }else{
                $this->log->error("Failed deleting staff ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Delete Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Delete Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function printResultsPerCourse($courseID){
        $conn = $this->dbConnect->connection();
        $query = "SELECT p.studentID,s.studentName,p.unitID,u.unitName,p.grade,p.coursePeriod FROM students_perfomance p INNER JOIN students s ON p.studentID = s.studentID INNER JOIN units u ON p.unitID = u.unitCode WHERE u.courseCode = ?";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'i',$paramCourseCode);
            $paramCourseCode = $courseID;

            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$resultStudentID,$resultStudentName,$resultUnitID,$resultUnitName,$resultGrade,$resultCoursePeriod);
                while (mysqli_stmt_fetch($stmt)){
                    $data[] = array("student_id"=>$resultStudentID,"student_name"=>$resultStudentName,"unit_id"=>$resultUnitID,"unit_name"=>$resultUnitName,"grade"=>$resultGrade,"course_period"=>$resultCoursePeriod);
                }
                $output =array("error"=>"false","message"=>$data);
            }else{
                $this->log->error("Failed printing results ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }
        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function printPerformancePerCourseAndYear($courseID,$period){
        $conn = $this->dbConnect->connection();
        $query = "SELECT p.studentID,s.studentName,p.unitID,u.unitName,p.grade,p.coursePeriod FROM students_perfomance p INNER JOIN students s ON p.studentID = s.studentID INNER JOIN units u ON p.unitID = u.unitCode WHERE u.courseCode = ? AND p.coursePeriod = ?";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'ii',$paramCourseCode,$paramCoursePeriod);
            $paramCourseCode = $courseID;
            $paramCoursePeriod = $period;

            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$resultStudentID,$resultStudentName,$resultUnitID,$resultUnitName,$resultGrade,$resultCoursePeriod);
                while (mysqli_stmt_fetch($stmt)){
                    $data[] = array("student_id"=>$resultStudentID,"student_name"=>$resultStudentName,"unit_id"=>$resultUnitID,"unit_name"=>$resultUnitName,"grade"=>$resultGrade,"course_period"=>$resultCoursePeriod);
                }
                $output =array("error"=>"false","message"=>$data);
            }else{
                $this->log->error("Failed printing results ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }
        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function printStudentsPerCourse($courseID){
        $conn = $this->dbConnect->connection();
        $query = "SELECT s.studentID,s.studentName,co.courseCode,co.courseName FROM students s INNER JOIN courses co ON s.courseID = co.courseCode WHERE s.courseID = ?";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'i',$paramCourseCode);
            $paramCourseCode = $courseID;

            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$resultStudentID,$resultStudentName,$resultCourseID,$resultCourseName);
                while (mysqli_stmt_fetch($stmt)){
                    $data[] = array("student_id"=>$resultStudentID,"student_name"=>$resultStudentName,"course_id"=>$resultCourseID,"course_name"=>$resultCourseName);
                }
                $output =array("error"=>"false","message"=>$data);
            }else{
                $this->log->error("Failed printing results ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }
        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }
    public function printDepartments(){
        $conn = $this->dbConnect->connection();
        $query = "SELECT departName,departmentID FROM departments";

        if ($stmt = mysqli_prepare($conn,$query)){
            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$paramDepartmentName,$paramDepartmentID);
                while (mysqli_stmt_fetch($stmt)){
                    $data[] = array("department_name"=>$paramDepartmentName,"department_id"=>$paramDepartmentID);
                }
                $output = array("error"=>"false","message"=>$data);
            }else{
                $this->log->error("Failed printing departments ".mysqli_error($conn).mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function printCourses(){
        $conn = $this->dbConnect->connection();
        $query = "SELECT co.courseCode,co.courseName,co.departmentCode,d.departmentName FROM courses co INNER JOIN departments d ON co.departmentCode = d.departmentID";

        if ($stmt=mysqli_prepare($conn,$query)){
            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$paramCourseCode,$paramCourseName,$paramDepartmentCode,$paramDepartmentName);
                while (mysqli_stmt_fetch($stmt)){
                    $data[] = array("course_code"=>$paramCourseCode,"course_name"=>$paramCourseName,"department_code"=>$paramDepartmentCode,"department_name"=>$paramDepartmentName);
                }
                $output = array("error"=>"false","message"=>$data);
            }else{
                $this->log->error("Failed printing course ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }
        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function printStaff(){
        $conn = $this->dbConnect->connection();
        $query = "SELECT s.staffNumber,s.staffName,s.departmentID,d.departmentName FROM staff s INNER JOIN departments d ON s.departmentID = d.departmentID";

        if ($stmt = mysqli_prepare($conn,$query)){
            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$paramStaffNumber,$paramStaffName,$paramDepartmentID,$paramDepartmentName);

                while (mysqli_stmt_fetch($stmt)){
                    $data[] = array("staff_number"=>$paramStaffNumber,"staff_name"=>$paramStaffName,"department_id"=>$paramDepartmentID,"department_name"=>$paramDepartmentName);
                }

                $output = array("error"=>"false","message"=>$data);
            }else{
                $this->log->error("Failed printing staff ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

    public function printUnitsPerCourse($courseID){
        $conn = $this->dbConnect->connection();
        $query = "SELECT u.unitCode,u.unitName,co.courseCode FROM units u INNER JOIN course co ON co.courseCode = u.unitCode WHERE courseCode = ?";;

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'s',$paramCourseCode);
            $paramCourseCode = $courseID;

            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$resultUnitCode,$resultUnitName,$resultCourseCode);
                while (mysqli_stmt_fetch($stmt)){
                    $data[] = array("unit_code"=>$resultUnitCode,"unit_name"=>$resultUnitName,"course_code"=>$resultCourseCode);
                }
                $output = array("error"=>"false","message"=>$data);
            }else{
                $this->log->error("Failed printing units according to course ".mysqli_error($conn).' '.mysqli_stmt_error_list($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }
        mysqli_close($conn);
    }

    public function printAttendance($unitID,$date){
        $conn = $this->dbConnect->connection();
        $query = "SELECT s.studentName,u.unitName,a.attendance FROM attendance a INNER JOIN students s ON s.studentID = a.studentID INNER JOIN units u ON a.unitID = u.unitCode WHERE unitID = ? AND unitDate = ?";

        if ($stmt = mysqli_prepare($conn,$query)){
            mysqli_stmt_bind_param($stmt,'is',$paramUnitID,$paramUnitDate);
            $paramUnitID = $unitID;
            $paramUnitDate = $date;
            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt,$paramStudentName,$paramUnitName,$paramAttendance);
                while (mysqli_stmt_fetch($stmt)){
                    $data[] = array("student_name"=>$paramStudentName,"unit_name"=>$paramUnitName,"attendance"=>$paramAttendance);
                }
                $output = array("error"=>"false","message"=>$data);
            }else{
                $this->log->error("Failed printing attendance ".mysqli_error($conn).' '.mysqli_stmt_error($stmt));
                $output = array("error"=>"true","message"=>"Failed");
            }
            mysqli_stmt_close($stmt);
        }else{
            $this->log->error("Prepare failed ".mysqli_error($conn));
            $output = array("error"=>"true","message"=>"Failed");
        }

        mysqli_close($conn);
        return json_encode($output,JSON_UNESCAPED_SLASHES);
    }

}
