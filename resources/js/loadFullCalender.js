document.addEventListener('DOMContentLoaded', function () {
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


        eventClick: function (info) {
            alert('Termin\n Title: ' + info.event.title + '\n Beschreibung: ' + info.event.extendedProps.description + '\n Beginn: ' + info.event.start + '\n Ende: ' + info.event.end);
        }
    });

    calendar.render();
});