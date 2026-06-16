<?php

error_reporting(0);
require_once "../processphp/config.php";
// session_start();
$l_id = $_GET['lumber_app_id'];
// include 'prc_approve_modal/evaluationlRORecommender.php';

include 'production/cenro_cert_r.php';
include 'production/cenro_endorsement_r.php';

$bussiness_name = $_GET['bussiness_name'];
$full_address = $_GET['full_address'];

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>OLDPMS | DENR R13</title>

    <link href="cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link href="vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendors/fontawesome-free-6.2.0-web/css/all.min.css" rel="stylesheet" type="text/css">
       
    <link href="vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

    <link href="build/css/custom.css" rel="stylesheet">
	  
	<style>
		.btn { width:80%; }
		.card-doc { transition: all 0.3s ease; border-left: 4px solid #ffc107; }
		.card-doc:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
		.card-doc.viewed { border-left-color: #28a745; background-color: #f8fff8; }
		.badge-status { font-size: 0.8rem; padding: 5px 10px; }
	</style>
  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
		  
        <?php require_once('sidebar.php'); ?>        
        <?php require_once('topbar.php'); ?> 
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
				  <h3 class="text-success"><strong>For Evaluation</strong> <small>  | Lumber Dealers of Caraga Region</small></h3>
              </div>              
            </div>

            <div class="clearfix"></div>

            <div class="row">
			  <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                  <div class="x_title">
                    <h2 class="text-info"><?php echo $bussiness_name ; ?><small> | <?php echo $full_address ?> </small></h2>
                    <ul class="nav navbar-right panel_toolbox">
						<div class="row justify-content-center">
							  <li>
                                <form method="POST" id="approveForm">
                                    <button type="button" class="btn-primary btn-sm btn-round btn ml-0" data-toggle="modal" data-target="#approveModal">
                                        <span class="text align-content-center text-white"><strong>Approve</strong></span>
                                        <span class="icon ml-2">
                                            <i class="fas fa-check-to-slot text-white"></i>
                                        </span>
                                    </button>
                                </form>
							  </li>
							  <li><a href="action.php" class="btn-secondary btn-sm btn-round btn ml-0">                                       				 
									   <span class="text align-content-center text-white"><strong>Cancel</strong></span>
										<span class="icon ml-2">
											   <i class="fas fa-circle-chevron-left text-white"></i>
										</span>
							  </li></a>
					    </div>
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                   <div class="x_content">
                      <div class="row">
                          <div class="col-sm-12">
                            <div class="row justify-content-center">

<?php

if ( isset($_POST['Approve'])) {

  $stat_uss = 'For Client';
  $Flow_stats = '17';
  
  $sql = "UPDATE lumber_application SET Status = :Status, Flow_stat = :Flow_stat WHERE lumber_app_id = $l_id";
  $stmt = $connection->prepare($sql);
  $stmt->execute(array(
  ':Status' => $stat_uss,
  ':Flow_stat' => $Flow_stats,));

// -------------------------------------------------------------------------------

$date2 = date('m/d/y');

function getFullMonthNameFromDate($date3){
 $monthName = date('F d, Y', strtotime($date3));
 return $monthName;
}

$date3 = $date2 ;
getFullMonthNameFromDate($date3);

date_default_timezone_set("Asia/Manila");
$Time = date("h:i:sa");

   $Title = 'Regional Executive Director';
   $Details = 'Final document review, approval of the Lumber Dealer E-Permit, Memorandum informing concerned PENROs and CENROs of the approved endorsed lumber dealer application and the acknowledgment letter for the applicant confirming that the e-permit was received.'.'<br><br>'.'
  
   Approved E-Permit and Acknowledgement Letter forwarded to Records Unit to release the documents.
   ';
   
   $query2 = $connection->prepare("INSERT INTO client_client_document_history(
    lumber_app_id,
    Date,
    Title,
    Details,
    Time
    )
   VALUES (
    :lumber_app_id,
    :Date,
    :Title,
    :Details,
    :Time
    )");
   $query2->bindParam("lumber_app_id", $l_id, PDO::PARAM_STR);
   $query2->bindParam("Date", $date2, PDO::PARAM_STR);
   $query2->bindParam("Title", $Title, PDO::PARAM_STR);
   $query2->bindParam("Details", $Details, PDO::PARAM_STR);
   $query2->bindParam("Time", $Time, PDO::PARAM_STR);
   
   $result2 = $query2->execute();

// ------------------------------------------------------------------------------------------------

  $date =  date("m/d/Y") ; 

  $doc_type_name = 'Release Certification';
  $date_applied =  $date ;
  $Number_of_doc = '14';
  $doc_app_ind = '0';
  $doc_status = 'View Certificaton to Release';
  
  $query = $connection->prepare("INSERT INTO lumber_app_doc_erow(
	  lumber_app_id,
	  doc_type_name,
	  date_applied,
	  doc_status,
	  Number_of_doc,
	  doc_app_ind
	  )
  VALUES (
	  :lumber_app_id,
	  :doc_type_name,
	  :date_applied,
	  :doc_status,
	  :Number_of_doc,
	  :doc_app_ind
  )");
  $query->bindParam("lumber_app_id", $l_id, PDO::PARAM_STR);
  $query->bindParam("doc_type_name", $doc_type_name, PDO::PARAM_STR);
  $query->bindParam("date_applied", $date, PDO::PARAM_STR);
  $query->bindParam("doc_status", $doc_status, PDO::PARAM_STR);
  $query->bindParam("Number_of_doc", $Number_of_doc, PDO::PARAM_STR);
  $query->bindParam("doc_app_ind", $doc_app_ind, PDO::PARAM_STR);
  
  $result = $query->execute();
  
  function function_alert($message) {
    echo "<script>alert('$message');location='action.php';</script>";
  }

  function_alert("Successfully Approved!");
  return; 
}

?>

                <?php
$stmt = $connection->query("SELECT * FROM lumber_app_doc_erow  where lumber_app_id = $l_id ");
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {

    $Review = 'Approved';
    $Review2 = 'For Review';
    $For_Review_FG = 'For Review (FG)';
    $ApprovedFG = 'Approved (FG)';
    $For_Generate_Endorsement = 'For Generate Endorsement';
    $For_Review_FG_RED = 'For Review (FG) RED';
    $For_Review_CG = 'For Review (CG)' ;
    $Approved_CG = 'Approved (CG)';
    $ApprovedFG = 'Approved (FG)';
    $For_Review_FG_RED = 'For Review (FG) RED';
    $For_Gen_EN_Red = 'For Review (LPDD)';
    $For_Review_LPDD_CF	 = 'For Review (LPDD) CF';

      $doc_id = $row['upload_id_doc'] ?? $row['lumber_app_id'];

      $doc_name = $row['doc_type_name'];
      $icon_map = [
        'Certification' => 'fa-file-certificate',
        'Endorsement for PENRO' => 'fa-handshake',
        'Endorsement for RED' => 'fa-file-signature',
        'Release Certification' => 'fa-file-export',
        'Lumber Dealer E-Permit' => 'fa-id-card',
        'Permit' => 'fa-file-contract',
        'Application' => 'fa-file-invoice',
        'Letter' => 'fa-envelope',
        'Report' => 'fa-file-report',
        'Memorandum' => 'fa-sticky-note',
        'Acknowledgment' => 'fa-receipt',
        'Certificate' => 'fa-award',
      ];
      $icon = 'fa-file-alt';
      foreach ($icon_map as $key => $val) {
          if (stripos($doc_name, $key) !== false) { $icon = $val; break; }
      }

      echo '<div class="col-lg-4 col-md-6 mb-4">';
      echo '<div class="card card-doc h-100" data-doc-id="'.$doc_id.'">';
      echo '<div class="card-body d-flex flex-column">';
      echo '<div class="d-flex justify-content-between align-items-center mb-2">';
      echo '<span class="badge badge-secondary badge-status">#'.htmlentities($row['Number_of_doc']).'</span>';
      echo '<span class="badge badge-warning badge-status badge-status-doc">For Review</span>';
      echo '</div>';
      echo '<div class="text-center mb-3"><i class="fas '.$icon.' fa-3x text-info"></i></div>';
      echo '<h5 class="card-title mb-3 text-center">'.htmlentities($doc_name).'</h5>';

      if (($row['doc_status']) == ($Review)){
        echo '<div class="mt-auto text-right"><a class="btn btn-outline-info btn-sm view-doc-btn" data-doc-id="'.$doc_id.'" href="modal_review_ROFUS.php?upload_id_doc='.$row['upload_id_doc'].'"><i class="fas fa-eye"></i> View</a></div>';

      } elseif (($row['doc_status']) == ($ApprovedFG)) {
        echo '<div class="mt-auto text-right"><a class="btn btn-outline-info btn-sm view-doc-btn" data-doc-id="'.$doc_id.'" href="production/modaltempVIEWER.php?lumber_app_id='.$row['lumber_app_id'].'"><i class="fas fa-eye"></i> View</a></div>';

      } elseif (($row['doc_type_name']) == ('Certification')) {
        echo '<div class="mt-auto text-right"><a class="btn btn-outline-info btn-sm view-doc-btn" data-doc-id="'.$doc_id.'" target="_blank" href="production/generates_view_pdf2.php?lumber_app_id='.$row['lumber_app_id'].'"><i class="fas fa-eye"></i> View</a></div>';

      } elseif (($row['doc_type_name']) == ('Endorsement for PENRO ')) {
        echo '<div class="mt-auto text-right"><a class="btn btn-outline-info btn-sm view-doc-btn" data-doc-id="'.$doc_id.'" target="_blank" href="production/endorsement_PENRO_modal.php?lumber_app_id='.$row['lumber_app_id'].'"><i class="fas fa-eye"></i> View</a></div>';

      } elseif (($row['doc_type_name']) == ('Endorsement for RED')) {
        echo '<div class="mt-auto text-right"><a class="btn btn-outline-info btn-sm view-doc-btn" data-doc-id="'.$doc_id.'" data-toggle="modal" data-target="#endorsementRedModal" data-iframe-src="production/penro_endorsement/endorsement_PENRO_modal.php?lumber_app_id='.$row['lumber_app_id'].'"><i class="fas fa-eye"></i> View</a></div>';

      } elseif (($row['doc_status']) == ($For_Gen_EN_Red)) {
        echo '<div class="mt-auto text-right"><a class="btn btn-outline-success btn-sm view-doc-btn" data-doc-id="'.$doc_id.'" href="modaltemp_RED.php?lumber_app_id='.$row['lumber_app_id'].'"><i class="fas fa-eye"></i> View</a></div>';

      } elseif (($row['doc_status']) == ($For_Review_LPDD_CF)) {
        echo '<div class="mt-auto text-right"><a class="btn btn-outline-success btn-sm view-doc-btn" data-doc-id="'.$doc_id.'" data-toggle="modal" data-target="#lumberEPermitModal" data-iframe-src="generate_viewLumberEdealer.php?lumber_app_id='.$row['lumber_app_id'].'"><i class="fas fa-eye"></i> View</a></div>';
      }

      echo '</div>';
      echo '</div>';
      echo '</div>';
}
?>

                            </div>
                		  </div>
              		  </div>
            	  </div>
                </div>
              </div>
            </div>
          </div>
        </div>	
        <?php require_once("footer.php"); ?>
        </div>
    </div>

    <div class="modal fade" id="endorsementRedModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Endorsement for RED</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div id="endorsementLoading" class="text-center py-5">
              <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
              <p class="mt-2">Loading document...</p>
            </div>
            <iframe id="endorsementIframe" src="" style="width:100%; height:600px; border:none; display:none;"></iframe>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="lumberEPermitModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Lumber Dealer E-Permit</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div id="lumberEPermitLoading" class="text-center py-5">
              <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
              <p class="mt-2">Loading document...</p>
            </div>
            <iframe id="lumberEPermitIframe" src="" style="width:100%; height:600px; border:none; display:none;"></iframe>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
          <div class="modal-body text-center py-5 px-4">
            <div class="mb-4">
              <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10" style="width:80px;height:80px;background:rgba(40,167,69,0.1);">
                <i class="fas fa-check-double fa-3x text-success"></i>
              </span>
            </div>
            <h4 class="fw-bold mb-2">Confirm Approval</h4>
            <p class="text-muted mb-1">You are about to approve this application.</p>
            <p class="text-muted mb-0"><strong><?php echo $bussiness_name; ?></strong></p>
            <p class="text-muted small"><?php echo $full_address; ?></p>
            <hr class="my-4">
            <p class="text-warning mb-0"><i class="fas fa-exclamation-triangle me-1"></i> This action cannot be undone.</p>
          </div>
          <div class="modal-footer justify-content-center border-0 pt-0 pb-4 px-4">
            <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-dismiss="modal">
              <i class="fas fa-times me-2"></i>Cancel
            </button>
            <button type="button" class="btn btn-success btn-lg px-5 shadow-sm" onclick="document.getElementById('approveForm').submit()">
              <i class="fas fa-check me-2"></i>Approve
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="vendors/jquery/dist/jquery.min.js"></script>
    <script src="vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vendors/fastclick/lib/fastclick.js"></script>
    <script src="vendors/nprogress/nprogress.js"></script>
    <script src="vendors/iCheck/icheck.min.js"></script>
    <script src="vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    <script src="vendors/jszip/dist/jszip.min.js"></script>
    <script src="vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="vendors/pdfmake/build/vfs_fonts.js"></script>

    <script src="build/js/custom.js"></script>

<script>
(function() {
    // History/Viewed Docs Logic
    var userId = <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;
    var storageKey = 'viewedDocs_' + userId;

    function getViewedDocs() {
        try {
            return JSON.parse(localStorage.getItem(storageKey)) || [];
        } catch(e) { return []; }
    }

    function markViewed(docId) {
        var viewed = getViewedDocs();
        if (viewed.indexOf(docId) === -1) {
            viewed.push(docId);
            localStorage.setItem(storageKey, JSON.stringify(viewed));
        }
    }

    function updateCardUI(docId) {
        var card = document.querySelector('.card-doc[data-doc-id="' + docId + '"]');
        if (!card) return;
        card.classList.add('viewed');
        var badge = card.querySelector('.badge-status-doc');
        if (badge) {
            badge.className = 'badge badge-success badge-status badge-status-doc';
            badge.textContent = 'Reviewed';
        }
    }

    var viewed = getViewedDocs();
    document.querySelectorAll('.card-doc').forEach(function(card) {
        var docId = card.getAttribute('data-doc-id');
        if (viewed.indexOf(docId) !== -1) {
            updateCardUI(docId);
        }
    });

    document.querySelectorAll('.view-doc-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var docId = this.getAttribute('data-doc-id');
            markViewed(docId);
            updateCardUI(docId);
        });
    });

    // jQuery Logic to properly handle Bootstrap 4 Modal Events
    $('#endorsementRedModal').on('show.bs.modal', function (e) {
        // Get the button that triggered the modal and extract the URL
        var btn = $(e.relatedTarget);
        var src = btn.attr('data-iframe-src');
        
        // Show the loader and hide the iframe
        $('#endorsementLoading').show();
        var $iframe = $('#endorsementIframe');
        $iframe.hide();
        
        // Set the iframe source
        $iframe.attr('src', src);
    });

    $('#endorsementRedModal').on('hidden.bs.modal', function () {
        // Clear the iframe source when modal closes to kill active processes
        $('#endorsementIframe').attr('src', '');
    });

    $('#endorsementIframe').on('load', function () {
        // Only hide the loader and show the iframe if the source is not empty
        if ($(this).attr('src') !== '') {
            $('#endorsementLoading').hide();
            $(this).show();
        }
    });

    // Lumber Dealer E-Permit Modal
    $('#lumberEPermitModal').on('show.bs.modal', function (e) {
        var btn = $(e.relatedTarget);
        var src = btn.attr('data-iframe-src');
        $('#lumberEPermitLoading').show();
        var $iframe = $('#lumberEPermitIframe');
        $iframe.hide();
        $iframe.attr('src', src);
    });

    $('#lumberEPermitModal').on('hidden.bs.modal', function () {
        $('#lumberEPermitIframe').attr('src', '');
    });

    $('#lumberEPermitIframe').on('load', function () {
        if ($(this).attr('src') !== '') {
            $('#lumberEPermitLoading').hide();
            $(this).show();
        }
    });
})();
</script>

  </body>
</html>