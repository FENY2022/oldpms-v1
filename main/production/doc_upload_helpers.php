<?php

if (!function_exists('upsert_lumber_app_doc_row')) {
    function upsert_lumber_app_doc_row(PDO $connection, $lumber_app_id, $number_of_doc, $doc_type_name, $new_file_name, $doc_status, $doc_app_ind, $date_applied, $uniqid_lapp)
    {
        $select = $connection->prepare("SELECT upload_id_doc, name_app_doc FROM lumber_app_doc_erow WHERE lumber_app_id = :lumber_app_id AND Number_of_doc = :number_of_doc ORDER BY upload_id_doc DESC LIMIT 1");
        $select->execute([
            ':lumber_app_id' => $lumber_app_id,
            ':number_of_doc' => $number_of_doc,
        ]);

        $existing = $select->fetch(PDO::FETCH_ASSOC);
        $uploadDir = __DIR__ . '/../../processphp/clientupload/uploads/';

        if ($existing) {
            $oldFile = $existing['name_app_doc'] ?? '';
            if ($oldFile && $oldFile !== $new_file_name) {
                $oldPath = $uploadDir . basename($oldFile);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $update = $connection->prepare("UPDATE lumber_app_doc_erow SET name_app_doc = :name_app_doc, doc_type_name = :doc_type_name, date_applied = :date_applied, doc_status = :doc_status, doc_app_ind = :doc_app_ind, uniqid_lapp = :uniqid_lapp WHERE upload_id_doc = :upload_id_doc");
            $update->execute([
                ':name_app_doc' => $new_file_name,
                ':doc_type_name' => $doc_type_name,
                ':date_applied' => $date_applied,
                ':doc_status' => $doc_status,
                ':doc_app_ind' => $doc_app_ind,
                ':uniqid_lapp' => $uniqid_lapp,
                ':upload_id_doc' => $existing['upload_id_doc'],
            ]);

            return 'updated';
        }

        $insert = $connection->prepare("INSERT INTO lumber_app_doc_erow (lumber_app_id, name_app_doc, doc_type_name, date_applied, doc_status, doc_app_ind, Number_of_doc, uniqid_lapp) VALUES (:lumber_app_id, :name_app_doc, :doc_type_name, :date_applied, :doc_status, :doc_app_ind, :number_of_doc, :uniqid_lapp)");
        $insert->execute([
            ':lumber_app_id' => $lumber_app_id,
            ':name_app_doc' => $new_file_name,
            ':doc_type_name' => $doc_type_name,
            ':date_applied' => $date_applied,
            ':doc_status' => $doc_status,
            ':doc_app_ind' => $doc_app_ind,
            ':number_of_doc' => $number_of_doc,
            ':uniqid_lapp' => $uniqid_lapp,
        ]);

        return 'inserted';
    }
}
