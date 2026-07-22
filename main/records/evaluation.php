<?php
                        require_once "../../processphp/config.php";
                        // session_start();
						$l_id = $_GET['lumber_app_id'];
                        $app_qry = $connection->query("SELECT bussiness_name, full_address FROM lumber_application WHERE lumber_app_id = $l_id LIMIT 1");
                        $app_row = $app_qry ? $app_qry->fetch(PDO::FETCH_ASSOC) : null;
                        $bussiness_name = $app_row['bussiness_name'] ?? '';
                        $full_address = $app_row['full_address'] ?? '';
            // include 'prc_approve_modal/evaluationlRORecommender.php';
                   ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>OLDPMS | DENR R13</title>

    <!-- Bootstrap -->
    <link href="cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/fontawesome-free-6.2.0-web/css/all.min.css" rel="stylesheet" type="text/css">
       
    <!-- Datatables -->
    
    <link href="../vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
	  
	<style>
		.btn {
 			 width:80%;
			 }
	</style>
  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
		  
        		<!-- sidebar navigation -->        
          			<?php
						require_once('sidebar.php');
					?>        
				<!-- /sidebar navigation -->
		
         		<!-- top navigation -->
		  
					<?php
						require_once('topbar.php');
					?> 
		  
        		<!-- /top navigation -->

        
        <!-- page content -->
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
                    <h2 class="text-info">Juan Dela Cruz<small> | No1knows Lumber Dealer</small></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="row">
                          <div class="col-sm-12">
                            <div class="card-box table-responsive">
        						<table id="datatable-responsive" class="table table-striped table-bordered" style="width:100%">
								   <thead class="bg-primary text-white">                        	
										  <tr>
											  <th> No. </th>
											  <th> Document Name </th>
											  <th> Status </th>
											  <th> Action </th>
										  </tr>
								   </thead>
										   <tbody>
										   <?php

										   
	$lumber_app_id	= $l_id ;

if ( isset($_POST['Release'])) {



	$lumber_app_id	= $l_id ;

	echo $lumber_app_id ;










  

	header( "Location: endorsement2.php?lumber_app_id=$lumber_app_id" ) ;



}


