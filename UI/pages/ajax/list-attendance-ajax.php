<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../../../vendor/autoload.php';

use App\Processors\AdminFunctions;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $admin = new AdminFunctions();

  $unitID = $_POST['unitID'];
  $attendanceDate = $_POST['attendanceDate'];

  $response = json_decode($admin->printAttendance($unitID,$attendanceDate),true);

  if ($response['error'] == "false"){

    ?>
    <div class="card-header border-transparent">
      <h3 class="card-title">Student Attendance</h3>

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
            <th>Student Name</th>
            <th>Unit Name</th>
            <th>Attendance</th>
          </tr>
          </thead>
          <tbody>
          <?php
          foreach ($response['message'] as $row){
            if ($row['attendance'] == 1){
              $attendanceStatus = "Present";
            }else{
              $attendanceStatus = "Absent";
            }
            ?>
            <tr>
              <td><p><?php echo $row['student_name']?></p></td>
              <td><p><?php echo $row['unit_name']?></p></td>
              <td><span class="badge badge-success"><?php echo $attendanceStatus?></span></td>
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
