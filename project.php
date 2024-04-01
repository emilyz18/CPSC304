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
$config["dbuser"] = "ora_axue02";			// change "cwl" to your own CWL
$config["dbpassword"] = "a70299383";	// change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()

$success = true;	// keep track of errors so page redirects only if there are no errors

$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

connectToDB();

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<body>

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

		<p><input type="submit" value="Find" name="find"></p>
	</form>

	<h2>Find names of accounts that have operated on stocks with dividend greater than a certain amount</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="JoinRequest" name="joinRequest">
		Dividend: <input type="number" for="Dividend" step="0.01" name="dividendInput" min="0" required><br>

		<p><input type="submit" value="Find" name="find"></p>

	</form>

    <h2>Find the highest stock price of each account</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="GroupbyRequest" name="groupByRequest">
		<p><input type="submit" value="Find" name="find"></p>
	</form>
    
    <hr />

    <h2>Find the lowest stock price of each account having accountID starts with 'A'</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="HavingRequest" name="havingRequest">
		<p><input type="submit" value="Find" name="find"></p>
	</form>

    <hr />

    <h2>Find the account who bought the lowest average stock price</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="NestedAggregationRequest" name="nestedAggregationRequest">
		<p><input type="submit" value="Find" name="find"></p>
	</form>

    <hr />

    <h2>Find account has operated on every stock in its watchlist (includes the accounts has no watchlist)</h2>
	<form method="POST" action="project.php">
        <input type="hidden" id="DivisionRequest" name="divisionRequest">
		<p><input type="submit" value="Find" name="find"></p>
	</form>

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

	function handleResetRequest()
	{
		global $db_conn;

		// Create new table
		echo "<br> creating new table <br>";
		executeSQLScript('/home/a/axue02/new.sql');
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
			$e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
			echo htmlentities($e['message']);
			$success = False;
		}

		$r = oci_execute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = oci_error($statement); // For oci_execute errors pass the statementhandle
			echo htmlentities($e['message']);
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

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('projectionRequest', $_POST)) {	
				if (!isset($_POST['selectedAttributes']) || empty($_POST['selectedAttributes'])) {
					echo '<p style="color: red;">Please select at least one attribute.</p>';
				} else {
					$selectedAttributes = $_POST['selectedAttributes'];
					$selectedTable = $_POST['selectedTable'];
					handleProjectionRequest($selectedTable, $selectedAttributes);
				}
			} else if (array_key_exists('joinRequest', $_POST)) {
				$dividend = $_POST['dividendInput'];
				handleJoinRequest($dividend);
			} else if (array_key_exists('resetTablesRequest', $_POST)) {
				handleResetRequest();
			} else if (array_key_exists('groupByRequest', $_POST)) {
				handleGroupByRequest();
			} else if (array_key_exists('havingRequest', $_POST)) {
				handleHavingRequest();
			} else if (array_key_exists('nestedAggregationRequest', $_POST)) {
				handleNestedAggregationRequest();
			} else if (array_key_exists('divisionRequest', $_POST)) {
				handleDivisionRequest();
			}

			disconnectFromDB();
		}
	}

	if (isset($_POST['reset']) || isset($_POST['find'])) {
		handlePOSTRequest();
	}

	// End PHP parsing and send the rest of the HTML content
	?>
</body>

</html>
