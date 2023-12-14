<?php
require_once("config.php");

/*
// Debugging tools in case something breaks

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
var_dump($_POST);
echo "<br>";
var_dump($_SESSION);
echo "<br>";
echo $_SERVER["REQUEST_METHOD"];
*/

?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $PROJECT_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1><?= $PROJECT_NAME ?></h1>
    <?php include("nav.php"); ?>
    <?php echo "<br>"; 
    echo "<div style='text-align: center; font-size: 30px;'><strong>Insert Tool</strong></div>";
    echo "<br>";
    ?>

    <!-- Form to select a table to insert data into -->
    <?php $table_name = $_POST['table_name'] ?? null; ?>
    <?php
    if (!empty($insertMsg)) {
        echo "$insertMsg<br>";
        $insertMsg = "";
    }

    // Create a new form builder object
    $insert_form = new PhpFormBuilder();
    $insert_form->set_att("method", "POST");

    // Connect to the database
    $db = new PDO('mysql:host=localhost;dbname=atmmanager', 'atmmanager', 'Zyaf&6yud');

    // Get a list of all tables in the database
    $result = $db->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);

    // Add a select input field to choose a table to insert data into
    $insert_form->add_input("Table Selection", [
        "type" => "select",
        "name" => "table_name",
        "id" => "table_name",
        "options" => array_combine($tables, $tables)
    ]);

    // Add a submit button to submit the form
    $insert_form->add_input("submit_select", array(
        "type" => "submit",
        "value" => "Select"
    ));

    // Build the form
    $insert_form->build_form();

    // Initialize an empty array to hold the columns of the selected table
    $columns = [];

    // If a table has been selected, show a form to insert data into that table
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["table_name"])) {
        $table_name = $_POST["table_name"];
        $_SESSION["table_name"] = $table_name;
        
        echo "You selected " . $table_name . "! <br><br>";

        // Get a list of all columns in the selected table
        $result = $db->query("DESCRIBE $table_name");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN);

        // Create a new form builder object
        $insert_form = new PhpFormBuilder();
        $insert_form->set_att("method", "POST");

        // Add input fields for each column in the selected table
        foreach ($columns as $column) {

            // Skip the Department_Name column
            if ($column === 'Department_Name') {
                continue;
            }

            // Only add input fields for varchar columns
            $result = $db->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = '$column'");
            $data_type = $result->fetchColumn();
            if ($data_type !== "varchar") {
                continue;
            }

            $insert_form->add_input($column, [
                "type" => "text",
                "name" => $column,
                "id" => $column
            ]);
        }

        // If the selected table is ATM, add a dropdown menu for Branch_ID and Bank_ID
        if ($table_name == 'ATM') {
            // Fetch all branch names and IDs from the Branch table
            $result = $db->query("SELECT Branch_ID, Name FROM Branch");
            $branches = $result->fetchAll(PDO::FETCH_ASSOC);

            // Add input fields for Starting Hours, Closing Hours, and Cash Stored
            $insert_form->add_input("Starting Hours", [
                "type" => "time",
                "name" => "Start_Hours",
                "id" => "Start_Hours"
            ]);

            $insert_form->add_input("Closing Hours", [
                "type" => "time",
                "name" => "Close_Hours",
                "id" => "Close_Hours"
            ]);

            $insert_form->add_input("Cash Stored", [
                "type" => "text",
                "name" => "Cash_Stored",
                "id" => "Cash_Stored"
            ]);

            // Add a dropdown menu for Branch_ID
            $options = [];
            foreach ($branches as $branch) {
                $options[$branch['Branch_ID']] = $branch['Branch_ID'] . ' - ' . $branch['Name'];
            }

            $insert_form->add_input("Branch", [
                "type" => "select",
                "name" => "Branch_ID",
                "id" => "Branch_ID",
                "options" => $options
            ]);
            
            // Fetch all bank names and IDs from the Bank table
            $result = $db->query("SELECT Bank_ID, Name FROM Bank");
            $banks = $result->fetchAll(PDO::FETCH_ASSOC);

            // Add a dropdown menu for Bank_ID
            $options = [];
            foreach ($banks as $bank) {
                $options[$bank['Bank_ID']] = $bank['Bank_ID'] . ' - ' . $bank['Name'];
            }

            $insert_form->add_input("Bank", [
                "type" => "select",
                "name" => "Bank_ID",
                "id" => "Bank_ID",
                "options" => $options
            ]);
        }

        // If the selected table is Bank, add a hours menu for Start_Hours and Close_Hours
        if ($table_name == 'Bank') {
            $insert_form->add_input("Starting Hours", [
                "type" => "time",
                "name" => "Start_Hours",
                "id" => "Start_Hours"
            ]);

            $insert_form->add_input("Closing Hours", [
                "type" => "time",
                "name" => "Close_Hours",
                "id" => "Close_Hours"
            ]);
        }
        
        // If the selected table is Account_Information, add a dropdown menu for Bank_ID and User_ID
        if ($table_name == 'Account_Information') {
            // Fetch all user IDs from the User table
            $result = $db->query("SELECT User_ID, Name FROM User");
            $users = $result->fetchAll(PDO::FETCH_ASSOC);

            // Add a dropdown menu for User_ID
            $options = [];
            foreach ($users as $user) {
                $options[$user['User_ID']] = $user['User_ID'] . ' - ' . $user['Name'];
            }

            $insert_form->add_input("User", [
                "type" => "select",
                "name" => "User_ID",
                "id" => "User_ID",
                "options" => $options
            ]);
                        
            $insert_form->add_input("Routing Number", [
                "type" => "text",
                "name" => "Routing_Number",
                "id" => "Routing_Number"
            ]);

            $insert_form->add_input("CVC", [
                "type" => "text",
                "name" => "CVC",
                "id" => "CVC"
            ]);

            $insert_form->add_input("Account Number", [
                "type" => "text",
                "name" => "Account_Number",
                "id" => "Account_Number"
            ]);

            // Fetch all bank names and IDs from the Bank table
            $result = $db->query("SELECT Bank_ID, Name FROM Bank");
            $banks = $result->fetchAll(PDO::FETCH_ASSOC);

            // Add a dropdown menu for Bank_ID
            $options = [];
            foreach ($banks as $bank) {
                $options[$bank['Bank_ID']] = $bank['Bank_ID'] . ' - ' . $bank['Name'];
            }

            $insert_form->add_input("Bank", [
                "type" => "select",
                "name" => "Bank_ID",
                "id" => "Bank_ID",
                "options" => $options
            ]);
        }

        // If the selected table is Branch, add a dropdown menu for Bank_ID
        if ($table_name == 'Branch') {
            // Fetch all bank names and IDs from the Bank table
            $result = $db->query("SELECT Bank_ID, Name FROM Bank");
            $banks = $result->fetchAll(PDO::FETCH_ASSOC);

            if ($result === false) {
                echo "SQL Error: " . $db->errorInfo()[2];
                exit;
            }

            // Add a dropdown menu for Bank_ID
            $options = [];
            foreach ($banks as $bank) {
                $options[$bank['Bank_ID']] = $bank['Bank_ID'] . ' - ' . $bank['Name'];
            }

            $insert_form->add_input("Bank", [
                "type" => "select",
                "name" => "Bank_ID",
                "id" => "Bank_ID",
                "options" => $options
            ]);

            $insert_form->add_input("Starting Hours", [
                "type" => "time",
                "name" => "Start_Hours",
                "id" => "Start_Hours"
            ]);

            $insert_form->add_input("Closing Hours", [
                "type" => "time",
                "name" => "Close_Hours",
                "id" => "Close_Hours"
            ]);
        }

        // If the selected table is Transaction, add a dropdown menu for Transaction_Type
        if ($table_name == 'Transaction') {
            $options = [
                "Credit" => "Credit",
                "Debit" => "Debit"
            ];

            $insert_form->add_input("Transaction Type", [
                "type" => "select",
                "name" => "Transaction_Type",
                "id" => "Transaction_Type",
                "options" => $options
            ]);

            $insert_form->add_input("Transaction Date", [
                "type" => "date",
                "name" => "Transaction_Date",
                "id" => "Transaction_Date"
            ]);
        }
            
        // If the selected table is Employee, add a dropdown menu for User_ID and Departments
        if ($table_name == 'Employee') {
            // Fetch all user IDs from the User table
            $result = $db->query("SELECT User_ID, Name FROM User");
            $users = $result->fetchAll(PDO::FETCH_ASSOC);

            // Add a text field for User_ID
            echo "If you're an employee and haven't made an account yet, please do so. <br>
                    You can create an account via Insert -> User and fill out the form. <br>
                    Then you can come back here and select your User_ID. <br><br>";
            $insert_form->add_input("User ID", [
                "type" => "text",
                "name" => "User_ID",
                 "id" => "User_ID",
            ]);

            // Fetch all departments from the Department table
            $result = $db->query("SELECT Department_ID, Department_Name FROM Department");
            $departments = $result->fetchAll(PDO::FETCH_ASSOC);

            // Add a dropdown menu for Department_ID
            $options = [];
            foreach ($departments as $department) {
                $options[$department['Department_ID']] = $department['Department_ID'] . ' - ' . $department['Department_Name'];
            }

            $insert_form->add_input("Department", [
                "type" => "select",
                "name" => "Department_ID",
                "id" => "Department_ID",
                "options" => $options
            ]);
        }

        // If the selected table is Transaction, add an Amount text field
        if ($table_name == 'Transaction') {
            $insert_form->add_input("Amount", [
                "type" => "number",
                "name" => "Amount",
                "id" => "Amount",
                "step" => "1.00",
                "min" => "0.00"
            ]);
        }

        // If the selected table is Department, add a text field for Department_Name
        if ($table_name == 'Department') {
            $insert_form->add_input("Department Name", [
                "type" => "text",
                "name" => "Department_Name",
                "id" => "Department_Name"
            ]);
        }

        $insert_form->add_input("submit_insert", array(
            "type" => "submit",
            "value" => "Insert",
        ));

        $insert_form->build_form();
    }

    

    // If the insert form has been submitted, insert the data into the table
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit-insert"])) {

        $table_name = $_SESSION["table_name"];

        $result = $db->query("DESCRIBE $table_name");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN);

        $values = array();
        $placeholders = array();
        $columnsToInsert = array();

            foreach ($columns as $column) {
                if (!isset($_POST[$column])) {
                    continue;
                }

                $value = $_POST[$column] ?? null;
                if ($value === null) {
                    $insertMsg = "Please fill out all fields";
                    break;
                }

                $values[] = $value;
                $placeholders[] = "?";
                $columnsToInsert[] = $column;
            }

            if (empty($insertMsg)) {
                $query = "INSERT INTO " . $table_name . " (" . implode(", ", $columnsToInsert) . ") VALUES (" . implode(", ", $placeholders) . ")";
                $stmt = $db->prepare($query);
                $result = $stmt->execute($values);

                if ($stmt === false) {
                    echo "Failed to prepare statement.";
                    exit;
                }

                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    echo "SQL Error: " . $errorInfo[2];
                } else {
                    $insertMsg = "Data successfully inserted!";
                }

            if (isset($insertMsg)) {
                echo $insertMsg;
            }
        }
    }
?>
</body>
</html>