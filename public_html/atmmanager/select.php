<?php
require_once("config.php");
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
    <h2>SQL SELECT -> HTML Table using <a href="https://www.php.net/manual/en/book.pdo.php">PDO</a></h2>
    <?php

$table_name = $_POST['table_name'] ?? null;
?>


<?php
$select_form = new PhpFormBuilder();
$select_form->set_att("method", "POST");

/**
 * Creates a select form to choose a table to search in, and a search form to search for data in the selected table.
 * If a table is selected and a search is performed, the results are displayed in a table.
 */

// Create select form
$select_form->add_input("Table Selection", [
    "type" => "select",
    "name" => "table_name",
    "id" => "table_name",
    "options" => [
        "ATM" => "ATM",
        "Account_Information" => "Account Information",
        "Bank" => "Bank",
        "Branch" => "Branch",
        "Employee" => "Employee",
        "Transaction" => "Transaction",
        "User" => "User"
    ]
]);

$select_form->add_input("submit", array(
    "type" => "submit",
    "value" => "Select"
));

// Create search form
$search_form = new PhpFormBuilder();
$search_form->set_att("method", "POST");

if (isset($_POST['table_name'])) {
    // If a table is selected, get its attributes to use as search options
    $table_name = $_POST['table_name'];
    $db = get_pdo_connection();
    $query = $db->prepare("DESCRIBE $table_name");
    $query->execute();
    $attributes = $query->fetchAll(PDO::FETCH_COLUMN);
    $options = array_combine($attributes, $attributes);
} else {
    // If no table is selected, set the search options to an empty array
    $options = [];
}

$search_form->add_input("Attribute", [
    "type" => "select",
    "name" => "attribute",
    "id" => "attribute",
    "options" => $options
]);

$search_form->add_input("Search Data", [
    "type" => "text",
    "name" => "search_data",
    "id" => "search_data"
]);

$search_form->add_input("Search", [
    "type" => "submit",
    "name" => "search",
    "value" => "Search"
]);

if (isset($_POST["search"])) {
    echo "Searching...<br>";

    $db = get_pdo_connection();
    $query = false;

    if (empty($table_name)) {
        // If no table is selected, display an error message
        echo "Error: No table selected. :(<br>";
        return;
    }

    if (!empty($_POST["search_id"])) {
        // If an ID is specified, search for it in the selected table
        $search_id = $_POST["search_id"];
        $query = $db->prepare("SELECT * FROM {$table_name} WHERE id LIKE ?");
        $query = $db->prepare($sql_query);
        $query->execute(array("%{$search_id}%"));
    }
    else if (!empty($_POST["search_data"]) && !empty($_POST["Attributes"])) {
        // If search data and an attribute are specified, search for data in the selected attribute of the selected table
        $attribute = $_POST["Attributes"];
        $search_data = $_POST["search_data"];
        $sql_query = "SELECT * FROM `{$table_name}` WHERE `{$attribute}` LIKE '%{$search_data}%'";
        $query = $db->prepare($sql_query);
        $query->bindValue(1, "%{$search_data}%", PDO::PARAM_STR);
        $query->execute();
    }
    else {
        // If no search criteria are specified, select all data from the selected table
        $sql_query = "SELECT * FROM `{$table_name}`";
        $query = $db->prepare($sql_query);
        $query->execute();
    }

    if ($query) {
        if ($query->execute()) {
            // If the query is successful, display the results in a table
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            echo makeTable($rows);
        }
        else {
            // If the query fails, display an error message
            echo "Error executing select query:<br>";
            print_r($query->errorInfo());
        }
    }
    else{
        // If no search criteria are specified, display an error message
        echo "Error executing select query: no id or data specified<br>";
    }
}



// Generates query forms
$select_form->build_form();
echo $search_form->build_form();

// Fetches data from selected table
$db = get_pdo_connection();
$query = $db->prepare("SELECT * FROM $table_name");
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_ASSOC);

// Outputs table results
echo makeTable($rows);

?>
</body>
</html>