<?php
require_once("config.php");


// Debugging tools in case something breaks
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
var_dump($_POST);
echo "<br>";
var_dump($_SESSION);
echo "<br>";
echo $_SERVER["REQUEST_METHOD"];
*/

$updateMsg = "";
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
    <?php include("nav.php"); 
    echo "<br>"; 
    echo "<div style='text-align: center; font-size: 30px;'><strong>Update Tool</strong></div>";
    echo "<br>";
    ?>

    <?php $table_name = $_POST['table_name'] ?? null; ?>

    <?php
    if (!empty($updateMsg)) {
        echo "$updateMsg<br>";
        $updateMsg = "";
    }

    $update_form = new PhpFormBuilder();
    $update_form->set_att("method", "POST");

    $db = new PDO('mysql:host=localhost;dbname=atmmanager', 'atmmanager', 'Zyaf&6yud');
    $result = $db->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);

    // Add a select input field to choose a table
    $update_form->add_input("Table Selection", [
        "type" => "select",
        "name" => "table_name",
        "id" => "table_name",
        "options" => array_combine($tables, $tables)
    ]);

    // Add a submit button to submit the form
    $update_form->add_input("submit_select", array(
        "type" => "submit",
        "value" => "Select Table"
    ));

    // If a table has been selected, show the table
    if (!empty($table_name)) {

        $db = get_pdo_connection();

        // Get the primary key column name
        $primaryKeyQuery = $db->prepare("SHOW KEYS FROM $table_name WHERE Key_name = 'PRIMARY'");
        $primaryKeyQuery->execute();
        $primaryKeyColumn = $primaryKeyQuery->fetch(PDO::FETCH_ASSOC)['Column_name'];

        // Add a field to input the primary key of the table
        $update_form->add_input($primaryKeyColumn, array(
            "type" => "number",
            "name" => "primary_key",
            "id" => "primary_key",
            "placeholder" => "Primary Key"
        ));

        // Add a select button for the primary key column
        $update_form->add_input("primary_key", array(
            "type" => "submit",
            "name" => "select_primary_key",
            "value" => "Select $primaryKeyColumn",
            "after_html" => "<br>"
        ));

        
        // If a table has been selected
        if (!empty($table_name)) {

            // If the "Select $primaryKeyColumn" button has been clicked
            if (isset($_POST["select_primary_key"])) {

                // If the primary key field is empty
                if (empty($_POST["primary_key"])) {
                    echo "<h2>Primary Key cannot be blank. :(</h2>";
                } else {

                    // If a primary key has been entered, show the details of the field from the table
                    $primary_key = htmlspecialchars($_POST["primary_key"]);
                    $query = $db->prepare("SELECT * FROM $table_name WHERE $primaryKeyColumn = ?");
                    $query->bindParam(1, $primary_key, PDO::PARAM_INT);

                    // If the query fails to execute, show the error
                    if (!$query->execute()) {    
                        echo "Error executing select query:<br>" . print_r($query->errorInfo(), true);
                    }
                    else {

                        // If the query executes successfully, show the details of the field from the table
                        $result = $query->fetch(PDO::FETCH_ASSOC);

                        // Check if any data was returned
                        if (!$result) {
                            echo "ID not found. :(";
                            exit;
                        }

                        if ($result) {
                            // Show the details of the field from the table
                            echo "<h2>Primary Key $primary_key Details from $table_name:</h2>";
                            echo "<table>";
                            // Show the column names as table headers
                            foreach ($result as $key => $value) {
                                echo "<tr><td>$key</td><td>$value</td></tr>";
                            }
                            echo "</table> <br>";

                            // Add text input fields for each column of the table
                            foreach ($result as $key => $value) {

                                // Don't add a text input field for the primary key column
                                if ($key != $primaryKeyColumn) {

                                    // If the column name contains "hour", add a time input field
                                    if (stripos($key, "hour") !== false) {
                                        $update_form->add_input($key, array(
                                            "type" => "time",
                                            "name" => $key,
                                            "id" => $key,
                                            "placeholder" => $key,
                                            "value" => $value
                                        ));
                                    }

                                    // If the column name is "Branch_ID", add a select input field for the column
                                    else if ($key == "Branch_ID") {
                                        // Fetch all the branches
                                        $stmt = $db->prepare("SELECT Branch_ID, Name FROM Branch");
                                        $stmt->execute();
                                        $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        // Create an array to hold the options for the dropdown menu
                                        $options = array();
                                        foreach ($branches as $branch) {
                                            // The key is the Branch_ID and the value is the "Branch_ID - Name" string
                                            $options[$branch['Branch_ID']] = $branch['Branch_ID'] . ' - ' . $branch['Name'];
                                        }

                                        // Add a select input field for the column
                                        $update_form->add_input($key, array(
                                            "type" => "select",
                                            "name" => $key,
                                            "id" => $key,
                                            "options" => $options,
                                            "selected" => $value
                                        ));
                                    }

                                    // If the column name is "Bank_ID", add a select input field for the column
                                    else if ($key == "Bank_ID") {
                                        // Fetch all the banks
                                        $stmt = $db->prepare("SELECT Bank_ID, Name FROM Bank");
                                        $stmt->execute();
                                        $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        // Create an array to hold the options for the dropdown menu
                                        $options = array();
                                        foreach ($banks as $bank) {
                                            // The key is the Bank_ID and the value is the "Bank_ID - Name" string
                                            $options[$bank['Bank_ID']] = $bank['Bank_ID'] . ' - ' . $bank['Name'];
                                        }

                                        // Add a select input field for the column
                                        $update_form->add_input($key, array(
                                            "type" => "select",
                                            "name" => $key,
                                            "id" => $key,
                                            "options" => $options,
                                            "selected" => $value
                                        ));
                                    }

                                    // If the column name contains "date", add a date input field
                                    else if (stripos($key, "date") !== false) {
                                        $update_form->add_input($key, array(
                                            "type" => "date",
                                            "name" => $key,
                                            "id" => $key,
                                            "placeholder" => $key,
                                            "value" => $value
                                        ));
                                    }

                                    // If the column name contains "Transaction_Type", add a select input field for the column
                                    else if (stripos($key, "Transaction_Type") !== false) {
                                        $update_form->add_input($key, array(
                                            "type" => "select",
                                            "name" => $key,
                                            "id" => $key,
                                            "placeholder" => $key,
                                            "options" => array(
                                                "Credit" => "Credit",
                                                "Debit" => "Debit"
                                            ),
                                            "selected" => $value
                                        ));
                                    }

                                    // If the column name is "Department_ID", add a select input field for the column
                                    else if ($key == "Department_ID") {
                                        // Fetch all the departments
                                        $stmt = $db->prepare("SELECT Department_ID, Department_Name FROM Department");
                                        $stmt->execute();
                                        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        // Create an array to hold the options for the dropdown menu
                                        $options = array();
                                        foreach ($departments as $department) {
                                            // The key is the Department_ID and the value is the "Department_ID - Name" string
                                            $options[$department['Department_ID']] = $department['Department_ID'] . ' - ' . $department['Department_Name'];
                                        }
                                    }

                                    // For all other column names, add a text input field for the column
                                    else {
                                        $update_form->add_input($key, array(
                                            "type" => "text",
                                            "name" => $key,
                                            "id" => $key,
                                            "placeholder" => $key,
                                            "value" => $value
                                        ));
                                    }
                                }
                            }
                        }

                        // Add a submit button to the form
                        $update_form->add_input('update', array(
                            "type" => "submit",
                            "name" => "update",
                            "value" => "Update"
                        ));

                        
                    }
                }
            }

            // If the form is submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {

                $primary_key = $_POST["primary_key"];

                // Fetch the row from the database again
                $query = $db->prepare("SELECT * FROM $table_name WHERE $primaryKeyColumn = ?");
                $query->bindParam(1, $primary_key, PDO::PARAM_INT);
                if (!$query->execute()) {    
                    echo "Error executing select query:<br>" . print_r($query->errorInfo(), true);
                }
                else {
                    $result = $query->fetch(PDO::FETCH_ASSOC);
                }

                // Build an SQL UPDATE statement
                $sql = "UPDATE $table_name SET ";
                $params = array();
                foreach ($result as $key => $value) {
                    if ($key != $primaryKeyColumn) {
                        $sql .= "$key = :$key, ";
                        $params[":$key"] = $_POST[$key];
                    }
                }
            
                $sql = rtrim($sql, ', ') . " WHERE $primaryKeyColumn = :primaryKey";
                $params[':primaryKey'] = $primary_key;

                // Prepare the SQL UPDATE statement
                $stmt = $db->prepare($sql);
                $success = $stmt->execute($params);

                if ($success) {
                    echo "Record updated successfully!";
                } else {
                    echo "Error updating record:<br>" . print_r($stmt->errorInfo(), true);
                }
            }
        }
    }
    
    $update_form->build_form();

?>
