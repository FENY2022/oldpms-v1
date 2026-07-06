








<?php



session_start();
include('../../processphp/config.php');
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){

	header("location: ../../admin/login.php");
	exit;
	  }
	  
  else{

	  }






   $userid = $_SESSION["user_id"] ;


  $lumber_app = "SELECT * FROM denr_users where user_id = $userid";
  $lumber_app_qry = mysqli_query($con, $lumber_app);
  $lumber_ap_row = mysqli_fetch_assoc($lumber_app_qry);


  $lumber_app_qry = mysqli_query($con, $lumber_app);

  if($lumber_app_qry === false) {
      die('Error executing query: ' . mysqli_error($con));
  }
  
  
  $lumber_ap_row3 = mysqli_fetch_assoc($lumber_app_qry);
  
  if($lumber_ap_row3 === null) {
      die('No data found.');
      // station on navbar.php
   echo $station = $lumber_ap_row3['station'];
  
  }else{

    $station = "";
  
  }
  



  $clientname = $lumber_ap_row['name'];
  $user_role = $lumber_ap_row['user_role_id'];
  $office_id = $lumber_ap_row['office_id'];

  function getNextActionRoleId($flowStat) {
    $flowRoleMap = array(
      '0' => '1',
      '1' => '1',
      '2' => '2',
      '3' => '1',
      '4' => '1',
      '6' => '1',
      '6.1' => '1',
      '6.2' => '1',
      '6.3' => '1',
      '7' => '7',
      '8' => '8',
      '9' => '9',
      '9.1' => '9.1',
      '10' => '10',
      '11' => '11',
      '12' => '12',
      '12.5' => '12.5',
      '13' => '13',
      '14' => '14',
      '15' => '15',
      '16' => '16',
      '17' => '17'
    );

    $flowStat = trim((string)$flowStat);
    return isset($flowRoleMap[$flowStat]) ? $flowRoleMap[$flowStat] : '';
  }

  function getNextActionUsers($con, $row) {
    $status = isset($row['Status']) ? trim($row['Status']) : '';
    $applicationStatus = isset($row['Application_status']) ? trim($row['Application_status']) : '';

    if ($status === 'For Re-apply') {
      return 'For client compliance / re-apply';
    }

    if ($status === 'For Client' || $applicationStatus === 'Complete') {
      return 'Completed / for client';
    }

    $flowStat = isset($row['Flow_stat']) ? $row['Flow_stat'] : '';
    $roleId = getNextActionRoleId($flowStat);

    if ($roleId === '') {
      return 'No assigned account';
    }

    $officeId = isset($row['Office_id']) ? (int)$row['Office_id'] : 0;
    $office = isset($row['Office']) ? trim($row['Office']) : '';
    $officeUnder = isset($row['office_under']) ? trim($row['office_under']) : '';
    $flowStat = trim((string)$flowStat);
    $names = array();

    if (in_array($flowStat, array('9.1', '10', '11', '12'), true)) {
      $sql = "SELECT du.name
              FROM denr_users du
              INNER JOIN office o ON o.office_id = du.office_id
              WHERE du.user_role_id = ? AND o.station = ?
              ORDER BY du.name ASC";
      $stmt = $con->prepare($sql);
      if ($stmt) {
        $stmt->bind_param('ss', $roleId, $officeUnder);
      }
    } elseif (in_array($flowStat, array('12.5', '13', '14', '15', '16', '17'), true)) {
      $sql = "SELECT name FROM denr_users WHERE user_role_id = ? ORDER BY name ASC";
      $stmt = $con->prepare($sql);
      if ($stmt) {
        $stmt->bind_param('s', $roleId);
      }
    } else {
      $sql = "SELECT DISTINCT du.name
              FROM denr_users du
              LEFT JOIN office o ON o.office_id = du.office_id
              WHERE du.user_role_id = ?
              AND ((? > 0 AND du.office_id = ?) OR (? <> '' AND o.station = ?))
              ORDER BY du.name ASC";
      $stmt = $con->prepare($sql);
      if ($stmt) {
        $stmt->bind_param('siiss', $roleId, $officeId, $officeId, $office, $office);
      }
    }

    if (!$stmt || !$stmt->execute()) {
      return 'No assigned account';
    }

    $result = $stmt->get_result();
    while ($user = $result->fetch_assoc()) {
      if (!empty($user['name'])) {
        $names[] = $user['name'];
      }
    }

    $stmt->close();

    if (empty($names)) {
      return 'No assigned account';
    }

    return implode(', ', $names);
  }



  
