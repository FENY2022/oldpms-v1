<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://unpkg.com/gutenberg-css@0.6">
    <style>
		@page {
			size: legal landscape;
			margin: 10mm;
		}
		body {
			  width: 100%;
			  font-family: Arial, sans-serif;
			  font-size: 14px;
			  background-image: url('backdropseal.png');
			  background-repeat: no-repeat;
			  background-attachment: fixed;
			  background-size: 600px;
			  background-position: 50% 50%; 
			  -webkit-background-size: cover; 
			  -moz-background-size: cover; 
			  -o-background-size: cover;
			}
		.bordered {
			border: 2px solid #000;
			padding: 8px;
		}
        table {
            width: 100%;
        }
        footer {
			text-color: gray;
			font-size: 8pt;
            text-align: center;
            font-style: italic;
			padding-top : 5pt;
			line-height: 0.5;
        }
        .indented {
			padding-top : -500pt;
			padding-bottom : -500pt;
			padding-left :-500pt;
			padding-right : -500pt;
        }
		table.center {
  			margin-left: auto; 
  			margin-right: auto;
		}

		#example2 {
    /* sig source */
    /* background: url(img/RED.png); */
    background-repeat: no-repeat;
    background-size: 90px 90px;
    background-position: 82% 20%
}



    </style>
 
</head>
<body class="indented">
<div class="bordered">
	<table>
		<tr>
			<td style="width: 10%;"></td>
			<td style="width: 20%; text-align: right"><img src="seal3.png" style="max-width: 70px; vertical-align: middle;" /></td>
			<td style="width: 50%;">
    			<p style="width:100%; text-align: center; line-height: 14px; font-size: 14px; vertical-align: middle;">REPUBLIC OF THE PHILIPPINES </br>
												<STRONG>DEPARTMENT OF ENVIRONMENT AND NATURAL RESOURCES</STRONG> </br>
														CARAGA REGION, AMBAGO, BUTUAN CITY 
				</p>
			</td>			
			<td style="width: 5%; text-align: left"><img src="bagongpilipinas.png" style="max-width: 70px; vertical-align: middle;" /></td>
			<td style="width: 25%;"></td>
		</tr>
    </table>
			 
		 <h1 style="width:100%;text-align: center; line-height: 0px; font-size: 18px;"><strong>CERTIFICATE OF REGISTRATION</strong></h1>
		 <p style="width:100%;text-align: center; font-size: 14px; line-height: 1px">Registration No. <strong><u><?php echo $regnumber ; ?> (<?php echo $status; ?>)</u></strong></p>
	
		 <p style="width:100%;text-align: center; line-height: 1px; font-size: 14px">This is to certify that,</p>
		 
		 <h2 style="width:100%;text-align: center; text-transform: uppercase; line-height: 1px; font-size: 16px;"><u><strong><?php echo $ldname; ?></strong></strong></u></h2>
		 
		 <p style="width:100%;text-align: center; line-height: 1px; font-size: 14px">(Business Name)</p>
		 
		 <p style="width:100%;text-align: center; font-size: 14px">Represented by its Owner, Mr./Ms. <u><strong><?php echo $owner; ?></strong></u>, a Filipino citizen of <u><strong><?php echo $ldaddress; ?></strong></u> that has been registered in this</p>
		
		 <p style="width:100%;text-align: center; font-size: 14px; line-height: 1px;">Office as</p>
		
		 <h2 style="width:100%;text-align: center; line-height: 1px; font-size: 16px;"><strong><u>LUMBER DEALER</u></strong></h2>
		 
		 <p style="width:100%;text-align: center; line-height: 1px; font-size: 14px">(Dealer in lumber/or operator of lumber yard)</p> <br><br>
		
	
	 <dd class="col-sm-9" style="font-size: 14px; text-indent: 2em;"> Pursuant to the pertinent provisions of PD 705, as amended, in accordance with the provisions of Republic Act No. 1239, and the Regulations promulgated thereto, subject to the terms and conditions enumerated in the succeeding pages (marked as Annex A), and such other additional regulation which may herein after be prescribed. The registrant has lumber supply contract(s) with the following:</dd>
	<table border=0 class="center" style="width:70%; font-size: 14px">
        <thead>
        <tr class="heading">
			<th style="font-size: 14px">SUPPLIERS </br> NAME/COMPANY</th>
			<th style="font-size: 14px">VOLUME FOR DISPOSITION </br> (BD.FT.)</th>
			

        </tr>
        </thead>
        <tbody>

