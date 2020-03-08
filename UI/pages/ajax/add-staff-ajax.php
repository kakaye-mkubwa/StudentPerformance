<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../../../vendor/autoload.php';

use App\Processors\AdminFunctions;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $admin = new AdminFunctions();

  $staffName = $_POST['staffName'];
  $department = $_POST['department'];

  $response = json_decode($admin->registerStaff($staffName,$department),true);

  if ($response['error'] == "false"){
    ?><hr><div class="alert alert-success"><p align="center"><strong>
          <i class="fa info"></i> Success!</strong>
      </p></div><?php
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