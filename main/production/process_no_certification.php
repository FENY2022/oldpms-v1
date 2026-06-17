<?php

include('../../processphp/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_no_cert'])) {
    $lumber_app_id = $_POST['lumber_app_id'];
    $remarks = $_POST['remarks'] ?? '';

    // Validate and process the ID
    if (!empty($lumber_app_id)) {

        // Define the new status and flow state
        $stat_uss = 'For Endorsement';
        $Flow_stats = '6.3';
        $Number_of_doc = '12';
        $doc_type_name = 'No Certification';

        try {
            // Handle file upload
            $uploaded_file_name = '';
            if (isset($_FILES['supporting_doc']) && $_FILES['supporting_doc']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "../../processphp/clientupload/uploads/";
                $file_ext = pathinfo($_FILES['supporting_doc']['name'], PATHINFO_EXTENSION);
                $uploaded_file_name = "PDF-" . uniqid() . "." . $file_ext;
                $target_file = $target_dir . $uploaded_file_name;

                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                move_uploaded_file($_FILES['supporting_doc']['tmp_name'], $target_file);
            }

            // Insert document record
            $date = date('m/d/y');
            $doc_status = 'For Review (CG)';

            $query = $connection->prepare("INSERT INTO lumber_app_doc_erow(
                lumber_app_id,
                doc_type_name,
                date_applied,
                Number_of_doc,
                doc_status,
                name_app_doc
            ) VALUES (
                :lumber_app_id,
                :doc_type_name,
                :date_applied,
                :Number_of_doc,
                :doc_status,
                :name_app_doc
            )");
            $query->bindParam("lumber_app_id", $lumber_app_id, PDO::PARAM_STR);
            $query->bindParam("doc_type_name", $doc_type_name, PDO::PARAM_STR);
            $query->bindParam("date_applied", $date, PDO::PARAM_STR);
            $query->bindParam("Number_of_doc", $Number_of_doc, PDO::PARAM_STR);
            $query->bindParam("doc_status", $doc_status, PDO::PARAM_STR);
            $query->bindParam("name_app_doc", $uploaded_file_name, PDO::PARAM_STR);
            $query->execute();

            // Update lumber_application
            $sql = "UPDATE lumber_application 
                    SET Status = :Status, Flow_stat = :Flow_stat, Remarks = :Remarks 
                    WHERE lumber_app_id = :lumber_app_id";
            $stmt = $connection->prepare($sql);
            $stmt->execute(array(
                ':Status' => $stat_uss,
                ':Flow_stat' => $Flow_stats,
                ':Remarks' => $remarks,
                ':lumber_app_id' => $lumber_app_id
            ));

            if ($stmt->rowCount() > 0) {
                echo "
                <script>
                    alert('No Certification confirmed for application ID: $lumber_app_id');
                    window.location.href = 'application.php';
                </script>
                ";
            } else {
                echo "
                <script>
                    alert('No changes were made to the application.');
                    window.location.href = 'application.php';
                </script>
                ";
            }
        } catch (Exception $e) {
            echo "
            <script>
                alert('An error occurred while updating the application. Please try again.');
                window.location.href = 'application.php';
            </script>
            ";
        }
    } else {
        echo "
        <script>
            alert('Invalid application ID.');
            window.location.href = 'application.php';
        </script>
        ";
    }
}
?>
