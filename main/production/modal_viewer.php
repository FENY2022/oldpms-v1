<?php 
// Start session and include config at the very top
session_start();

// Optional: Increase script execution time and memory limit for large files
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

include "../../processphp/config.php";

$nshow = $_GET['upload_id_doc'] ?? 0;
$lumber_ap_show_applicationform = '';
$lumber_app_id = '';
$doc_status = '';
$n = '';

// Fetch Document Data
if ($nshow) {
    // Using PDO/Prepared statements to prevent SQL Injection
    $lumber_app = "SELECT * FROM lumber_app_doc_erow WHERE upload_id_doc = :upload_id";
    $stmt = $connection->prepare($lumber_app); // Assuming $connection is PDO from config.php
    $stmt->execute([':upload_id' => $nshow]);
    $lumber_ap_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lumber_ap_row) {
        $lumber_ap_show_applicationform = $lumber_ap_row['name_app_doc'];
        $lumber_app_id = $lumber_ap_row['lumber_app_id'];
        $doc_status = $lumber_ap_row['doc_status'];
        $n = "../../processphp/clientupload/uploads/" . $lumber_ap_show_applicationform;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Document | O-LDPMS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Modal Customizations */
        .modal-header { background: #0c2461; color: white; border-bottom: none; padding: 15px 20px; }
        .modal-header .btn-close { filter: invert(1); opacity: 0.8; }
        .modal-header .btn-close:hover { opacity: 1; }
        .modal-body { padding: 0; overflow: hidden; }
        
        /* Split Screen Layout */
        .sidebar-controls { background: #ffffff; padding: 30px 20px; height: calc(100vh - 60px); overflow-y: auto; border-right: 1px solid #e0e0e0; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .document-viewer { background: #333; height: calc(100vh - 60px); padding: 0; display: flex; align-items: center; justify-content: center; }
        #fileViewer { width: 100%; height: 100%; border: none; background: #525659; }

        /* UI Elements */
        .status-badge { font-size: 1rem; padding: 8px 15px; border-radius: 8px; font-weight: 500; }
        .action-card { border: 1px solid #e9ecef; border-radius: 12px; padding: 20px; margin-bottom: 25px; background: #f8f9fa; }
        
        /* Custom File Upload */
        .upload-area { border: 2px dashed #4a69bd; border-radius: 10px; padding: 25px 20px; text-align: center; background: #f0f7ff; cursor: pointer; transition: all 0.3s ease; position: relative; }
        .upload-area:hover { background: #e0efff; border-color: #0c2461; }
        .upload-area i { font-size: 30px; color: #4a69bd; margin-bottom: 10px; }
        .upload-area input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        
        /* Toasts */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 1060; }
    </style>
</head>
<body>

<div class="toast-container"></div>

<div class="modal fade show" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">
                    <i class="fas fa-file-signature me-2"></i> Document Viewer - O-LDPMS
                </h5>
                <button type="button" class="btn-close" onclick="closeModal()" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row g-0">
                    
                    <div class="col-lg-4 col-xl-3 sidebar-controls">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="m-0 text-dark">Status</h4>
                            <?php if ($doc_status == 'Approved'): ?>
                                <span class="badge bg-success status-badge"><i class="fas fa-check-circle me-1"></i> Approved</span>
                            <?php elseif ($doc_status == 'Disapproved'): ?>
                                <span class="badge bg-danger status-badge"><i class="fas fa-times-circle me-1"></i> Disapproved</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark status-badge"><i class="fas fa-clock me-1"></i> Pending</span>
                            <?php endif; ?>
                        </div>

                        <div class="action-card">
                            <h6 class="text-muted mb-3 text-uppercase fw-bold">Update File</h6>
                            <form id="uploadForm" enctype="multipart/form-data">
                                <input type="hidden" id="file_name" name="file_name" value="<?php echo htmlspecialchars($lumber_ap_show_applicationform); ?>">
                                
                                <div class="upload-area mb-3">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p class="m-0 fw-bold text-dark" id="fileNameDisplay">Click or Drag file to replace</p>
                                    <p class="text-muted small m-0">PDF, JPG, PNG allowed (Max 50MB)</p>
                                    <input type="file" id="fileInput" name="file" accept=".pdf,.jpg,.jpeg,.png" required onchange="updateFileName(this)">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                                        <i class="fas fa-save me-2"></i> Upload & Replace
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="d-grid mt-4">
                            <button class="btn btn-secondary" onclick="closeModal()">
                                <i class="fas fa-arrow-left me-2"></i> Back to List
                            </button>
                        </div>

                    </div>
                    
                    <div class="col-lg-8 col-xl-9 document-viewer">
                        <?php if(!empty($n) && file_exists(__DIR__ . "/" . $n)): ?>
                            <embed id="fileViewer" src="<?php echo htmlspecialchars($n); ?>?t=<?php echo time(); ?>" type="application/pdf"></embed>
                        <?php else: ?>
                            <embed id="fileViewer" src="<?php echo htmlspecialchars($n); ?>" type="application/pdf"></embed>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // --- UI Interactions ---
    function closeModal() {
        history.back(); // Go back to the previous page
    }

    // Show filename and check file size when selected
    function updateFileName(input) {
        const display = document.getElementById('fileNameDisplay');
        const maxFileSize = 50 * 1024 * 1024; // 50MB in bytes

        if (input.files && input.files[0]) {
            const fileSize = input.files[0].size;
            
            if (fileSize > maxFileSize) {
                showToast('error', 'File is too large! Maximum allowed size is 50MB.');
                input.value = ''; // Clear the input
                display.textContent = "Click or Drag file to replace";
                display.classList.remove('text-primary');
                return;
            }

            display.textContent = input.files[0].name;
            display.classList.add('text-primary');
        } else {
            display.textContent = "Click or Drag file to replace";
            display.classList.remove('text-primary');
        }
    }

    // --- Dynamic Toast System ---
    function showToast(type, message) {
        const toastContainer = document.querySelector('.toast-container');
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${icon} me-2"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Auto remove toast after 4 seconds
        const toasts = toastContainer.querySelectorAll('.toast');
        const latestToast = toasts[toasts.length - 1];
        setTimeout(() => {
            latestToast.classList.remove('show');
            setTimeout(() => latestToast.remove(), 300);
        }, 4000);
    }

    // --- AJAX File Upload Logic ---
    const form = document.getElementById('uploadForm');
    const uploadBtn = document.getElementById('uploadBtn');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const fileInput = document.getElementById('fileInput');
        if (fileInput.files.length === 0) {
            showToast('error', 'Please select a file to upload.');
            return;
        }

        // Disable button & show loading state
        const originalBtnText = uploadBtn.innerHTML;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Uploading...';
        uploadBtn.disabled = true;

        const formData = new FormData(form);

        try {
            // Check network connection first
            if (!navigator.onLine) {
                throw new Error("No internet connection.");
            }

            const response = await fetch('updatefile.php', {
                method: 'POST',
                body: formData
            });

            // Catch error before parsing JSON
            if (!response.ok) {
                // If it throws a 413 error, the file exceeded PHP's post_max_size
                if(response.status === 413) {
                    throw new Error("File exceeds the server's maximum upload limit.");
                }
                throw new Error(`Server error: ${response.status} ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.status === 'success' || result.message.toLowerCase().includes('success')) {
                showToast('success', result.message || 'File uploaded successfully!');
                
                // Refresh the embedded viewer to show the new file
                const fileViewer = document.getElementById('fileViewer');
                fileViewer.src = fileViewer.src.split('?')[0] + '?t=' + new Date().getTime();
            } else {
                showToast('error', result.message || 'Upload failed. Please try again.');
            }

        } catch (error) {
            console.error("Upload Error:", error);
            showToast('error', 'Upload failed: ' + error.message);
        } finally {
            // Reset button state
            uploadBtn.innerHTML = originalBtnText;
            uploadBtn.disabled = false;
        }
    });

    // Auto-refresh embed to prevent cache issues on load
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const fileViewer = document.getElementById('fileViewer');
            if(fileViewer.src) {
                fileViewer.src = fileViewer.src.split('?')[0] + '?t=' + new Date().getTime();
            }
        }, 500);
    });
</script>

</body>
</html>