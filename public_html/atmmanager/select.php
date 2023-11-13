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
    <?php

$table_name = $_POST['table_name'] ?? null;
?>

<?php
echo "<br><br>";
?>

<?php
$select_form = new PhpFormBuilder();
$select_form->set_att("method", "POST");

/**
 * Creates a select form to choose a table to search in, and a search form to search for data in the selected table.
 * If a table is selected and a search is performed, the results are displayed in a table.
 */

// Create select form

$db = new PDO('mysql:host=localhost;dbname=atmmanager', 'atmmanager', 'Zyaf&6yud');

$result = $db->query("SHOW TABLES");
$tables = $result->fetchAll(PDO::FETCH_COLUMN);

$select_form->add_input("Table Selection", [
    "type" => "select",
    "name" => "table_name",
    "id" => "table_name",
    "options" => array_combine($tables, $tables)
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

$search_form->add_input("table_name", [
    "type" => "hidden",
    "name" => "table_name",
    "value" => $table_name
]);


if (isset($_POST["search"])) {
    $db = get_pdo_connection();
    $query = null;

    if (empty($table_name)) {
        echo "Error: No table was selected :( <br>";
        return;
    }

    if (!empty($_POST["search_data"]) && !empty($_POST["attribute"])) {
        $attribute = $_POST["attribute"];
        $search_data = $_POST["search_data"];
        $sql_query = "SELECT * FROM `{$table_name}` WHERE `{$attribute}` LIKE :searchData";
        $query = $db->prepare($sql_query);
        $query->bindValue(':searchData', "%{$search_data}%", PDO::PARAM_STR);
    } else {
        $sql_query = "SELECT * FROM `{$table_name}`";
        $query = $db->prepare($sql_query);
    }

    if ($query->execute()) {
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);
        echo makeTable($rows);
    } else {
        echo "Error executing select query:<br>";
        print_r($query->errorInfo());
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