?>


                <?php


			$_14_ = '14';

          $stmt = $connection->query("SELECT * FROM lumber_app_doc_erow  where lumber_app_id = $l_id && Number_of_doc = $_14_ ORDER BY doc_type_name");
          while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) 
          
          {

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
	

                echo "<tr><td>" ;
                echo(htmlentities($row['Number_of_doc']));
                echo("</td><td>");
                echo(htmlentities($row['doc_type_name']));
                echo("</td><td>");
                echo(htmlentities ($row['doc_status']) );
                // echo('<a class="btn btn-warning" "'($row['doc_type_name'])'"");
                // echo(htmlentities($row['doc_status']) . '' . ($row['perm_lname']));
                echo("</td><td>");


       
                // echo(htmlentities($row['application_type']));
                // echo("</td><td>");
                if (($row['doc_status']) == ($Review))
                echo('<a class="btn btn-warning" href="../modal_review_ROFUS.php?upload_id_doc='.$row['upload_id_doc'].'">View </a>');

                                
                if (($row['doc_status']) == ($Approved_CG))
                echo('<a class="btn btn-warning" href="../production/generates_view_pdf2.php?lumber_app_id='.$row['lumber_app_id'].'">View</a>');

                if (($row['doc_status']) == ($ApprovedFG))
                echo('<a class="btn btn-warning" href="production/modaltempVIEWER.php?lumber_app_id='.$row['lumber_app_id'].'">View </a>');

                if (($row['doc_status']) == ($For_Review_FG_RED))
                echo('<a class="btn btn-warning" href="production/generates_view_pdf2.php?lumber_app_id='.$row['lumber_app_id'].'">View</a>');


                if (($row['doc_status']) == ($For_Gen_EN_Red))
                echo('<a class="btn btn-danger" href="generate_VIEW.php?lumber_app_id='.$row['lumber_app_id'].'">View</a>');

                if (($row['doc_status']) == ($For_Review_LPDD_CF))
                echo('<a class="btn btn-danger" href="generate_viewLumberEdealer.php?lumber_app_id='.$row['lumber_app_id'].'">View</a>');

			        	if (($row['Number_of_doc']) == ($_14_))
                // echo('<a class="btn btn-danger" target="_blank"  href="generate_viewLumberEdealer.php?lumber_app_id='.$row['lumber_app_id'].'">View</a>');
                echo '<a class="btn btn-warning" style="color: white !important;" data-bs-toggle="modal" data-bs-target="#viewPermit' . $row['lumber_app_id'] . '">View</a>';



                
                echo '        <div class="modal fade" id="viewPermit' . htmlspecialchars($row['lumber_app_id'], ENT_QUOTES, 'UTF-8') . '" tabindex="-1" aria-labelledby="viewCssLabel" aria-hidden="true">
                              <div class="modal-dialog" style="max-width: 90%; height: 50vh;">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <h5 class="modal-title" id="viewCssLabel">View Permit</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                      </div>
                                      <div class="modal-body">
                                          <div id="viewPermitLoading' . $row['lumber_app_id'] . '" class="text-center py-5">
                                              <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                                              <p class="mt-2 mb-0">Loading document...</p>
                                          </div>
                                          <iframe src="generate_viewLumberEdealer.php?lumber_app_id=' . $row['lumber_app_id']  . '" style="width: 100%; height: 85vh; border: none; display:none;" onload="document.getElementById(\'viewPermitLoading' . $row['lumber_app_id'] . '\').style.display=\'none\'; this.style.display=\'block\';"></iframe>
                                      </div>
                                      <div class="modal-footer">
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">Close</button>
                                      </div>
                                  </div>
                              </div>
                          </div>';




          }
                // echo('<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.$row['user_id'].'" class="img-fluid" alt="QR Not available"  width="50" height="50t"');
    
 
                echo("</td></tr>\n");


                // include 'modal_review.php';

                  ?>

                                           </tbody>
                                 </table>
							</div>
                		  </div>
              		  </div>
            	  </div>
					<ul class="nav navbar-right panel_toolbox">
						<div class="row justify-content-center">
							  <!-- <li> <form method="POST"><button  class="btn-primary btn-sm btn-round btn ml-0" name="Release"></form> -->


				  <li>  <button type="button" class="btn-primary btn-sm btn-round btn ml-0" data-bs-toggle="modal" data-bs-target="#approveModal">                                       	
					   <span class="text align-content-center text-white"><strong>Release</strong></span>
						<span class="icon ml-2">
							   <i class="fas fa-check-to-slot text-white"></i>
						</span>
				  </button></li>



							  <li>  <a href="action.php" class="btn-secondary btn-sm btn-round btn ml-0">   
								
							  
									   <span class="text align-content-center text-white"><strong>Back</strong></span>
										<span class="icon ml-2">
											   <i class="fas fa-circle-chevron-left text-white"></i>
										</span>
							  </li></a>
					    </div>
                    </ul>
                </div>
              </div>
            </div>
          </div>
        </div>	
        <!-- /page content -->

        <!-- footer content -->
        <?php
		   require_once("footer.php")
		  ?>
	        <!-- /footer content -->
      </div>
    </div>

     <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
          <div class="modal-body text-center py-5 px-4">
            <div class="mb-4">
              <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10" style="width:80px;height:80px;background:rgba(40,167,69,0.1);">
                <i class="fas fa-check-double fa-3x text-success"></i>
              </span>
            </div>
            <h4 class="fw-bold mb-2">Confirm Release</h4>
            <p class="text-muted mb-1">You are about to release this application.</p>
            <p class="text-muted mb-0"><strong><?php echo htmlspecialchars($bussiness_name); ?></strong></p>
            <p class="text-muted small"><?php echo htmlspecialchars($full_address); ?></p>
            <hr class="my-4">
            <p class="text-warning mb-0"><i class="fas fa-exclamation-triangle me-1"></i> This action cannot be undone.</p>
            <div id="releaseErrorBox" class="alert alert-danger mt-4 mb-0 text-start" style="display:none;"></div>
          </div>
          <div class="modal-footer justify-content-center border-0 pt-0 pb-4 px-4">
            <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
              <i class="fas fa-times me-2"></i>Cancel
            </button>
            <button type="button" id="releaseConfirmBtn" class="btn btn-success btn-lg px-5 shadow-sm" data-lumber-app-id="<?php echo htmlspecialchars($l_id, ENT_QUOTES, 'UTF-8'); ?>">
              <span id="releaseBtnText"><i class="fas fa-check me-2"></i>Release</span>
              <span id="releaseBtnSpinner" style="display:none;"><i class="fas fa-spinner fa-spin me-2"></i>Releasing...</span>
            </button>
          </div>
        </div>
      </div>
     </div>

     <div class="modal fade" id="releaseResultModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
          <div class="modal-body text-center py-5 px-4">
            <div class="mb-4">
              <span id="releaseResultIcon" class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:80px;height:80px;"></span>
            </div>
            <h4 id="releaseResultTitle" class="fw-bold mb-2"></h4>
            <p id="releaseResultMessage" class="text-muted mb-3"></p>
            <hr class="my-4">
            <div id="releaseEmailStatus" class="text-start mb-3"></div>
          </div>
          <div class="modal-footer justify-content-center border-0 pt-0 pb-4 px-4">
            <a href="action.php" class="btn btn-primary btn-lg px-5 shadow-sm">
              <i class="fas fa-arrow-left me-2"></i>Go Back
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
   <script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    <!-- iCheck -->
    <script src="../vendors/iCheck/icheck.min.js"></script>
    <!-- Datatables -->
    <script src="../vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="../vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="../vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="../vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="../vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    <script src="../vendors/jszip/dist/jszip.min.js"></script>
    <script src="../vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="../vendors/pdfmake/build/vfs_fonts.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>

    <script>
    (function() {
        var releaseButton = document.getElementById('releaseConfirmBtn');
        if (!releaseButton) return;

        releaseButton.addEventListener('click', async function() {
            var errorBox = document.getElementById('releaseErrorBox');
            var lumberAppId = this.getAttribute('data-lumber-app-id');
            var btnText = document.getElementById('releaseBtnText');
            var btnSpinner = document.getElementById('releaseBtnSpinner');
            var cancelBtn = document.querySelector('#approveModal .btn-outline-secondary');

            errorBox.style.display = 'none';
            errorBox.textContent = '';
            this.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline';
            if (cancelBtn) cancelBtn.disabled = true;

            try {
                var formData = new FormData();
                formData.append('lumber_app_id', lumberAppId);

                var response = await fetch('release.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                var data = await response.json();

                if (!data.success) {
                    errorBox.textContent = data.message || 'Release failed.';
                    errorBox.style.display = 'block';
                    return;
                }

                $('#approveModal').modal('hide');

                var resultIcon = document.getElementById('releaseResultIcon');
                var resultTitle = document.getElementById('releaseResultTitle');
                var resultMessage = document.getElementById('releaseResultMessage');
                var emailStatus = document.getElementById('releaseEmailStatus');

                resultIcon.innerHTML = '<i class="fas fa-check-double fa-3x text-success"></i>';
                resultIcon.style.background = 'rgba(40,167,69,0.1)';
                resultTitle.textContent = 'Application Released Successfully!';
                resultTitle.className = 'fw-bold mb-2 text-success';
                resultMessage.textContent = data.message || '';

                if (data.email_sent) {
                    emailStatus.innerHTML = '<div class="alert alert-success mb-0"><i class="fas fa-envelope me-2"></i>' + (data.email_message || 'Email notification sent successfully.') + '</div>';
                } else {
                    emailStatus.innerHTML = '<div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle me-2"></i>' + (data.email_message || 'Email notification could not be sent.') + '</div>';
                }

                $('#releaseResultModal').modal('show');

            } catch (error) {
                errorBox.textContent = error.message || 'Release failed.';
                errorBox.style.display = 'block';
            } finally {
                releaseButton.disabled = false;
                btnText.style.display = 'inline';
                btnSpinner.style.display = 'none';
                if (cancelBtn) cancelBtn.disabled = false;
            }
        });
    })();
    </script>

  </body>
</html>
