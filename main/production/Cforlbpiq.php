<?php
$selectedRowId = $_GET['lumber_app_id'] ?? '';

require_once "../../processphp/config.php";

// Fetch Order of Payment
$sql = "SELECT * FROM order_of_payment WHERE lumber_app_id = '$selectedRowId'"; 
$result = $con->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $Amount_Decimal = $row['Amount_Decimal']; 
        $payment_transaction = $row['payment_transaction']; 
        $Serial_No = $row['Serial_No']; 
        $Address_Office_of_Payor = $row['Address_Office_of_Payor']; 
        $Name_of_Payor = $row['Name_of_Payor']; 
        $Entity_Name = $row['Entity_Name']; 
        $FundCluster = $row['FundCluster']; 
        $Payment_Status = $row['Payment_Status'];
    }
} else {
    echo "No results found for the selected ID";
    exit;
}

// Read the lumber owner details
$sql = "SELECT bussiness_name FROM lumber_application WHERE lumber_app_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $selectedRowId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$bussiness_name = "";
if ($row = mysqli_fetch_assoc($result)) {
    $bussiness_name = $row['bussiness_name'];
}
mysqli_stmt_close($stmt);

// Read the responsibility centers code 
$query = "SELECT code, acronym, description FROM reponsibilitycenters WHERE Office = ?";
$stmt = mysqli_prepare($con, $query);
$code = "";

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $Entity_Name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $code = $row['code'];
        $acronym = $row['acronym'];
        $description = $row['description'];
    }
    mysqli_stmt_close($stmt);
}

// LBP API Parameters
$trxnamt = $Amount_Decimal; 
$merchantcode = "1344"; 
$bankcode = "B000"; // LBP = B000 (Fixed)
$trxndetails = $payment_transaction; 
$trandetail1 = $Serial_No; 
$trandetail2 = $Name_of_Payor; 
$trandetail3 = "venzonanthonie@gmail.com"; 
$trandetail4 = $Address_Office_of_Payor; 
$trandetail5 = $bussiness_name; 
$trandetail6 = $FundCluster; 
$trandetail7 = $code; 
$trxndetailsblank = null;

// Checksum Calculation
$concatenatedParams = $merchantcode . $trxndetailsblank . $trandetail1; 
$username = "username";
$password = "password";
$secretKey = "N\\HWJUKFHQX"; // Secret Key for Checksum

$concatenatedParams .= $username . $password . $secretKey;
$checksumHex = strtoupper(hash('sha256', $concatenatedParams));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment Routing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .processing-card {
            background-color: #ffffff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 450px;
            width: 100%;
        }

        .processing-card h2 {
            margin-top: 0;
            color: #1a202c;
            font-size: 24px;
            font-weight: 600;
        }

        .processing-card p {
            color: #718096;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        /* Modern Spinner CSS */
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #28a745; /* LBP Green */
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            font-size: 13px;
            color: #28a745;
            font-weight: 500;
            margin-top: 20px;
        }

        .secure-badge svg {
            width: 16px;
            height: 16px;
            margin-right: 6px;
        }

        /* Hide the form completely */
        #myForm {
            display: none;
        }

        /* Custom Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none; /* Hidden by default */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-content h3 {
            margin-top: 0;
            color: #1a202c;
            font-size: 22px;
        }

        .modal-content p {
            color: #4a5568;
            font-size: 15px;
            margin-bottom: 25px;
        }

        .modal-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .modal-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

    <div class="processing-card" id="processingCard">
        <div class="spinner"></div>
        <h2>Processing Payment</h2>
        <p>Please wait while we securely verify your transaction with the Landbank Link.Biz Portal. Do not close or refresh this page.</p>
        
        <div class="secure-badge">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Secure 256-bit Connection
        </div>
    </div>

    <div id="customModal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="modalTitle">Notification</h3>
            <p id="modalMessage">Message goes here.</p>
            <button id="modalBtn" class="modal-btn">OK</button>
        </div>
    </div>

    <form id="myForm" method="post">
        <input type="hidden" name="merchantcode" value="<?php echo htmlspecialchars($merchantcode); ?>">
        <input type="hidden" name="refnum" value="<?php echo htmlspecialchars($trxndetailsblank); ?>">
        <input type="hidden" name="trandetail1" value="<?php echo htmlspecialchars($trandetail1); ?>">
        <input type="hidden" name="checksum" value="<?php echo htmlspecialchars($checksumHex); ?>">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
        <input type="hidden" name="password" value="<?php echo htmlspecialchars($password); ?>">
        <input type="hidden" name="auto_submitted" value="1">
    </form>

    <script type="text/javascript">
        // Modal Control Function
        function showModal(title, message, action) {
            // Hide the processing card
            document.getElementById('processingCard').style.display = 'none';
            
            // Set modal content
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalMessage').innerText = message;
            document.getElementById('customModal').style.display = 'flex';
            
            // Handle button click
            document.getElementById('modalBtn').onclick = function() {
                if (action === 'back') {
                    history.back();
                } else if (action) {
                    window.location.href = action;
                } else {
                    document.getElementById('customModal').style.display = 'none';
                }
            };
        }

        // Auto-submit logic only if it hasn't been submitted yet
        <?php if ($_SERVER["REQUEST_METHOD"] != "POST"): ?>
            window.onload = function() {
                setTimeout(function() {
                    document.getElementById("myForm").submit();
                }, 800);
            };
        <?php endif; ?>
    </script>

