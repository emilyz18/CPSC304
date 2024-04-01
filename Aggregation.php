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


function getTablesAndAttributes()
{
    global $db_conn;

    $tables = array();

    $query = "SELECT table_name FROM user_tables";
    $stmt = oci_parse($db_conn, $query);
    oci_execute($stmt);

    while ($row = oci_fetch_assoc($stmt)) {
        $tables[] = $row['TABLE_NAME'];
    }

    return $tables;
}

function getAttributesForTable($tableName)
{
    global $db_conn;

    $attributes = array();

    // Query to fetch attributes for the selected table
    $query = "SELECT column_name FROM all_tab_columns WHERE table_name = '$tableName'";
    $stmt = oci_parse($db_conn, $query);
    oci_execute($stmt);

    while ($row = oci_fetch_assoc($stmt)) {
        $attributes[] = $row['COLUMN_NAME'];
    }

    return $attributes;
}

// connectToDB();

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<body>

    <h2>Find accounts created after a certain date</h2>
	<form method="POST" action="Aggregation.php">
        <input type="hidden" id="SelectionRequest" name="selectionRequest">

		<!-- <input type="checkbox" id="BeforeDate" name="beforeDate"> -->
		<!-- <label for="BeforeDateInput">Accounts created before:</label>
        <input type="date" id="BeforeDateInput" name="beforeDateInput" pattern="\d{4}-\d{2}-\d{2}"> </br> -->

		<!-- <input type="checkbox" id="AfterDate" name="afterDate"> -->
		<label for="AftereDateInput">Accounts created after:</label> <!-- todo: add some space here !-->
        <input type="date" id="AfterDateInput" name="afterDateInput" pattern="\d{4}-\d{2}-\d{2}" required>


		<p><input type="submit" value="Find" name="find"></p>

	</form>
    
    <hr />

	<h2>Choose attributes from any table</h2>
	<form method="POST" action="Aggregation.php">
        <input type="hidden" id="ProjectionRequest" name="projectionRequest">

		<label for="selectedTable">Select Table:</label>
        <select name="selectedTable" onchange="this.form.submit()">
            <?php
            $tables = getTablesAndAttributes();
            foreach ($tables as $table) {
            $selected = isset($_POST['selectedTable']) && $_POST['selectedTable'] == $table ? 'selected' : '';
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

	<?php
	// The following code will be parsed as PHP

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

	function handleSelectionRequest($afterDate)
	{
		global $db_conn;
		$result = executePlainSQL("
   		SELECT A.accountID
    	FROM Has_Account A
		WHERE A.since > TO_DATE('$afterDate', 'YYYY-MM-DD')
		");

		if ($result) {
			echo "<br>AccountID<br>";
			while ($row = oci_fetch_array($result, OCI_ASSOC)) {
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
		// var_dump($selectedTable)
		$result = executePlainSQL($selectQuery);
		
		if ($result) {
			echo '<h2>Generated Table</h2>';
			echo '<table>';
			echo '<tr>';
			// Display attribute names as table headers
			foreach ($selectedAttributes as $attribute) {
				echo '<th>' . $attribute . '</th>';
			}
			echo '</tr>';
		
			// Check if there are rows to fetch
			if (oci_fetch($result)) {
				// Fetch and display attribute values for each row
				do {
					echo '<tr>';
					foreach ($selectedAttributes as $attribute) {
						echo '<td>' . oci_result($result, $attribute) . '</td>';
					}
					echo '</tr>';
				} while (oci_fetch($result));
			} else {
				echo '<tr><td colspan="' . count($selectedAttributes) . '">No data found for the selected attributes and table.</td></tr>';
			}
		
			echo '</table>';
		} else {
			echo '<p>No data found for the selected attributes and table.</p>';
			$e = oci_error(); // Get OCI error information
			if ($e) {
				echo htmlentities($e['message']); // Display OCI error message
			}
		}

	}

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('selectionRequest', $_POST)) {
				// Handle form data and pass to handleSelectionRequest
				// $beforeDate = $_POST['beforeDateInput'];
				$afterDate = $_POST['afterDateInput'];
				handleSelectionRequest($afterDate);
				
			} else if (array_key_exists('projectionRequest', $_POST)) {	
				$selectedAttributes = isset($_POST['selectedAttributes']) ? $_POST['selectedAttributes'] : array();
				$selectedTable = $_POST['selectedTable'];
				handleProjectionRequest($selectedTable, $selectedAttributes);
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
