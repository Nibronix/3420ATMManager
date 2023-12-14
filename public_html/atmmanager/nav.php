<!DOCTYPE html>
<html>
<head>
    <style>
    .dropdown {
        position: absolute;
        top: 0;
        right: 0;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropbtn {
        background-color: #00E8EA;
        color: white;
        padding: 16px;
        font-size: 16px;
        border: none;
        cursor: pointer;
    }
    </style>
</head>
<body>

<?php
$pages = ["select", "insert", "update", "delete"];
?>
<nav>
    <a href="locator.php">Locator</a>
    <div class="dropdown">
        <button class="dropbtn">Database Management</button>
        <div class="dropdown-content">
        <?php
        foreach($pages as $page) {
            echo "<a href=\"$page.php\">" . ucfirst($page) . "</a>";
        }
        ?>
        </div>
    </div>
</nav>

</body>
</html>