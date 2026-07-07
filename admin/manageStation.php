<?php


// require_once('configmysqli.php');
session_start();
include('../processphp/config.php');
// block if no log in 
          if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            // if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){

              header("location: login.php");
              exit;
            }
            else{

         
            }

     




              $userid = $_SESSION["user_id"] ;

              $lumber_app = "SELECT * FROM denr_users where user_id = $userid";
              $lumber_app_qry = mysqli_query($con, $lumber_app);
              $lumber_ap_row = mysqli_fetch_assoc($lumber_app_qry);


               $clientname = $lumber_ap_row['name'] ;

              
               $_SESSION['clientname'] = $clientname ;

               
               $user_role = $lumber_ap_row['user_role_id'] ;

               if ($user_role != '99') {
                 header('Location: prc_logout.php');
                 exit;
               }







?> 







  













<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OLDPMS Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/css/bootstrap.min.css">
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/js/bootstrap.bundle.min.js"></script>

  <!-- Custom fonts for this template-->
    <!-- Bootstrap -->
  
    <link href="vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="vendors/fontawesome-free-6.2.0-web/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- NProgress -->
    <link href="vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- bootstrap-daterangepicker -->
    <link href="vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

  <!-- Custom Theme Style -->
    <link href="build/css/custom.css" rel="stylesheet">

    <style>
      body.nav-md .container.body {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
      }

      body.nav-md .container.body .main_container {
        width: 100% !important;
      }

      body.nav-md .container.body .col-md-3.left_col {
        width: 230px !important;
        min-height: 100vh !important;
        height: 100vh !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 1000 !important;
        overflow-y: auto !important;
        background: #22782c !important;
      }

      body.nav-md .container.body .col-md-3.left_col .left_col {
        width: 230px !important;
        min-height: 100vh !important;
        background: #22782c !important;
      }

      body.nav-md .container.body .col-md-3.left_col #sidebar-menu {
        padding-top: 10px !important;
      }

      body.nav-md .container.body .col-md-3.left_col .nav.side-menu > li > a {
        color: #fff !important;
        display: block !important;
        padding: 12px 18px !important;
        font-weight: 600 !important;
      }

      body.nav-md .container.body .col-md-3.left_col .nav.side-menu > li > a:hover,
      body.nav-md .container.body .col-md-3.left_col .nav.side-menu > li.active > a {
        background: rgba(255, 255, 255, 0.12) !important;
      }

      body.nav-md .container.body .col-md-3.left_col .nav.side-menu li a i,
      body.nav-md .container.body .col-md-3.left_col .nav.side-menu li a span,
      body.nav-md .container.body .col-md-3.left_col .nav.child_menu li a {
        color: #fff !important;
      }

      body.nav-md .container.body .right_col {
        width: calc(100% - 230px) !important;
        margin-left: 230px !important;
        padding-bottom: 180px !important;
      }

      body.nav-md .container.body .top_nav {
        width: calc(100% - 230px) !important;
        margin-left: 230px !important;
      }

      #userModal {
        z-index: 5000 !important;
      }

      @media (max-width: 991px) {
        body.nav-md .container.body .right_col,
        body.nav-md .container.body .top_nav {
          width: 100% !important;
          margin-left: 0 !important;
        }

        body.footer_fixed footer {
          left: 0 !important;
          width: 100% !important;
        }
      }

      body.footer_fixed footer {
        position: fixed !important;
        left: 230px !important;
        bottom: 0 !important;
        width: calc(100% - 230px) !important;
        margin-left: 0 !important;
        z-index: 900 !important;
      }
    </style>

</head>

<body class="nav-md bg-slate-100 footer_fixed">
    <div class="container body">
      <div class="main_container">
      
    <!-- sidebar navigation -->        
      <?php
        require_once('adminsidebar.php');
      ?>        
    <!-- /sidebar navigation -->
      
        <!-- top navigation -->
      
      <?php
        require_once('adminnavbar.php');
      ?> 
      
        <!-- /top navigation -->

<!-- page content -->
      <div class="right_col" role="main">
        <div class="w-full px-4 py-8 sm:px-6 lg:px-8">
          <div class="mb-8 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 px-6 py-5 text-white shadow-xl shadow-slate-900/20">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-300">Admin Station</p>
            <h1 class="mt-1 text-2xl font-semibold">Manage Stations</h1>
            <p class="mt-1 text-sm text-slate-300">Select an office, then open its users in a modal iframe.</p>
          </div>