?> 

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>OLDPMS | DENR R13</title>
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/fontawesome-free-6.2.0-web/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <link href="../build/css/custom.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
		  
  

		  
          <?php
				require_once('navbar.php');
			?> 
		  

        <div class="right_col" role="main">
          <div class="">		  
		
    <table class="table">
  <caption>List of released E-Permit</caption>
  <thead>

    <tr>
      <th scope="col">Lumber ID</th>
      <th scope="col">Owner Name</th>
      <th scope="col">Bussiness Trade Name</th>
      <th scope="col">Registration Number</th>
      <th scope="col">Office</th>
      <th scope="col">Status</th>
      <th scope="col">Flow Stat</th>
      <th scope="col">Next Action</th>
      <th scope="col">Action</th>
    </tr>

  </thead>
  <tbody>

<?php


if (empty($station)) {
    $sql = "SELECT * FROM `lumber_application` WHERE office_under = '$office_under' AND Flow_stat <= 15 AND Application_status != 'Return' AND Registration_Number = '' ORDER BY lumber_app_id ASC";    $province = mysqli_query($con,$sql);
}else{
    $sql = "SELECT * FROM `lumber_application` WHERE Office = '$station' AND Flow_stat <= 15 AND Application_status != 'Return' AND Registration_Number = '' ORDER BY lumber_app_id ASC";
    $province = mysqli_query($con, $sql);    
}




