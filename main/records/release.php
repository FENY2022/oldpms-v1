<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../processphp/config.php";
require_once "../mail/send_release_email.php";

header('Content-Type: application/json; charset=UTF-8');

function jsonReleaseResponse($success, $message)
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonReleaseResponse(false, 'Invalid request method.');
}

if (empty($_SESSION['user_id'])) {
    jsonReleaseResponse(false, 'Unauthorized access.');
}

$lumber_app_id = filter_input(INPUT_POST, 'lumber_app_id', FILTER_VALIDATE_INT);
if (!$lumber_app_id) {
    jsonReleaseResponse(false, 'Invalid lumber application ID.');
}

$recipientStmt = $connection->prepare("SELECT CONCAT(uc.firstname, ' ', uc.mid_name, ' ', uc.lastname) AS client_name, uc.email, la.bussiness_name
    FROM lumber_application la
    INNER JOIN user_client uc ON uc.client_id = la.client_id
    WHERE la.lumber_app_id = :lumber_app_id
    LIMIT 1");
$recipientStmt->execute([':lumber_app_id' => $lumber_app_id]);
$recipientRow = $recipientStmt->fetch(PDO::FETCH_ASSOC);
$recipientEmail = $recipientRow['email'] ?? '';
$recipientName = trim(preg_replace('/\s+/', ' ', $recipientRow['client_name'] ?? 'Client'));
$businessName = $recipientRow['bussiness_name'] ?? '';

if (empty($recipientEmail)) {
    jsonReleaseResponse(false, 'No client email found for this application.');
}

try {
    sendReleaseEmail($recipientEmail, $recipientName);
} catch (Throwable $e) {
    jsonReleaseResponse(false, 'Email sending failed for ' . $recipientEmail . ': ' . $e->getMessage());
}




$date2 = date('m/d/y');

function getFullMonthNameFromDate($date3)
{
        $monthName = date('F d, Y', strtotime($date3));
        return $monthName;
}


//  $date = $row['date_recieve'] ;
$date3 = $date2;
getFullMonthNameFromDate($date3);




date_default_timezone_set("Asia/Manila");
$Time = date("h:i:sa");



$Title = 'Records Unit';
$Details = 'Released the approved Lumber Dealer E-Permit, Memorandum to the concerned PENROs and CENROs and the acknowledgment letter for the applicant.';


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
$query2->bindParam("lumber_app_id", $lumber_app_id, PDO::PARAM_STR);
$query2->bindParam("Date", $date2, PDO::PARAM_STR);
$query2->bindParam("Title", $Title, PDO::PARAM_STR);
$query2->bindParam("Details", $Details, PDO::PARAM_STR);
$query2->bindParam("Time", $Time, PDO::PARAM_STR);


$result2 = $query2->execute();




$Title = 'Client';
$Details = 'E-Permit is now available for download' . '<br><br>' . '

Note: Kindly share your time to accomplish the Client Satisfaction Survey (CSS) for us to further improve our services to you.
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
$query2->bindParam("lumber_app_id", $lumber_app_id, PDO::PARAM_STR);
$query2->bindParam("Date", $date2, PDO::PARAM_STR);
$query2->bindParam("Title", $Title, PDO::PARAM_STR);
$query2->bindParam("Details", $Details, PDO::PARAM_STR);
$query2->bindParam("Time", $Time, PDO::PARAM_STR);


$result2 = $query2->execute();



try {
    // Prepare the SQL query with named placeholders
    $sql = "UPDATE lumber_application 
            SET Application_status = :Application_status, Flow_stat = :Flow_stat
            WHERE lumber_app_id = :lumber_app_id";

    // Prepare the statement
    $stmt = $connection->prepare($sql);

    // Bind the parameters and execute the query
    $stmt->execute(array(
        ':Application_status' => 'Complete',
        ':Flow_stat' => 'Complete',
        ':lumber_app_id' => $lumber_app_id
    ));

    jsonReleaseResponse(true, 'Application successfully released !');
} catch (PDOException $e) {
    jsonReleaseResponse(false, 'Error updating application: ' . $e->getMessage());
}
?>