<form method="post" action="" id="officeForm" class="mb-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 ring-1 ring-slate-200">
    <label for="Office_id" class="mb-3 block text-sm font-semibold uppercase tracking-wide text-slate-600">Select an office</label>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
    <select name="Office" id="Office_id" class="block w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:bg-white focus:ring-4 focus:ring-sky-100 sm:flex-1">
        <?php




        // Check if connection is successful
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        }

        // SQL query to retrieve office names
        $sql = "SELECT Office_id, station, code FROM office";

        // Prepare and execute SQL query
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        // Initialize an array to store unique office names
        $unique_offices = array();

        // Get the last selected value from the form submission
        $last_selected_value = isset($_POST['Office']) ? $_POST['Office'] : '';

        // Check if there are rows in the result
        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                // Check if the office name already exists in the array
                if (!in_array($row['station'], $unique_offices)) {
                    $selected = ($row['Office_id'] == $last_selected_value) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['code']) . "' $selected>" . htmlspecialchars($row['station']) . "</option>";
                    // Add the office name to the array
                    $unique_offices[] = $row['station'];
                }
            }
        } else {
            echo "<option value='' disabled selected>No offices found</option>";
        }

        ?>
    </select>
    <button type="submit" data-loading-button="true" data-loading-text="Loading..." class="inline-flex items-center justify-center rounded-2xl bg-sky-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-600/25 transition hover:bg-sky-700 focus:outline-none focus:ring-4 focus:ring-sky-200" value="Submit">Select</button>
    </div>


</form>

<script>
    // Retain selected option after form submission
    document.addEventListener("DOMContentLoaded", function() {
        var officeForm = document.getElementById('officeForm');
        var selectElement = officeForm.elements['Office'];
        var lastSelectedValue = "<?php echo $last_selected_value; ?>";
        var officeSubmitButton = officeForm.querySelector('[data-loading-button="true"]');
        var currentViewButton = null;
        var userFrame = document.getElementById('userFrame');

        function setLoadingState(button, loadingText) {
            if (!button) {
                return;
            }

            if (!button.dataset.originalLabel) {
                button.dataset.originalLabel = button.innerHTML;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.innerHTML = '<span class="inline-flex items-center gap-2"><span class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span><span>' + loadingText + '</span></span>';
        }

        function clearLoadingState(button) {
            if (!button) {
                return;
            }

            if (button.dataset.originalLabel) {
                button.innerHTML = button.dataset.originalLabel;
                delete button.dataset.originalLabel;
            }

            button.disabled = false;
            button.removeAttribute('aria-busy');
        }

        for (var i = 0; i < selectElement.options.length; i++) {
            if (selectElement.options[i].value === lastSelectedValue) {
                selectElement.selectedIndex = i;
                break;
            }
        }

        officeForm.addEventListener('submit', function () {
            setLoadingState(officeSubmitButton, officeSubmitButton ? (officeSubmitButton.getAttribute('data-loading-text') || 'Loading...') : 'Loading...');
        });

        if (userFrame) {
            userFrame.addEventListener('load', function () {
                clearLoadingState(currentViewButton);
                currentViewButton = null;
            });
        }

        document.querySelectorAll('[data-view-users]').forEach(function (button) {
            button.addEventListener('click', function () {
                currentViewButton = button;
                setLoadingState(button, button.getAttribute('data-loading-text') || 'Loading...');
            });
        });
    });
</script>

<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if Office is set and not empty
    if (isset($_POST['Office']) && !empty($_POST['Office'])) {

        

        // Prepare SQL query
        $sql = "SELECT DISTINCT office_id, office_name, station, code FROM office WHERE code = ?";


        // Prepare and execute SQL query
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $_POST['Office']);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if there are rows in the result
        if ($result->num_rows > 0) {
            // Output table header
            echo "<div class='overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60 ring-1 ring-slate-200'>";
            echo "<div class='border-b border-slate-200 px-6 py-4'>";
            echo "<h2 class='text-lg font-semibold text-slate-900'>Office Users</h2>";
            echo "<p class='text-sm text-slate-500'>Click View Users to open the office roster in a modal iframe.</p>";
            echo "</div>";
            echo "<div class='overflow-x-auto'>";
            echo "<table class='min-w-full divide-y divide-slate-200'>";
            echo "<thead class='bg-slate-50'><tr>
            
            <th class='px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500'>ID</th>
            <th class='px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500'>Office Name</th>
            <th class='px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500'>Station</th>
            <th class='px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500'>Action</th>
            </tr></thead><tbody class='divide-y divide-slate-100 bg-white'>";
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                $officeId = htmlspecialchars($row['office_id']);
                $officeName = htmlspecialchars($row['office_name']);
                $stationName = htmlspecialchars($row['station']);

                echo "<tr class='transition hover:bg-slate-50'><td class='px-6 py-4 text-sm text-slate-700'>" . $officeId . "</td>
                
                <td class='px-6 py-4 text-sm font-medium text-slate-900'>" . $officeName . "</td>
                
                <td class='px-6 py-4 text-sm text-slate-700'>" . $stationName . "</td>
                
                <td class='px-6 py-4'>
                    <button type='button' class='inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-slate-900/20 transition hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200' data-view-users data-office-id='" . $officeId . "' data-office-name='" . $officeName . "'>View Users</button>
                </td>

                </tr>";
            }
            echo "</tbody></table></div></div>";
        } else {
            echo "<div class='rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-slate-500'>0 results</div>";
        }

        // Close prepared statement
        $stmt->close();
    } else {
        echo "Please select an office.";
    }
}