if ($stmt = $con->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result(); // Fetch the result set

while ($row = mysqli_fetch_array($province,MYSQLI_ASSOC)):;
 
        $oldpmsId = $row['lumber_app_id'];
        $bussiness_name = $row['bussiness_name'];
        $srfId = $row['lumber_app_id'];
        $nextActionUsers = getNextActionUsers($con, $row);
        $padulongBadgeClass = (isset($row['Status']) && trim($row['Status']) === 'For Re-apply') ? 'badge-danger' : 'badge-info';

         echo   '<tr>' ;
         echo   '<th scope="row">'.$row['lumber_app_id'].'</th>';
         echo   '<td>'.$row['perm_fname'].' '.$row['perm_lname'].'</td>';
         echo   '<td>'.$row['bussiness_name'].'</td>';
         echo   '<td>'.$row['Registration_Number'].'</td>';
         echo   '<td>'.$row['Office'].'</td>';
         echo   '<td>'.$row['Status'].'</td>';
         echo   '<td>'.htmlspecialchars($row['Flow_stat'], ENT_QUOTES, 'UTF-8').'</td>';
         echo   '<td><span class="badge '.$padulongBadgeClass.'" style="white-space: normal; text-align: left;">'.htmlspecialchars($nextActionUsers, ENT_QUOTES, 'UTF-8').'</span></td>';


         echo "
         <td>
             <div class='dropdown'>
                 <button class='btn btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton{$srfId}' data-bs-toggle='dropdown' aria-expanded='false'>
                     <i class='fa fa-cog'></i> Action
                 </button>
                 <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$srfId}'>
                     <li><a class='dropdown-item bg-success text-white' href='#' data-bs-toggle='modal' data-bs-target='#viewMap{$srfId}'><i class='fa fa-map'></i> View Map</a></li>
                     <li><a class='dropdown-item bg-secondary text-white' href='#' data-bs-toggle='modal' data-bs-target='#viewDocuments{$srfId}'><i class='fa fa-file'></i> View Documents</a></li>
                     <li><a class='dropdown-item bg-success text-white' href='#' data-bs-toggle='modal' data-bs-target='#viewTracking{$srfId}'><i class='fa fa-truck'></i> View Tracking</a></li>
                     <li><a type='button' class='dropdown-item bg-secondary text-white' href='orderofpaymentview3.php?lumber_app_id={$row['lumber_app_id']}'><i class='fa fa-credit-card'></i> Order of Payment</a></li>
                 </ul>             </div>
         </td>";
     
          echo "
         <!-- Modals -->
         <div class='modal fade' id='viewMap{$srfId}' tabindex='-1' aria-labelledby='viewMapLabel{$srfId}' aria-hidden='true'>
             <div class='modal-dialog' style='max-width: 80%; height: 50vh;'>
                 <div class='modal-content'>
                     <div class='modal-header'>
                         <h5 class='modal-title' id='viewMapLabel{$srfId}'>View Map</h5>
                         <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'>x</button>
                     </div>
                     <div class='modal-body'>
                         <iframe src='../../map/index_view_map.php?lumber_app_id={$row['lumber_app_id']}' style='width: 100%; height: 85vh; border: none;'></iframe>
                     </div>
                     <div class='modal-footer'>
                         <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                     </div>
                 </div>
             </div>
         </div>";

         echo"
     
         <div class='modal fade' id='viewEPermit{$srfId}' tabindex='-1' aria-labelledby='viewEPermitLabel{$srfId}' aria-hidden='true'>
             <div class='modal-dialog' style='max-width: 80%; height: 50vh;'>
                 <div class='modal-content'>
                     <div class='modal-header'>
                         <h5 class='modal-title' id='viewEPermitLabel{$srfId}'>View E-Permit</h5>
                         <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'>x</button>
                     </div>
                     <div class='modal-body'>
                         <iframe src='../records/generate_viewLumberEdealer.php?lumber_app_id={$oldpmsId}' style='width: 100%; height: 85vh; border: none;'></iframe>
                     </div>
                     <div class='modal-footer'>
                         <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                     </div>
                 </div>
             </div>
         </div>";

         echo "
     
         <div class='modal fade' id='viewDocuments{$srfId}' tabindex='-1' aria-labelledby='viewDocumentsLabel{$srfId}' aria-hidden='true'>
             <div class='modal-dialog' style='max-width: 80%; height: 50vh;'>
                 <div class='modal-content'>
                     <div class='modal-header'>
                         <h5 class='modal-title' id='viewDocumentsLabel{$srfId}'>View Documents</h5>
                         <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'>x</button>
                     </div>
                     <div class='modal-body'>
                         <iframe src='../listofdocuments_approved.php?lumber_app_id={$row['lumber_app_id']}' style='width: 100%; height: 85vh; border: none;'></iframe>
                     </div>
                     <div class='modal-footer'>
                         <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                     </div>
                 </div>
             </div>
         </div>";

         echo"
     
         <div class='modal fade' id='orderOfPayment{$srfId}' tabindex='-1' aria-labelledby='orderOfPaymentLabel{$srfId}' aria-hidden='true'>
             <div class='modal-dialog' style='max-width: 80%; height: 50vh;'>
                 <div class='modal-content'>
                     <div class='modal-header'>
                         <h5 class='modal-title' id='orderOfPaymentLabel{$srfId}'>Order of Payment</h5>
                         <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'>x</button>
                     </div>
                     <div class='modal-body'>
                         <iframe src='orderofpaymentview3.php?lumber_app_id={$row['lumber_app_id']}' style='width: 100%; height: 85vh; border: none;'></iframe>
                     </div>
                     <div class='modal-footer'>
                         <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                     </div>
                 </div>
             </div>
         </div>";

         echo"
     
         <div class='modal fade' id='viewCSS{$srfId}' tabindex='-1' aria-labelledby='viewCSSLabel{$srfId}' aria-hidden='true'>
             <div class='modal-dialog' style='max-width: 80%; height: 50vh;'>
                 <div class='modal-content'>
                     <div class='modal-header'>
                         <h5 class='modal-title' id='viewCSSLabel{$srfId}'>View CSS</h5>
                         <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'>x</button>
                     </div>
                     <div class='modal-body'>
                         <iframe src='orderofpaymentview3.php?lumber_app_id={$row['lumber_app_id']}' style='width: 100%; height: 85vh; border: none;'></iframe>
                     </div>
                     <div class='modal-footer'>
                         <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                     </div>
                 </div>
             </div>
         </div>";

         echo"
     
         <div class='modal fade' id='viewTracking{$srfId}' tabindex='-1' aria-labelledby='viewTrackingLabel{$srfId}' aria-hidden='true'>
             <div class='modal-dialog' style='max-width: 80%; height: 50vh;'>
                 <div class='modal-content'>
                     <div class='modal-header'>
                         <h5 class='modal-title' id='viewTrackingLabel{$srfId}'>View Tracking</h5>
                         <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'>x</button>
                     </div>
                     <div class='modal-body'>
                         <iframe src='../../client/doctracker.php?lumber_app_id={$oldpmsId}&bussiness_name={$bussiness_name}' style='width: 100%; height: 85vh; border: none;'></iframe>
                     </div>
                     <div class='modal-footer'>
                         <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                     </div>
                 </div>
             </div>
         </div>
     </tr>";


endwhile;
}
?>
  </tbody>
</table>


          </div>
        </div>

        	<?php
					require_once('footer.php');
			?>
      </div>
    </div>


    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../vendors/fastclick/lib/fastclick.js"></script>   
    <script src="../vendors/jquery-sparkline/dist/jquery.sparkline.min.js"></script>    
    <script src="../vendors/DateJS/build/date.js"></script>
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="../build/js/custom.min.js"></script>
  </body>
</html>
