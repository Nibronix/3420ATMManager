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
        echo "<br>";
        echo "<div style='text-align: center; font-size: 30px;'><strong>Delete Tool</strong></div>";
        echo "<br>";
        ?>
        <?php
        $select_form = new PhpFormBuilder();
        $select_form->set_att("method", "POST");

        // Create select form for table selection
        $db = get_pdo_connection();
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
        $select_form->build_form();

        // Create delete form
        $delete_form = new PhpFormBuilder();
        $delete_form->set_att("method", "POST");

        if (isset($_POST['table_name'])) {
            $table_name = $_POST['table_name'];
            $query = $db->prepare("DESCRIBE $table_name");
            $query->execute();
            $attributes = $query->fetchAll(PDO::FETCH_COLUMN);
            $options = array_combine($attributes, $attributes);

            $delete_form->add_input("Attribute", [
                "type" => "select",
                "name" => "attribute",
                "id" => "attribute",
                "options" => $options
            ]);

            $delete_form->add_input("Delete Row", [
                "type" => "text",
                "name" => "delete_data",
                "id" => "delete_data"
            ]);

            $delete_form->add_input("Delete", [
                "type" => "submit",
                "name" => "delete",
                "value" => "Delete"
            ]);

            $delete_form->add_input("table_name", [
                "type" => "hidden",
                "name" => "table_name",
                "value" => $table_name
            ]);
        }

        if (isset($_POST["delete"])) {
            if (empty($table_name)) {
                echo "Error: No table was selected :( <br>";
                return;
            }

            if (!empty($_POST["delete_data"]) && !empty($_POST["attribute"])) {
                $attribute = $_POST["attribute"];
                $delete_data = $_POST["delete_data"];
                $sql_query = "DELETE FROM `{$table_name}` WHERE `{$attribute}` = :deleteData";
                $query = $db->prepare($sql_query);
                $query->bindValue(':deleteData', $delete_data, PDO::PARAM_STR);
                if ($query->execute()) {
                    echo "Deleted rows: " . $query->rowCount() . "<br>";
                } else {
                    echo "Error executing delete query:<br>";
                    print_r($query->errorInfo());
                }
            } else {
                echo "Error: No delete data or attribute specified<br>";
            }
        }

        echo $delete_form->build_form();
        ?>
    </body>
</html>

