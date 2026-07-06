<?php
error_reporting(0);
session_start();
require_once "../processphp/config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../admin/login.php");
    exit;
}

$userid = $_SESSION["user_id"];
$stmt = $connection->prepare("SELECT name, user_role_id, office_id FROM denr_users WHERE user_id = :user_id LIMIT 1");
$stmt->bindValue(':user_id', $userid, PDO::PARAM_INT);
$stmt->execute();
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

$clientname = $userRow['name'] ?? '';
$user_role = $userRow['user_role_id'] ?? '';
$office_id = $userRow['office_id'] ?? '';

$sql = "SELECT ccdh.id, ccdh.lumber_app_id, ccdh.Date, ccdh.Time, ccdh.Title, ccdh.Details, la.bussiness_name, la.full_address
        FROM client_client_document_history ccdh
        LEFT JOIN lumber_application la ON la.lumber_app_id = ccdh.lumber_app_id
        WHERE ccdh.Action_ = 'RO_APPROVER'
        ORDER BY ccdh.id DESC";
$stmt = $connection->prepare($sql);
$stmt->execute();
$recentRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatRecentDetails($details) {
    $details = (string) $details;
    $details = str_replace(array('<br>', '<br/>', '<br />'), "\n", $details);

    return nl2br(htmlentities($details, ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OLDPMS | Recent</title>
    <link href="vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendors/fontawesome-free-6.2.0-web/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="build/css/custom.css" rel="stylesheet">
    <style>
        #recentTable {
            width: 100%;
            min-width: 1350px;
        }
        #recentTable thead th {
            white-space: nowrap;
            vertical-align: middle;
        }
        #recentTable tbody td {
            vertical-align: top;
            word-break: break-word;
            white-space: normal;
        }
        .col-id { width: 70px; }
        .col-date { width: 105px; }
        .col-time { width: 110px; }
        .col-app { width: 90px; }
        .col-business { width: 230px; }
        .col-address { width: 260px; }
        .col-account { width: 190px; }
        .col-details { width: auto; min-width: 420px; }
        .details-box {
            max-height: 140px;
            overflow: auto;
            line-height: 1.45;
        }
    </style>
</head>
<body class="nav-md">
<div class="container body">
    <div class="main_container">
        <?php require_once('sidebar.php'); ?>
        <?php require_once('topbar.php'); ?>
        <div class="right_col" role="main">
            <div class="page-title">
                <div class="title_left">
                    <h3 class="text-success"><strong>Recent</strong> <small>| Regional Executive Director Approvals</small></h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="recentTable" width="100%">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th class="col-id">ID</th>
                                            <th class="col-date">Date</th>
                                            <th class="col-time">Time</th>
                                            <th class="col-app">App ID</th>
                                            <th class="col-business">Business Name</th>
                                            <th class="col-address">Address</th>
                                            <th class="col-account">Padulong nga Account</th>
                                            <th class="col-details">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentRows)): ?>
                                            <?php foreach ($recentRows as $row): ?>
                                                <tr>
                                                    <td><?= htmlentities($row['id'] ?? '') ?></td>
                                                    <td><?= htmlentities($row['Date'] ?? '') ?></td>
                                                    <td><?= htmlentities($row['Time'] ?? '') ?></td>
                                                    <td><?= htmlentities($row['lumber_app_id'] ?? '') ?></td>
                                                    <td><?= htmlentities($row['bussiness_name'] ?? '-') ?></td>
                                                    <td><?= htmlentities($row['full_address'] ?? '-') ?></td>
                                                    <td><?= htmlentities($row['Title'] ?? '') ?></td>
                                                    <td><div class="details-box"><?= formatRecentDetails($row['Details'] ?? '') ?></div></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No recent approvals found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once('footer.php'); ?>
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
<script>
    $(function () {
        $('#recentTable').DataTable({
            pageLength: 10,
            order: [],
            scrollX: true,
            autoWidth: false
        });
    });
</script>
<script src="build/js/custom.min.js"></script>
</body>
</html>
