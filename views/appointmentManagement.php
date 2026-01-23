<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";

?>    
    <!-- FullCalendar CSS und JS via CDN -->
    <?php #TODO replace with local link?>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js'></script>
    
    <style>
      body {
        margin: 40px 10px;
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
          initialView: 'dayGridMonth', // monthview
          locale: 'de',                // language german
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          
          // calling events from getEvents.php
          events: 'getEvents.php',

          // Optional: Was passiert beim Klick auf ein Event?
          eventClick: function(info) {
            alert('Event: ' + info.event.title);
            // Hier könnte man z.B. zu einer Bearbeiten-Seite weiterleiten
            // window.location.href = 'edit.php?id=' + info.event.id;
          }
        });

        calendar.render();
      });
    </script>

  </body>
</html>