// Close database connection
$con->close();
?>

<div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 px-2 py-3 backdrop-blur-sm sm:px-4 sm:py-6">
  <div class="flex h-[95vh] w-[98vw] max-w-[98vw] flex-col overflow-hidden rounded-3xl bg-white shadow-2xl shadow-slate-950/30 ring-1 ring-black/5 sm:w-[96vw] sm:max-w-[96vw]">
    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-600">User View</p>
        <h3 class="mt-1 text-xl font-semibold text-slate-900">Office Users</h3>
        <p id="modalSubtitle" class="mt-1 text-sm text-slate-500"></p>
      </div>
      <button type="button" id="closeModalBtn" class="rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" aria-label="Close modal">&times;</button>
    </div>
    <div class="flex-1 bg-slate-50 p-3 sm:p-4">
      <iframe id="userFrame" title="Office users" class="h-full w-full rounded-2xl border border-slate-200 bg-white" src="about:blank"></iframe>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('userModal');
        const iframe = document.getElementById('userFrame');
        const subtitle = document.getElementById('modalSubtitle');
        const closeBtn = document.getElementById('closeModalBtn');

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            iframe.src = 'about:blank';
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('[data-view-users]').forEach(function (button) {
            button.addEventListener('click', function () {
                const officeId = button.getAttribute('data-office-id');
                const officeName = button.getAttribute('data-office-name') || 'Office';

                subtitle.textContent = officeName + ' | Office ID: ' + officeId;
                iframe.src = 'viewaccount.php?office_id=' + encodeURIComponent(officeId) + '&embed=1';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            });
        });

        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    });
</script>
      </div>
    </div>
  </div>
</div>
<?php
// // Check if form is submitted
// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Check if Office is set and not empty
//     if (isset($_POST['Office']) && !empty($_POST['Office'])) {



//         // Prepare SQL query
//         $sql = "SELECT DISTINCT name, contact_no FROM denr_users WHERE office_id = ?";


//         // Prepare and execute SQL query
//         $stmt = $con->prepare($sql);
//         $stmt->bind_param("i", $_POST['Office']);
//         $stmt->execute();
//         $result = $stmt->get_result();

//         // Check if there are rows in the result
//         if ($result->num_rows > 0) {
//             // Output table header
//             echo "<table class='dataTable'>";
//             echo "<thead><tr><th>Name</th><th>Contact No</th></tr></thead><tbody>";
//             // Output data of each row
//             while ($row = $result->fetch_assoc()) {
//                 echo "<tr><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row['contact_no']) . "</td></tr>";
//             }
//             echo "</tbody></table>";
//         } else {
//             echo "0 results";
//         }

//         // Close prepared statement
//         $stmt->close();
//     } else {
//         echo "Please select an office.";
//     }
// }

// // Close database connection
// $con->close();
?>


<?php 
      require_once 'adminfooter.php';
  ?>

</body>
</html>

