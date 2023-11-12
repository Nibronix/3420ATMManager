<?php
$pages = ["select", "insert", "update", "delete"];
?>
<nav>
<?php
foreach($pages as $page) {
    echo "<a href=\"$page.php\">$page</a>";
}
?>
</nav>