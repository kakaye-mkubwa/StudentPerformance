<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../../../vendor/autoload.php';

use App\Processors\AdminFunctions;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $admin = new AdminFunctions();
  $courseCode = $_POST['courseInput'];

  $response = json_decode($admin->printStudentsPerCourse($courseCode),true);
  if ($response['error'] == "false"){
    ?>
    <div class="card-header border-transparent">
      <h3 class="card-title">Students List</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
        <button type="button" class="btn btn-tool" data-card-widget="remove">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table m-0">
          <thead>
          <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Course</th>
          </tr>
          </thead>
          <tbody>
          <?php
          foreach ($response['message'] as $row){
            ?>
            <tr>
              <td><p><?php echo $row['student_id']?></p></td>
              <td><p><?php echo $row['student_name']?></p></td>
              <td><p><?php echo$row['course_name']?></p></td>
            </tr>
            <?php
          }
          ?>

          </tbody>
        </table>
      </div>
      <!-- /.table-responsive -->
    </div>
    <!-- /.card-body -->
    <?php
  }else{
    ?>
    <hr><div class="alert alert-danger"><p align="center"><strong>
          <i class="fa fa-exclamation-triangle"></i> Error Processing Request!</strong>
        <?php echo $response['message']?></p></div>
    <?php
  }

}else{
  ?>
  <hr><div class="alert alert-danger"><p align="center"><strong>
        <i class="fa fa-exclamation-triangle"></i> Error Processing Request!</strong>
      Ooops! Something went wrong</p></div>
  <?php
}
