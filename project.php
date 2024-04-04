<?php
	session_start();
	if (!isset($_SESSION['activeTab'])) {
	$_SESSION['activeTab'] = 'UserManagement'; // Default to UserManagement
	}
?>


<?php
	// The preceding tag tells the web server to parse the following text as PHP
	// rather than HTML (the default)

	// The following 3 lines allow PHP errors to be displayed along with the page
	// content. Delete or comment out this block when it's no longer needed.
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	// Set some parameters

	// Database access configuration
	$config["dbuser"] = "ora_fangzh02";			// change "cwl" to your own CWL
	$config["dbpassword"] = "a72990732";	// change to 'a' + your student number
	$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
	$db_conn = NULL;	// login credentials are used in connectToDB()

	$success = true;	// keep track of errors so page redirects only if there are no errors

	$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

	connectToDB();

	// The next tag tells the web server to stop parsing the text as PHP. Use the
	// pair of tags wherever the content switches to PHP
?>

<!DOCTYPE html>
<html>
<head>
    <title>CPSC 304 GroupProject</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<body>

	<h2>Reset</h2>
	<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

	<form method="POST" action="project.php">
		<input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
		<p><input type="submit" value="Reset" name="reset"></p>

		<?php
			if (isset($_POST['reset'])) {
				if(connectToDB()) {
					handleResetRequest();
				}
			}
		?>
	</form> 

	<hr />

	<nav>
		<button class="tablink <?php echo ($_SESSION['activeTab'] == 'UserManagement') ? 'active' : ''; ?>" onclick="openPage('UserManagement', this)">User Management</button>
		<button class="tablink <?php echo ($_SESSION['activeTab'] == 'DataDisplayQueries') ? 'active' : ''; ?>" onclick="openPage('DataDisplayQueries', this)">Data Display & Queries</button>
	</nav>



	<hr />

	<div id="UserManagement" class="tabcontent">

		<h2>Create New User</h2>
		<form method="POST" action="project.php">
			<input type="hidden" id="insertUserRequest" name="insertUserRequest">
	
			ID(Must be 8 Digits): <input type="text" name="insID"> <br /><br />
			Email: <input type="text" name="insEmail"> <br /><br />
			Name: <input type="text" name="insName"> <br /><br />
			Address: <input type="text" name="insAddress"> <br /><br />
			Phone Number: <input type="text" name="insPhoneNumber"> <br /><br />
	
			<input type="submit" value="Insert" name="insertSubmit">
			<?php
				if (isset($_POST['insertSubmit'])) {
					$_SESSION['activeTab'] = 'UserManagement';
					if(connectToDB()) {
						$Msg = handleInsertUserRequest();
						echo "<div id='Messages'>$Msg</div>";
						// disconnectFromDB();
					}
				}
			?>
			
		</form>


		<form method="POST" action="project.php">
			<input type="submit" name="displayUserTable_Insert" value="Display User Table">

			<?php
				if (isset($_POST['displayUserTable_Insert'])) {
					$_SESSION['activeTab'] = 'UserManagement';
					displayUserInfo();
					displayUserContactInfo();
				}
			?>
		</form>

		<hr />

		<h2>Add User to Financial Market</h2>
		<form method="POST" action="project.php">
			<input type="hidden" id="insertUserInFinancialRequest" name="insertUserInFinancialRequest">
		
			User ID (Must be 8 Digits): <input type="text" name="userID"> <br /><br />
			Country: <input type="text" name="country"> <br /><br />
		
			<input type="submit" value="Add to Market" name="insertUserInFinancial">

			<?php
				if (isset($_POST['insertUserInFinancial'])) {
					if(connectToDB()) {
						$Msg = handleInsertUserInFinancialRequest();
						echo "<div id='Messages'>$Msg</div>";
						// disconnectFromDB();
					}
				}
			?>
		</form>

		

		<form method="POST" action="project.php">
			<input type="submit" name="displayMarketData" value="Display Market Data">

			<?php
				if (isset($_POST['displayMarketData'])) {
					displayUserInFinancial();
					displayFinancialMarketInfo();
				}
			?>
		</form>
		
		<hr />


		<h2>Change User's Info</h2>
		<p>Enter the user's ID and the new name you wish to change.</p>

		<form method="POST" action="project.php">
			<input type="hidden" id="updateQueryRequest" name="updateQueryRequest">

			ID (to Update): <input type="text" name="userID"> <br /><br />
			New Name: <input type="text" name="newName"> <br /><br />
			New Email: <input type="text" name="newEmail"> <br /><br />
			<input type="submit" value="Update" name="updateSubmit">

			<?php
				if (isset($_POST['updateSubmit'])) {
					if(connectToDB()) {
						$Msg = handleUpdateRequest();
						echo "<div id='Messages'>$Msg</div>";
						// disconnectFromDB();
					}
				}
			?>
		</form>

		<form method="POST" action="project.php">
			<input type="submit" name="displayUserTable_Update" value="Display User Table">
			
			<?php
				if (isset($_POST['displayUserTable_Update'])) {
					displayUserInfo();
					displayUserContactInfo();
				}
			?>
		</form>


		<hr />

		<h2>Delete User</h2>
		<p>Enter the ID of the user you wish to delete.</p>

		<form method="POST" action="project.php">
			<input type="hidden" id="deleteUserRequest" name="deleteUserRequest">
			ID (to Delete): <input type="text" name="userIDToDelete"> <br /><br />
			<input type="submit" value="Delete" name="deleteSubmit"></p>

			<?php
				if (isset($_POST['deleteSubmit'])) {
					if(connectToDB()) {
						$Msg = handleDeleteRequest();
						echo "<div id='Messages'>$Msg</div>";
						// disconnectFromDB();
					}
				}
			?>
		</form>

		<form method="POST" action="project.php">
			<input type="submit" name="displayUserTable_Delete" value="Display User Table">

			<?php
				if (isset($_POST['displayUserTable_Delete'])) {
					displayUserInfo();
					displayUserContactInfo();
				}
			?>
		</form>

	</div>

	<div id="DataDisplayQueries" class="tabcontent">
	<hr />

	<h2>Find accounts</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="SelectionRequest" name="selectionRequest">

		Filter (example: balance > 900): <input type="text" name="filter" required>


		<p><input type="submit" value="Find" name="findSelection"></p>
		<?php
			if (isset($_POST['findSelection'])) {
				$_SESSION['activeTab'] = 'DataDisplayQueries';
				
				$filter = $_POST['filter'];
				handleSelectionRequest($filter);
			}
		?>

	</form>
    
    <hr />

	<h2>Choose attributes from any table</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="ProjectionRequest" name="projectionRequest">

		<label for="selectedTable">Select Table:</label>
        <select name="selectedTable" onchange="this.form.submit()">
            <?php
            $tables = getTables();
            foreach ($tables as $table) 
			{
				$selected = $_POST['selectedTable'] == $table ? 'selected' : '';
				echo '<option value="' . $table . '" ' . $selected . '>' . $table . '</option>';
       		}
            ?>
        </select>

		<?php
		if (isset($_POST['selectedTable'])) {
			$_SESSION['activeTab'] = 'DataDisplayQueries';
			
			$selectedTable = $_POST['selectedTable'];
			$attributes = getAttributesForTable($selectedTable);
			if ($attributes) {
				echo '<div id="attributeCheckboxes">';
				foreach ($attributes as $attribute) {
					echo '<input type="checkbox" name="selectedAttributes[]" value="' . $attribute . '">';
					echo '<label for="' . $attribute . '">' . $attribute . '</label><br>';
				}
				echo '</div>';
			} else {
				echo '<p>No attributes found for the selected table.</p>';
			}
		}
    ?>

		<p><input type="submit" value="Find" name="findProjection"></p>
		<?php
			if (isset($_POST['findProjection'])) {
				$_SESSION['activeTab'] = 'DataDisplayQueries';
				
				if (!isset($_POST['selectedAttributes']) || empty($_POST['selectedAttributes'])) {
						echo '<p style="color: red;">Please select at least one attribute.</p>';
					} else {
						$selectedAttributes = $_POST['selectedAttributes'];
						$selectedTable = $_POST['selectedTable'];
						handleProjectionRequest($selectedTable, $selectedAttributes);
					}
			}
		?>
	</form>

	<hr />

	<h2>Find names of accounts that have operated on stocks with dividend greater than a certain amount</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="JoinRequest" name="joinRequest">
		Dividend: <input type="number" for="Dividend" step="0.01" name="dividendInput" min="0" required><br>

		<p><input type="submit" value="Find" name="findJoin"></p>
		<?php
			if (isset($_POST['findJoin'])) {
				$_SESSION['activeTab'] = 'DataDisplayQueries';

				$dividend = $_POST['dividendInput'];
				handleJoinRequest($dividend);
			}
		?>

	</form>

    <h2>Find the highest stock price of each account</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="GroupbyRequest" name="groupByRequest">
		<p><input type="submit" value="Find" name="find"></p>
		<?php
			if (isset($_POST['find'])) {
				$_SESSION['activeTab'] = 'DataDisplayQueries';
				
				handleGroupByRequest();
			}
		?>
	</form>
    
    <hr />

    <h2>Find the lowest stock price of each account having accountID starts with 'A'</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="HavingRequest" name="havingRequest">
		<p><input type="submit" value="Find" name="find1"></p>
		<?php
			if (isset($_POST['find1'])) {
				$_SESSION['activeTab'] = 'DataDisplayQueries';
				
				handleHavingRequest();
			}
		?>
	</form>

    <hr />

    <h2>Find the account who bought the lowest average stock price</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="NestedAggregationRequest" name="nestedAggregationRequest">
		<p><input type="submit" value="Find" name="find2"></p>
		<?php
			if (isset($_POST['find2'])) {
				$_SESSION['activeTab'] = 'DataDisplayQueries';
				
				handleNestedAggregationRequest();
			}
		?>
	</form>

    <hr />

    <h2>Find account has operated on every stock in its watchlist (includes the accounts has no watchlist)</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="DivisionRequest" name="divisionRequest">
		<p><input type="submit" value="Find" name="find3"></p>
		<?php
			if (isset($_POST['find3'])) {
				$_SESSION['activeTab'] = 'DataDisplayQueries';
				
				handleDivisionRequest();
			}
		?>
	</form>

	</div>

	<?php
	// The following code will be parsed as PHP

	function executeSQLScript($filePath) {
		global $db_conn;
		
		$scriptContent = file_get_contents($filePath);
		$sqlCommands = explode(';', $scriptContent); // assuming each SQL command ends with a semicolon
		
		foreach ($sqlCommands as $command) {
			if (trim($command)) {
				executePlainSQL($command);
			}
		}
	}


	function debugAlertMessage($message)
	{
		global $show_debug_alert_messages;

		if ($show_debug_alert_messages) {
			echo "<script type='text/javascript'>alert('" . $message . "');</script>";
		}
	}

	function executePlainSQL($cmdstr)
	{ //takes a plain (no bound variables) SQL command and executes it
		//echo "<br>running ".$cmdstr."<br>";
		global $db_conn, $success;

		$statement = oci_parse($db_conn, $cmdstr);
		//There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			// $e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
			// echo htmlentities($e['message']);
			$success = False;
		}

		$r = @oci_execute($statement, OCI_DEFAULT);
		if (!$r) {
			// echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			// $e = oci_error($statement); // For oci_execute errors pass the statementhandle
			// echo htmlentities($e['message']);
			echo '<p style="color: red;">Error: Invalid filter.</p>';

			$success = False;
		}

		return $statement;
	}

	function connectToDB()
	{
		global $db_conn;
		global $config;

		// Your username is ora_(CWL_ID) and the password is a(student number). For example,
		// ora_platypus is the username and a12345678 is the password.
		// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
		$db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

		if ($db_conn) {
			debugAlertMessage("Database is Connected");
			return true;
		} else {
			debugAlertMessage("Cannot connect to Database");
			$e = OCI_Error(); // For oci_connect errors pass no handle
			echo htmlentities($e['message']);
			return false;
		}
	}

	function disconnectFromDB()
	{
		global $db_conn;

		debugAlertMessage("Disconnect from Database");
		oci_close($db_conn);
	}

	function getTables()
	{
		global $db_conn;
		$tables = array();

		$query = "SELECT table_name FROM user_tables";
		$statement = oci_parse($db_conn, $query);
		oci_execute($statement, OCI_DEFAULT);

		while ($row = oci_fetch_array($statement)) 
		{
			$tables[] = $row['TABLE_NAME'];
    	}

    	return $tables;
	}

	function getAttributesForTable($tableName)
	{
		global $db_conn;
		$attributes = array();

		$query = "SELECT column_name FROM user_tab_cols WHERE table_name = '$tableName'";
		$statement = oci_parse($db_conn, $query);
		oci_execute($statement, OCI_DEFAULT);

		while ($row = oci_fetch_array($statement)) 
		{
			$attributes[] = $row['COLUMN_NAME'];
		}

		return $attributes;
	}

	function handleResetRequest()
	{
		global $db_conn;

		executePlainSQL("DROP TABLE FINANCIAL_MARKET_HOURS CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE FINANCIAL_MARKET_INFO CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE STOCK_MARKET CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE BOND_MARKET CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE USER_ADDRESS_INFO CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE USER_CONTACT_INFO CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE USER_INFO CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE User_In_Financial CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE HAS_ACCOUNT CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE PUBLIC_TRADED_COMPANY CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE PUBLIC_TRADED_COMPANY_DETAILS CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE OWNS_PTC_STOCK_STOCK CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE HAVE_STOCK CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE BOND CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE HAVE_BOND CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE CREATE_WATCHLIST CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE ISSUE_PTC_BOND_CORPORATEBOND CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE GOVERNMENT CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE ISSUE_GOVERNMENT_BOND_GOVERNMENTBOND CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE OPERATES_STOCK CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE OPERATES_BOND CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE INCLUDES_STOCK CASCADE CONSTRAINTS");
		executePlainSQL("DROP TABLE INCLUDES_BOND CASCADE CONSTRAINTS");
		
		executeSQLScript('/home/f/fangzh02/public_html/MainSQL_Lite.sql');
		
		echo "<br> Reset Done! <br>";

		oci_commit($db_conn);
	}

	function handleInsertUserRequest() {
		global $db_conn;
		$errorMessage = "";
	
		// Input sanitation and validation
		$insID = htmlspecialchars($_POST['insID']);
		$insEmail = filter_var($_POST['insEmail'], FILTER_VALIDATE_EMAIL);
		$insName = preg_replace("/[^a-zA-Z\s]/", "", $_POST['insName']);
		$insAddress = htmlspecialchars($_POST['insAddress']);
		$insPhoneNumber = htmlspecialchars($_POST['insPhoneNumber']);
	
		// Input validation checks
		if (strlen($insID) != 8) {
			$errorMessage .= "ID Must be 8 Digits<br>";
		}
		if (!$insEmail) {
			$errorMessage .= "Invalid Email<br>";
		}
		if (empty($insName)) {
			$errorMessage .= "Invalid Name<br>";
		}
		if (empty($insAddress)) {
			$errorMessage .= "Invalid Address<br>";
		}
		if (empty($insPhoneNumber) || !preg_match('/^[0-9]{10,15}$/', $insPhoneNumber)) {
			$errorMessage .= "Invalid Phone Number<br>";
		}
	
		// Stop if there are any validation errors
		if (!empty($errorMessage)) {
			return $errorMessage;
		}
	
		// Check for duplicate ID, email, or address
		// Duplicate ID check
		$idCheckResult = executePlainSQL("SELECT ID FROM User_Info WHERE ID='" . $insID . "'");
		if (oci_fetch_array($idCheckResult)) {
			$errorMessage .= "Error: User with ID " . $insID . " already exists.<br>";
		}
	
		// Duplicate email check
		$emailCheckResult = executePlainSQL("SELECT email FROM User_Contact_Info WHERE email='" . $insEmail . "'");
		if (oci_fetch_array($emailCheckResult)) {
			$errorMessage .= "Error: User with email " . $insEmail . " already exists.<br>";
		}
	
		// Duplicate address check
		$addressCheckResult = executePlainSQL("SELECT address FROM User_Address_Info WHERE address='" . $insAddress . "'");
		if (oci_fetch_array($addressCheckResult)) {
			$errorMessage .= "Error: User with address " . $insAddress . " already exists.<br>";
		}
	
		// Stop if there are any duplicate errors
		if (!empty($errorMessage)) {
			return $errorMessage;
		}
	
		// If all checks pass, perform the database insert
		executePlainSQL("INSERT INTO User_Address_Info (address, phoneNumber) VALUES ('" . $insAddress . "', '" . $insPhoneNumber . "')");
		executePlainSQL("INSERT INTO User_Contact_Info (email, address) VALUES ('" . $insEmail . "', '" . $insAddress . "')");
		executePlainSQL("INSERT INTO User_Info (email, ID, name) VALUES ('" . $insEmail . "', '" . $insID . "', '" . $insName . "')");
	
		oci_commit($db_conn);
		// Return success message if no error occurred
		return "User added successfully!";
	}

	function handleInsertUserInFinancialRequest() {
		global $db_conn;
		$errorMessage = "";
		
		// Input sanitation and validation
		$userID = htmlspecialchars($_POST['userID']);
		$country = htmlspecialchars($_POST['country']);
	
		// Input validation checks
		if (strlen($userID) != 8) {
			$errorMessage .= "ID Must be 8 Digits<br>";
		}
		if (empty($country)) {
			$errorMessage .= "Country is required<br>";
		}
		
		// Stop if there are any validation errors
		if (!empty($errorMessage)) {
			return $errorMessage;
		}
	
		// Check for existence of user ID
		$userCheckResult = executePlainSQL("SELECT ID FROM User_Info WHERE ID='" . $userID . "'");
		if (!oci_fetch_array($userCheckResult)) {
			$errorMessage .= "Error: User with ID " . $userID . " does not exist.<br>";
		}
	
		// Check for existence of country in Financial_Market_Info
		$countryCheckResult = executePlainSQL("SELECT country FROM Financial_Market_Info WHERE country='" . $country . "'");
		if (!oci_fetch_array($countryCheckResult)) {
			$errorMessage .= "Error: Country " . $country . " does not exist in Financial Market Info.<br>";
		}
		
		// Check if the user-country pair already exists in User_In_Financial
		$existsCheckResult = executePlainSQL("SELECT * FROM User_In_Financial WHERE ID='" . $userID . "' AND country='" . $country . "'");
		if (oci_fetch_array($existsCheckResult)) {
			$errorMessage .= "Error: The user with ID " . $userID . " is already associated with the financial market in " . $country . ".<br>";
		}
	
		// Stop if there are any existence or duplication errors
		if (!empty($errorMessage)) {
			return $errorMessage;
		}
		
		// If all checks pass, perform the database insert
		$sql = "INSERT INTO User_In_Financial (country, ID) VALUES (:country, :userID)";
		$stmt = oci_parse($db_conn, $sql);
	
		// Bind the parameters
		oci_bind_by_name($stmt, ':country', $country);
		oci_bind_by_name($stmt, ':userID', $userID);
	
		// Execute the statement and check for errors
		if (!oci_execute($stmt)) {
			$e = oci_error($stmt);
			$errorMessage .= "Error inserting into User_In_Financial: " . htmlentities($e['message']) . "<br>";
		}
	
		if (!empty($errorMessage)) {
			return $errorMessage;
		}
	
		// Commit the transaction to finalize the insertion
		oci_commit($db_conn);
		// Return success message if no error occurred
		return "User successfully added to the financial market!";
	}

	function handleUpdateRequest() {
		global $db_conn;
		$errorMessage = "";
		
		// Input sanitation and validation
		$userID = htmlspecialchars($_POST['userID']);
		$newName = htmlspecialchars($_POST['newName']);
		$newEmail = filter_var($_POST['newEmail'], FILTER_VALIDATE_EMAIL);
		
		// Input validation checks
		if (strlen($userID) != 8) {
			$errorMessage .= "ID must be 8 digits.<br>";
		}
		if (empty($newName)) {
			$errorMessage .= "Invalid name.<br>";
		}
		if (!$newEmail) {
			$errorMessage .= "Invalid email.<br>";
		}
		
		// Check if ID exists
		$idCheckResult = executePlainSQL("SELECT ID FROM User_Info WHERE ID='" . $userID . "'");
		if (!oci_fetch_array($idCheckResult)) {
			$errorMessage .= "User ID does not exist.<br>";
		}
		
		// Check if the new email already exists
		$emailCheckResult = executePlainSQL("SELECT email FROM User_Contact_Info WHERE email='" . $newEmail . "'");
		if (oci_fetch_array($emailCheckResult)) {
			$errorMessage .= "Email already in use.<br>";
		}
	
		// Stop if there are any validation errors
		if (!empty($errorMessage)) {
			return $errorMessage;
		}
		
		// Database update operations
		$oldEmailQuery = executePlainSQL("SELECT email FROM User_Info WHERE ID='$userID'");
		$oldEmailRow = OCI_Fetch_Array($oldEmailQuery, OCI_BOTH);
		$oldEmail = $oldEmailRow[0];
	
		$oldAddressQuery = executePlainSQL("SELECT address FROM User_Contact_Info WHERE email='$oldEmail'");
		$oldAddressRow = OCI_Fetch_Array($oldAddressQuery, OCI_BOTH);
		$oldAddress = $oldAddressRow[0];
	
		// Insert the new email into User_Contact_Info with the existing address
		executePlainSQL("INSERT INTO User_Contact_Info (email, address) VALUES ('" . $newEmail . "', '" . $oldAddress . "')");
	
		// Update the User_Info with the new email and name
		executePlainSQL("UPDATE User_Info SET email='" . $newEmail . "', name='" . $newName . "' WHERE ID='" . $userID . "'");
		
		// Delete the old email from User_Contact_Info
		executePlainSQL("DELETE FROM User_Contact_Info WHERE email='" . $oldEmail . "'");
		
		oci_commit($db_conn);
		// Return success message if no error occurred
		return "User updated successfully!";
	}
	

	function handleDeleteRequest() {
		global $db_conn;
		$errorMessage = "";
	
		// Get the user ID from the form submission
		$userIDToDelete = htmlspecialchars($_POST['userIDToDelete']);
	
		// Validate input
		if (empty($userIDToDelete) || strlen($userIDToDelete) != 8) {
			$errorMessage = "Invalid User ID format. Must be 8 digits.<br>";
		}
	
		if (empty($errorMessage)) {
			$oldEmailQuery = executePlainSQL("SELECT email FROM User_Info WHERE ID='$userIDToDelete'");
			if ($oldEmailRow = OCI_Fetch_Array($oldEmailQuery, OCI_BOTH)) {
				$oldEmail = $oldEmailRow[0];
	
				$oldAddressQuery = executePlainSQL("SELECT address FROM User_Contact_Info WHERE email='$oldEmail'");
				if ($oldAddressRow = OCI_Fetch_Array($oldAddressQuery, OCI_BOTH)) {
					$oldAddress = $oldAddressRow[0];
	
					// No need to manually delete from User_Contact_Info or User_Address_Info due to ON DELETE CASCADE
					// Just delete the user from User_Info
					executePlainSQL("DELETE FROM User_Address_Info WHERE Address='$oldAddress'");
					oci_commit($db_conn);
	
					// Return success message if no error occurred
					return "User Deleted Successfully!";
				} else {
					$errorMessage = "User ID found, but corresponding email or address not found.<br>";
				}
			} else {
				$errorMessage = "User ID not found.<br>";
			}
		}
	
		// Return error message if something went wrong
		return $errorMessage;
	}
	

	function displayUserInFinancial() {
		if (connectToDB()) {
			global $db_conn;
	
			$result = executePlainSQL("SELECT * FROM User_In_Financial");
			echo "<br>User In Financial Data:<br>";
			echo "<table border='1'>";
			echo "<tr><th>User ID</th><th>Country</th></tr>";
		
			while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
				echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["COUNTRY"] . "</td></tr>";
			}
		
			echo "</table>";

			disconnectFromDB();
		}
	}
	
	function displayFinancialMarketInfo() {
		if (connectToDB()) {
			global $db_conn;
		
			$result = executePlainSQL("SELECT * FROM Financial_Market_Info");
			echo "<br>Financial Market Info Data:<br>";
			echo "<table border='1'>";
			echo "<tr><th>Country</th><th>Market Date</th><th>Starting Hour</th></tr>";
		
			while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
				echo "<tr><td>" . $row["COUNTRY"] . "</td><td>" . $row["MARKETDATE"] . "</td><td>" . $row["STARTINGHOUR"] . "</td></tr>";
			}
		
			echo "</table>";
			
			disconnectFromDB();
		}

	}

	function displayUserInfo() {
		if (connectToDB()) {
			global $db_conn;
	
			$result = executePlainSQL("SELECT * FROM User_Info");
			echo "<br>User Info Data:<br>";
			echo "<table border='1'>";
			echo "<tr><th>User ID</th><th>Name</th><th>Email</th></tr>";
	
			while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
				echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["EMAIL"] . "</td></tr>";
			}
	
			echo "</table>";
	
			disconnectFromDB();
		}
	}

	function displayUserContactInfo() {
		if (connectToDB()) {
			global $db_conn;
	
			$result = executePlainSQL("SELECT * FROM User_Contact_Info");
			echo "<br>User Contact Info Data:<br>";
			echo "<table border='1'>";
			echo "<tr><th>Email</th><th>Address</th></tr>";
	
			while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
				echo "<tr><td>" . $row["EMAIL"] . "</td><td>" . $row["ADDRESS"] . "</td></tr>";
			}
	
			echo "</table>";
	
			disconnectFromDB();
		}
	}

	function handleSelectionRequest($filter)
	{
		global $db_conn;
		$result = executePlainSQL("
   		SELECT A.accountID
    	FROM Has_Account A
		WHERE $filter
		");

		if ($result) {
			echo "<br>AccountID<br>";
			while ($row = @oci_fetch_array($result, OCI_ASSOC)) {
				echo $row['ACCOUNTID'] . "<br>";
			}
		} else {
			echo "No data found.";
		}

	}

	function handleProjectionRequest($selectedTable, $selectedAttributes)
	{
		global $db_conn;
		$selectQuery = "SELECT " . implode(", ", $selectedAttributes) . " FROM $selectedTable";
		$result = executePlainSQL($selectQuery);
		
		if ($result) {
			echo '<table>';
			echo '<tr>';
			foreach ($selectedAttributes as $attribute) {
				echo '<th>' . $attribute . '</th>';
			}
			echo '</tr>';
		
			while (oci_fetch($result)) {
				echo '<tr>';
				foreach ($selectedAttributes as $attribute) {
					echo '<td>' . oci_result($result, $attribute) . '</td>';
				}
				echo '</tr>';
			}
		
			echo '</table>';
		} else {
			echo '<p>No data found for the selected attributes and table.</p>';
		}

	}

	function handleJoinRequest($dividend)
	{
		global $db_conn;
		$result = executePlainSQL("
   		SELECT DISTINCT A.accountName
    	FROM Has_Account A, Operates_Stock S, Owns_PTC_Stock_Stock O
		WHERE O.dividend > $dividend AND S.accountID = A.accountID AND S.stockID = O.stockID
		");

		if ($result) {
			echo "<br>accountName<br>";
			while ($row = oci_fetch_array($result, OCI_ASSOC)) {
				echo $row['ACCOUNTNAME'] . "<br>";
			}
		} else {
			echo "No account names found.";
		}
	}

	function handleGroupByRequest()
	{
		global $db_conn;
		$result = executePlainSQL
		("
		Select O.accountID, MAX(S.price) AS highest_price
		From Operates_Stock O, Owns_PTC_Stock_Stock S
		Where O.stockID = S.stockID
		Group by O.accountID
		");
		if ($result) {
			echo "<br>AccountID | Highest Price<br>";
			while ($row = oci_fetch_array($result, OCI_ASSOC)) {
				echo $row['ACCOUNTID'] . " | " . $row['HIGHEST_PRICE'] . "<br>";
			}
		} else {
			echo "No data found.";
		}
	}

	function handleHavingRequest()
	{
		global $db_conn;
		$result = executePlainSQL
		("
		Select O.accountID, MIN(S.price) AS lowest_price
		From Operates_Stock O, Owns_PTC_Stock_Stock S
		Where O.stockID = S.stockID
		Group by O.accountID
		Having O.accountID LIKE 'A%'
		");
		if ($result) {
			echo "<br>AccountID | Lowest Price<br>";
			while ($row = oci_fetch_array($result, OCI_ASSOC)) {
				echo $row['ACCOUNTID'] . " | " . $row['LOWEST_PRICE'] . "<br>";
			}
		} else {
			echo "No data found.";
		}
	}

	function handleNestedAggregationRequest()
	{
		global $db_conn;
		$result = executePlainSQL
		("
		With Temp(accountID, average) AS 
		(Select O.accountID, AVG(S.price) as average
		From Operates_Stock O, Owns_PTC_Stock_Stock S
		Where O.stockID = S.stockID
		Group by O.accountID)
		Select T.accountID, T.average
		From Temp T
		Where T.average = (Select MIN(T2.average)
		From Temp T2)
		");
		if ($result) {
			echo "<br>AccountID | Lowest Average<br>";
			while ($row = oci_fetch_array($result, OCI_ASSOC)) {
				echo $row['ACCOUNTID'] . " | " . $row['AVERAGE'] . "<br>";
			}
		} else {
			echo "No data found.";
		}
	}

	function handleDivisionRequest()
	{
		global $db_conn;
		$result = executePlainSQL
		("
		Select H.accountID
		From Has_Account H
		Where NOT EXISTS 
		(Select I.stockID
		From Includes_Stock I, Create_Watchlist C
		Where I.listID = C.listID and C.accountID = H.accountID
		AND NOT EXISTS (
			Select O.accountID
			From Operates_Stock O
			Where O.accountID = H.accountID
			And I.stockID = O.stockID
		)
		)
		");
		if ($result) {
			echo "<br>AccountID<br>";
			while ($row = oci_fetch_array($result, OCI_ASSOC)) {
				echo $row['ACCOUNTID'] ."<br>";
			}
		} else {
			echo "No data found.";
		}
	}
	
	// if (isset($_POST['reset']) || isset($_POST['find'])) {
	// 	handlePOSTRequest();
	// }

	
	// End PHP parsing and send the rest of the HTML content
	?>

	<script src="script.js"></script>
</body>

</html>