<?php
// Use prepared statement for safety (recommended)
$stmt = $connection->prepare("SELECT ownername, bname, Species, other, result, validity_val 
                              FROM supp_contdetails 
                              WHERE lumber_app_id = ?");
$stmt->execute([$lumber_app_id]);

$totalResult = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $totalResult += $row['result'];

    echo '<tr class="item">';
    echo '<td style="text-align: center">' . htmlspecialchars($row['ownername']) . '</td>';
    echo '<td style="text-align: center">' . htmlspecialchars($row['Species'] . ' ' . $row['other']) . '</td>';
    echo '<td style="text-align: center">Chainsaw Cut</td>';
    echo '<td style="text-align: center">' . htmlspecialchars($row['validity_val']) . '</td>';
    echo '<td style="text-align: center">' . htmlspecialchars($row['result']) . '</td>';
    echo '</tr>';
}

// Total row
echo '<tr class="total">';
echo '<td style="text-align: right"><strong></strong></td>';
echo '<td style="text-align: center"><strong></strong></td>';
echo '<td style="text-align: center"><strong></strong></td>';
echo '<td style="text-align: center"><strong>Total:</strong></td>';
echo '<td style="text-align: center"><strong>' . $totalResult . '</strong></td>';
echo '</tr>';
?>


        </tbody>


<br><br>

    </table>
	</br>
	<dd class="col-sm-9" style="text-align:justify; font-size: 14px; text-indent: 2em;">The place of its/his/her business operation is in <u><strong><?php echo $ldaddress; ?></strong></u>. This Certificate of Registration is non-negotiable and non-transferable and, unless sooner terminated, will expire on <u><strong>{{dateexpiry}}</strong></u>.</dd>
	</br>
	<dd class="col-sm-9" style="text-align:justify; font-size: 14px; text-indent: 2em;">Issued on <u><strong>{{dateissued}}</strong></u> at <u><strong>DENR-13, Ambago, Butuan City, Agusan del Norte, Philippines</strong></u>.</dd>
	</br>
	<div id="example2">	
	<table border=0 class="center" style="width:90%">
		<tr>
			<td style="width:15%"> <p style="font-size: 10px">Bond (Cash) No. </br> Date:</p> </td>
			<td style="width:15%"> <p style="font-size: 10px"><u>PHP <?php echo $cashbond ; ?>.00 </u> </br> <u><?php echo $datepaid; ?></u></p> </td>
			<td style="width:5%"></td>
			<td style="width:65%"></td>
		</tr>
	</table>

	<table border=0 class="center" style="width:90%">
		<tr>
			<td style="width:15%"> <p style="font-size: 10px"> Application Fee: </br>
					 O.R. No. </br>
					 Date:
				</p>
		    </td>
			<td style="width:15%"> <p style="font-size: 10px"> PHP 600.00 </br>
		       	     <?php echo $refnumber; ?> 
						</br>
					 <?php echo $datepaid; ?> 
				</p>
			</td>
			<td style="width:10px"></td>
			<td style="width:15%"> 
				<p style="font-size: 12px"> Registration Fee: </br>
					 O.R. No. </br>
					 Date:
				 </p>
			</td>
			<td style="width:15%"> <p style="font-size: 10px"> PHP 480.00 </br>
					 <?php echo $refnumber; ?> 
						</br>
					 <?php echo $datepaid; ?> 
				</p>
			</td>

			<!-- <td style="width:35%"> <p align="right" style="padding-right: 70px; font-size: 15px"><u><strong>MARITESS M. OCAMPO</strong></u> </br>
			 OIC, Regional Executive Director </p></td> -->

			<?php
			// Determine which signatory to show based on current month-day
			$md = date('m-d');

			// MARITESS M. OCAMPO: July 15 through May 30 (crosses year boundary)
			// MA. THERESA J. ALLEN: June 3 through July 14
			if ($md >= '07-15' || $md <= '05-30') {
				?>
				<td style="width:35%; text-align:center;">
					<p style="font-size: 14px; padding-right: 0px;">
						<u><strong>MARITESS M. OCAMPO</strong></u><br/>
						OIC, Regional Executive Director
					</p>
				</td>
				<?php
			} elseif ($md >= '06-03' && $md <= '07-14') {
				?>
				<td style="width:35%; text-align:center;">
					<p style="font-size: 14px; padding-right: 0px;">
						<u><strong>MA. THERESA J. ALLEN</strong></u><br/>
						OIC, Regional Executive Director
					</p>
				</td>
				<?php
			} else {
				// Fallback to MARITESS for any unspecified small gaps
				?>
				<td style="width:35%; text-align:center;">
					<p style="font-size: 14px; padding-right: 0px;">
						<u><strong>MARITESS M. OCAMPO</strong></u><br/>
						OIC, Regional Executive Director
					</p>
				</td>
				<?php
			}
			?>




</div>	
		</tr>
	</table>
</div>
 </body>
 </html>