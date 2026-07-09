<?php 

require_once __DIR__ . '/doc_upload_helpers.php';

//  if (isset($_POST['[Submit]'])) {



	
	// echo "<pre>";
	// print_r($_FILES['my_image2']);
	// echo "</pre>";

	$img_name = $_FILES['my_image2']['name'];
	$img_size = $_FILES['my_image2']['size'];
	$tmp_name = $_FILES['my_image2']['tmp_name'];
	$error = $_FILES['my_image2']['error'];

	if ($error === 0) {
		if ($img_size > 30 * 1024 * 1024) {
			$em = "Sorry, Document number 1 file is too large.";
		    header("Location: ../univmodal.php?error=$em");
		}else {
			$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
			$img_ex_lc = strtolower($img_ex);

			$allowed_exs = array("jpg", "jpeg", "png", "pdf"); 

			if (in_array($img_ex_lc, $allowed_exs)) {
				$new_img_name2 = uniqid("PDF-", true).'.'.$img_ex_lc;
				$img_upload_path = '../../processphp/clientupload/uploads/'.$new_img_name2;
				move_uploaded_file($tmp_name, $img_upload_path);

				// Insert into Database

				// $sql = "INSERT INTO images(image_url) 
				        // VALUES('$new_img_name')";
				// mysqli_query($conn, $sql);
				// header("Location: view.php");

	




				$For_Review = 'For Review' ;
				$date =  date("d/m/Y") ; 
				$doc_app_ind = '0';
				$doc_type_name = 'Uploaded Verification Report' ;
				$Number_of_doc = '8';
				upsert_lumber_app_doc_row(
					$connection,
					$l_id,
					$Number_of_doc,
					$doc_type_name,
					$new_img_name2,
					$For_Review,
					$doc_app_ind,
					$date,
					$uniqid_lap
				);
				






			}else {
				$em = "You can't upload files of this type on document number 1";
		        header("Location: ../univmodal.php?error=$em");
			}
		}

	}

	
?>
