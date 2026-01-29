<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";

?>

<script src='../resources/js/fullCalendar.min.js'></script>
<script src='../resources/js/fullCalendarBootstrapPlugin.js'></script>

<div class="calendar-container">

  <div id='calendar'></div>
</div>
<script src="/resources/js/loadFullCalender.js"></script>

<?php
include "footer.php"
?>