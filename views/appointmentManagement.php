<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";

?>

<script src='../resources/js/fullCalendar.min.js'></script>
<script src='../resources/js/fullCalendarBootstrapPlugin.js'></script>
<style>
  body {
    padding: 0;
    font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
    font-size: 14px;
  }

  #calendar {
    max-width: 1100px;
    margin: 0 auto;
  }
</style>
</head>

<body>

  <div id='calendar'></div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');

      var calendar = new FullCalendar.Calendar(calendarEl, {
        themeSystem: 'bootstrap5',
        initialView: 'dayGridMonth',
        locale: 'de',
        firstDay: 1,

        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
          today: 'Heute',
          month: 'Monat',
          week: 'Woche',
          day: 'Tag',
          list: 'Liste'
        },

        // calling events from getEvents.php
        events: '../controllers/getEvents.php',


        eventClick: function(info) {
          alert('Termin\n Title: ' + info.event.title + '\n Beschreibung: ' + info.event.extendedProps.description + '\n Beginn: ' + info.event.start + '\n Ende: ' + info.event.end);
        }
      });

      calendar.render();
    });
  </script>

  <?php
   include "footer.php" 
  ?>