<?php
// =========================================================================
// Backend Process: Handle the POST request after the form submits to itself
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['auto_submitted'])) {
    $formData = $_POST;
    // Remove our custom flag before sending to LBP
    unset($formData['auto_submitted']);
    
    $serverUrl = 'https://www.lbp-eservices.com/linkbiz/rs/inquirestatus';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $serverUrl);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($curl);

    if ($response === false) {
        $errorMsg = addslashes(curl_error($curl));
        echo "<script>showModal('Connection Error', 'cURL Error: $errorMsg', 'back');</script>";
    } else {
        $response_parts = explode('|', $response, 3); 
        $first_part = ($response_parts[0] ?? '') . '|' . ($response_parts[1] ?? '');
        
        include '../../client/lbperror.php';
        
        if ($first_part === "00|0000") {
            // Success Logic
            $lumber_app_id = $_GET["lumber_app_id"];

            $sql = "UPDATE client_client_document_history SET Details = :Details, Title = :Title WHERE lumber_app_id = :lumber_app_id AND Title = 'Bill Collector'";
            $stmt = $connection->prepare($sql);
            $stmt->execute(array(
                ':Title' => 'Bill Collector (Successfully Paid)',
                ':Details' => 'Successfully Paid',
                ':lumber_app_id' => $lumber_app_id
            ));
            
            $referenceNumber = uniqid(); 
            $sql = "UPDATE order_of_payment SET Payment_Status = :Payment_Status, Payment_Reference_Number = :Payment_Reference_Number WHERE lumber_app_id = :lumber_app_id";
            $stmt = $connection->prepare($sql);
            $stmt->execute(array(
                ':Payment_Reference_Number' => $referenceNumber,
                ':Payment_Status' => 'Paid',
                ':lumber_app_id' => $lumber_app_id
            ));
            
            echo "<script>showModal('Payment Successful!', 'You have successfully paid for this transaction.', 'confirm_payment.php?lumber_app_id={$lumber_app_id}');</script>";
            
        } elseif ($first_part === "00|0813") {
            // Pending Logic
            echo "<script>showModal('Payment Pending', 'Your payment transaction is currently pending. Please wait...', 'back');</script>";
        } else {
            // Failed Logic
            echo "<script>showModal('Payment Failed', 'Client bills are unpaid or the transaction failed to process.', 'application.php');</script>";
        }
        exit; 
    }
    curl_close($curl);
}
?>

</body>
</html>