<?php
echo 'Checking and Redirecting . . . <br>';

require_once "../processphp/config.php";

$selectedRowId = $_GET['lumber_app_id'] ?? '';
if (empty($selectedRowId)) {
    die("Error: No Application ID provided.");
}

// ==========================================
// 1. FUNCTION DECLARATIONS
// ==========================================
function function_alert($message, $app_id) {
    $safe_message = addslashes($message);
    echo "<script type='text/javascript'>
            alert('{$safe_message}'); 
            window.location.href='doctracker.php?lumber_app_id={$app_id}';
          </script>";
    exit();
}

// ==========================================
// 2. HANDLE LBP API RESPONSE (cURL POST)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['merchantcode'])) {
    $formData = $_POST;
    $serverUrl = 'https://www.lbp-eservices.com/linkbiz/rs/inquirestatus';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $serverUrl);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    // --- SSL FIX FOR LOCALHOST ---
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    // -----------------------------

    $response = curl_exec($curl);

    if ($response === false) {
        echo 'Error: ' . curl_error($curl);
        curl_close($curl);
        exit();
    }
    
    curl_close($curl);
    
    echo '<br><br>';
    echo 'Response: ' . htmlspecialchars($response) . '<br><br>';

    $response_parts = explode('|', $response, 3);
    $first_part = ($response_parts[0] ?? '') . '|' . ($response_parts[1] ?? '');

    if(file_exists('lbperror.php')) {
        include 'lbperror.php';
    }

    if ($first_part === "00|0000") {
        // --- PAYMENT SUCCESS: UPDATE DATABASE SECURELY ---
        
        $sql_hist = "UPDATE client_client_document_history SET Details = 'Successfully Paid', Title = 'Credit  Officer (Successfully Paid)' WHERE lumber_app_id = ? AND Title = 'Credit  Officer'";
        $stmt_hist = $connection->prepare($sql_hist);
        $stmt_hist->execute([$selectedRowId]);

        $referenceNumber = uniqid(); 
        $sql_pay = "UPDATE order_of_payment SET Payment_Status = 'Paid', Payment_Reference_Number = ? WHERE lumber_app_id = ?";
        $stmt_pay = $connection->prepare($sql_pay);
        $stmt_pay->execute([$referenceNumber, $selectedRowId]);

        function_alert("You have already paid successfully!", $selectedRowId);

    } elseif ($first_part === "00|0813") {
        echo "<script type='text/javascript'>
                alert('Payment Transaction Pending Please Wait...'); 
                window.close();
              </script>";
        exit();
    } else {
        echo "<script>window.location.href='clientpayment.php?lumber_app_id=" . htmlspecialchars($selectedRowId) . "';</script>";
        exit();
    }
}


// ==========================================
// 3. FETCH DATA & PREPARE LBP REQUEST
// ==========================================

// Fetch order_of_payment
$stmt_payment = $con->prepare("SELECT * FROM order_of_payment WHERE lumber_app_id = ?");
$stmt_payment->bind_param("s", $selectedRowId);
$stmt_payment->execute();
$result_payment = $stmt_payment->get_result();

$Amount_Decimal = $payment_transaction = $Serial_No = $Address_Office_of_Payor = "";
$Name_of_Payor = $Entity_Name = $FundCluster = $Payment_Status = "";

if ($result_payment->num_rows > 0) {
    while ($row = $result_payment->fetch_assoc()) {
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
    die("No payment results found for the selected ID");
}
$stmt_payment->close();


// Fetch lumber_application details 
$bussiness_name = "";
$stmt_owner = $con->prepare("SELECT bussiness_name FROM lumber_application WHERE lumber_app_id = ?");
$stmt_owner->bind_param("s", $selectedRowId);
$stmt_owner->execute();
$result_owner = $stmt_owner->get_result();
while ($row = $result_owner->fetch_assoc()) {
    $bussiness_name = $row['bussiness_name'];
}
$stmt_owner->close();


// Fetch responsibility centers code 
$code = $acronym = $description = "";
$stmt_center = $con->prepare("SELECT code, acronym, description FROM reponsibilitycenters WHERE Office = ?");
if ($stmt_center) {
    $stmt_center->bind_param("s", $Entity_Name);
    $stmt_center->execute();
    $result_center = $stmt_center->get_result();
    
    if ($result_center->num_rows > 0) {
        $row = $result_center->fetch_assoc();
        $code = $row['code'];
        $acronym = $row['acronym'];
        $description = $row['description'];
    }
    $stmt_center->close();
}

// LBP Variables Setup
$trxnamt = $Amount_Decimal; 
$merchantcode = "1344"; 
$bankcode = "B000"; 
$trxndetails = $payment_transaction; 
$trandetail1 = $Serial_No;  
$trandetail2 = $Name_of_Payor;  
$trandetail3 = "venzonanthonie@gmail.com"; 
$trandetail4 = $Address_Office_of_Payor; 
$trandetail5 = $bussiness_name;  
$trandetail6 = $FundCluster;  
$trandetail7 = $code; 

$trxndetailsblank = null; 

// Generate Checksum
$username = "username";
$password = "password";
$secretKey = "N\\HWJUKFHQX"; 

$concatenatedParams = $merchantcode . $trxndetailsblank . $trandetail1 . $username . $password . $secretKey;
$checksumHex = strtoupper(hash('sha256', $concatenatedParams));

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transaction Details</title>
</head>
<body>

<?php if ($Payment_Status != 'Paid'): ?>
    <form id="myForm" method="post" hidden>
        <input type="text" name="merchantcode" value="<?php echo htmlspecialchars($merchantcode); ?>">
        <input type="text" name="refnum" value="<?php echo htmlspecialchars($trxndetailsblank ?? ''); ?>">
        <input type="text" name="trandetail1" value="<?php echo htmlspecialchars($trandetail1); ?>">
        <input type="text" name="checksum" value="<?php echo htmlspecialchars($checksumHex); ?>">
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
        <input type="text" name="password" value="<?php echo htmlspecialchars($password); ?>">
        <input type="submit" value="Pay">
    </form>

    <script type="text/javascript">
        window.onload = function() {
            document.getElementById("myForm").submit();
        };
    </script>
<?php else: ?>
    <div style="text-align: center; margin-top: 50px;">
        <h2>This application has already been paid.</h2>
        <button onclick="window.close();">Close Window</button>
    </div>
<?php endif; ?>

</body>
</html>