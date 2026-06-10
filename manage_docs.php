<?php
// Include the database connection
// Adjust the path as necessary depending on where this file is located
require_once 'processphp/config.php'; 

// ==========================================
// Handle Single & Bulk Delete Requests
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    // Single Delete
    $delete_id = $_POST['delete_id'];
    $stmt = $connection->prepare("DELETE FROM lumber_app_doc_erow WHERE upload_id_doc = :id");
    $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $stmt->execute();
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=deleted");
    exit;
} elseif (isset($_POST['action']) && $_POST['action'] == 'bulk_delete') {
    // Bulk Delete (Marked Items)
    if (!empty($_POST['marked_ids'])) {
        // Sanitize and prepare the marked IDs
        $ids = implode(',', array_map('intval', $_POST['marked_ids']));
        $connection->exec("DELETE FROM lumber_app_doc_erow WHERE upload_id_doc IN ($ids)");
        
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=deleted");
        exit;
    } else {
        // If they clicked delete but didn't mark anything
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// ==========================================
// Handle Add / Edit Request
// ==========================================
if (isset($_POST['save_document'])) {
    $upload_id_doc = $_POST['upload_id_doc']; 
    $name_app_doc  = $_POST['name_app_doc'];
    $doc_type_name = $_POST['doc_type_name'];
    $lumber_app_id = $_POST['lumber_app_id'];
    $doc_status    = $_POST['doc_status'];
    $date_applied  = $_POST['date_applied'];
    $remarks       = $_POST['remarks'];
    $Number_of_doc = $_POST['Number_of_doc']; 

    if (empty($upload_id_doc)) {
        // INSERT NEW RECORD
        $sql = "INSERT INTO lumber_app_doc_erow 
                (name_app_doc, doc_type_name, lumber_app_id, doc_status, date_applied, remarks, Number_of_doc, date_recieved, date_approved, date_disapprove, validator_id, inspection_val_id, validation_info_id, uniqid_lapp, doc_app_ind) 
                VALUES (?, ?, ?, ?, ?, ?, ?, '', '', '', '', '', '', '', '')";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$name_app_doc, $doc_type_name, $lumber_app_id, $doc_status, $date_applied, $remarks, $Number_of_doc]);
        $status = "added";
    } else {
        // UPDATE EXISTING RECORD
        $sql = "UPDATE lumber_app_doc_erow SET 
                name_app_doc = ?, 
                doc_type_name = ?, 
                lumber_app_id = ?, 
                doc_status = ?, 
                date_applied = ?, 
                remarks = ?,
                Number_of_doc = ?
                WHERE upload_id_doc = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$name_app_doc, $doc_type_name, $lumber_app_id, $doc_status, $date_applied, $remarks, $Number_of_doc, $upload_id_doc]);
        $status = "updated";
    }
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=" . $status);
    exit;
}

// ==========================================
// Fetch All Documents for the Table
// ==========================================
$query = "SELECT * FROM lumber_app_doc_erow ORDER BY upload_id_doc DESC";
$stmt = $connection->prepare($query);
$stmt->execute();
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lumber App Documents</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 10px; }
        .table-responsive { overflow-x: auto; }
        thead th { background-color: #198754 !important; color: white !important; white-space: nowrap; }
        .action-btn { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 5px; }
        .form-check-input { cursor: pointer; }
    </style>
</head>
<body>

<div class="container-fluid py-4 px-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="text-success fw-bold"><i class="fa-solid fa-folder-open me-2"></i>Lumber Documents Directory</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#docModal" onclick="clearForm()">
                <i class="fa-solid fa-plus me-1"></i> Add Document
            </button>
        </div>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?php 
                if($_GET['status'] == 'deleted') echo "Document(s) successfully deleted.";
                if($_GET['status'] == 'added') echo "New document successfully added.";
                if($_GET['status'] == 'updated') echo "Document successfully updated.";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <form action="" method="POST" id="bulkDeleteForm" onsubmit="return confirmBulkDelete();">
            <input type="hidden" name="action" value="bulk_delete">
            
            <div class="mb-3">
                <button type="submit" class="btn btn-danger" id="bulkDeleteBtn" disabled>
                    <i class="fa-solid fa-trash-can me-1"></i> Delete Marked
                </button>
            </div>

            <div class="table-responsive">
                <table id="docTable" class="table table-hover table-striped w-100 align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th>ID</th>
                            <th>Doc #</th> 
                            <th>Document Name</th>
                            <th>Document Type</th>
                            <th>App ID</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $row): ?>
                        <tr>
                            <td class="text-center">
                                <input class="form-check-input row-checkbox" type="checkbox" name="marked_ids[]" value="<?= htmlspecialchars($row['upload_id_doc']) ?>">
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($row['upload_id_doc']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['Number_of_doc']) ?></span></td> 
                            <td>
                                <a href="#" class="text-decoration-none text-primary fw-semibold">
                                    <?= htmlspecialchars($row['name_app_doc']) ?>
                                </a>
                            </td>
                            <td><small><?= htmlspecialchars($row['doc_type_name']) ?></small></td>
                            <td><?= htmlspecialchars($row['lumber_app_id']) ?></td>
                            <td><?= htmlspecialchars($row['date_applied']) ?></td>
                            <td>
                                <?php 
                                    $status = htmlspecialchars($row['doc_status']); 
                                    $badgeColor = ($status == 'Approved') ? 'bg-success' : (($status == 'Pending') ? 'bg-warning text-dark' : 'bg-secondary');
                                ?>
                                <span class="badge <?= $badgeColor ?> px-2 py-1"><?= $status ?></span>
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-primary action-btn me-1" title="Edit" 
                                    onclick='editDoc(<?= json_encode($row) ?>)'>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                
                                <button type="button" class="btn btn-sm btn-danger action-btn" title="Delete" onclick="deleteSingle(<?= $row['upload_id_doc'] ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        
        <form id="singleDeleteForm" action="" method="POST" style="display: none;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" id="single_delete_id" value="">
        </form>
    </div>
