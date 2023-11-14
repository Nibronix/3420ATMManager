<?php
require_once("config.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
    <?php echo "<br>"; ?>

    <!-- Form to select a table to insert data into -->
    <?php $table_name = $_POST['table_name'] ?? null; ?>
    <?php
    if (!empty($insertMsg)) {
        echo "$insertMsg<br>";
        $insertMsg = "";
    }

    $insert_form = new PhpFormBuilder();
    $insert_form->set_att("method", "POST");

    $db = new PDO('mysql:host=localhost;dbname=atmmanager', 'atmmanager', 'Zyaf&6yud');

    $result = $db->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);

    $insert_form->add_input("Table Selection", [
        "type" => "select",
        "name" => "table_name",
        "id" => "table_name",
        "options" => array_combine($tables, $tables)
    ]);

    $insert_form->add_input("submit_select", array(
        "type" => "submit",
        "value" => "Select"
    ));

    $insert_form->build_form();

    // If a table has been selected, show a form to insert data into that table
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["table_name"])) {
        $_SESSION["table_name"] = $_POST["table_name"];
        $table_name = $_POST["table_name"];
        echo "You selected " . $table_name;

        $result = $db->query("DESCRIBE $table_name");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN);

        $insert_form = new PhpFormBuilder();
        $insert_form->set_att("method", "POST");

        // Add input fields for each column in the selected table
        foreach ($columns as $column) {
            if (strpos($column, "ID") !== false || strpos($column, "Tokenization") !== false) {
                continue;
            }

            $insert_form->add_input($column, [
                "type" => "text",
                "name" => $column,
                "id" => $column
            ]);
        }

        $insert_form->add_input("submit_insert", array(
            "type" => "submit",
            "value" => "Insert"
        ));

        $insert_form->build_form();
    }

    // If the insert form has been submitted, insert the data into the table
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_insert"])) {
        $table_name = $_SESSION["table_name"];
        $values = array();
            foreach ($columns as $column) {
                if (strpos($column, "ID") !== false || strpos($column, "Tokenization") !== false) {
                    continue;
                }

                $value = $_POST[$column] ?? null;
                if ($value === null) {
                    $insertMsg = "Please fill out all fields";
                    break;
                }

                $values[] = $value;
            }

            if (empty($insertMsg)) {
                $query = "INSERT INTO $table_name (" . implode(", ", $columns) . ") VALUES (" . implode(", ", array_fill(0, count($values), "?")) . ")";
                $stmt = $db->prepare($query);
                $result = $stmt->execute($values);

                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    echo "SQL Error: " . $errorInfo[2];
                } else {
                    if ($result) {
                        $insertMsg = "Data successfully inserted!";
                    } else {
                        $insertMsg = "Failed to insert data :(";
                    }
                }
            }
        }
    
    ?>
</body>
</html>