</div>

<div class="modal fade" id="docModal" tabindex="-1" aria-labelledby="docModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="docModalLabel"><i class="fa-solid fa-file-signature me-2"></i>Document Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST">
          <div class="modal-body">
              <input type="hidden" name="upload_id_doc" id="upload_id_doc">
              
              <div class="row g-3">
                  <div class="col-md-9">
                      <label class="form-label fw-bold">Document Name (File Name)</label>
                      <input type="text" class="form-control" name="name_app_doc" id="name_app_doc" required placeholder="e.g. PDF-638eac19eb.pdf">
                  </div>

                  <div class="col-md-3">
                      <label class="form-label fw-bold">Doc #</label>
                      <input type="text" class="form-control" name="Number_of_doc" id="Number_of_doc" required placeholder="e.g. 1">
                  </div>
                  
                  <div class="col-md-8">
                      <label class="form-label fw-bold">Document Type</label>
                      <input type="text" class="form-control" name="doc_type_name" id="doc_type_name" required placeholder="e.g. Annual Business Plan">
                  </div>
                  
                  <div class="col-md-4">
                      <label class="form-label fw-bold">Lumber App ID</label>
                      <input type="text" class="form-control" name="lumber_app_id" id="lumber_app_id" required>
                  </div>
                  
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Date Applied</label>
                      <input type="text" class="form-control" name="date_applied" id="date_applied" placeholder="DD/MM/YYYY" required>
                  </div>
                  
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Document Status</label>
                      <select class="form-select" name="doc_status" id="doc_status" required>
                          <option value="Approved">Approved</option>
                          <option value="Pending">Pending</option>
                          <option value="Disapproved">Disapproved</option>
                      </select>
                  </div>
                  
                  <div class="col-md-12">
                      <label class="form-label fw-bold">Remarks</label>
                      <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Additional notes..."></textarea>
                  </div>
              </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="save_document" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i> Save Document</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTables
        var table = $('#docTable').DataTable({
            "order": [[ 1, "desc" ]], // Changed to order by ID column (which is now index 1)
            "pageLength": 10,
            "columnDefs": [
                { "orderable": false, "targets": [0, 8] } // Disable sorting on checkbox & action columns
            ],
            "language": {
                "search": "Quick Search:",
                "searchPlaceholder": "Filter records..."
            }
        });

        // "Select All" checkbox logic
        $('#selectAll').on('click', function(){
            var rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
            toggleBulkDeleteBtn();
        });

        // Individual row checkbox logic
        $('#docTable tbody').on('change', 'input[type="checkbox"]', function(){
            if(!this.checked){
                var el = $('#selectAll').get(0);
                if(el && el.checked && ('indeterminate' in el)){
                    el.indeterminate = true;
                }
            }
            toggleBulkDeleteBtn();
        });

        // Enable/Disable the Bulk Delete button if items are checked
        function toggleBulkDeleteBtn() {
            var isChecked = $('.row-checkbox:checked').length > 0;
            $('#bulkDeleteBtn').prop('disabled', !isChecked);
        }
    });

    // Confirmation for Bulk Delete
    function confirmBulkDelete() {
        var count = $('.row-checkbox:checked').length;
        if (count === 0) return false;
        return confirm('Are you sure you want to delete the ' + count + ' marked document(s)? This cannot be undone.');
    }

    // Single Delete Action trigger
    function deleteSingle(id) {
        if(confirm('Are you sure you want to delete this document? This cannot be undone.')) {
            document.getElementById('single_delete_id').value = id;
            document.getElementById('singleDeleteForm').submit();
        }
    }

    // Populate Modal for Editing
    function editDoc(doc) {
        document.getElementById('docModalLabel').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Edit Document';
        document.getElementById('upload_id_doc').value = doc.upload_id_doc;
        document.getElementById('name_app_doc').value = doc.name_app_doc;
        document.getElementById('Number_of_doc').value = doc.Number_of_doc; 
        document.getElementById('doc_type_name').value = doc.doc_type_name;
        document.getElementById('lumber_app_id').value = doc.lumber_app_id;
        document.getElementById('date_applied').value = doc.date_applied;
        document.getElementById('doc_status').value = doc.doc_status;
        document.getElementById('remarks').value = doc.remarks;
        
        var editModal = new bootstrap.Modal(document.getElementById('docModal'));
        editModal.show();
    }

    // Clear Modal when Adding New
    function clearForm() {
        document.getElementById('docModalLabel').innerHTML = '<i class="fa-solid fa-file-signature me-2"></i>Add New Document';
        document.getElementById('upload_id_doc').value = '';
        document.getElementById('name_app_doc').value = '';
        document.getElementById('Number_of_doc').value = ''; 
        document.getElementById('doc_type_name').value = '';
        document.getElementById('lumber_app_id').value = '';
        document.getElementById('date_applied').value = '';
        document.getElementById('doc_status').value = 'Approved';
        document.getElementById('remarks').value = '';
    }
</script>

</body>
</html>