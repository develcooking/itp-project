function changeSubBtnStatus(status) {
    if (status) {
        const modal_submit_btn = document.getElementById('modal_submit_btn');
        if (status === true) {
            if (modal_submit_btn.classList.contains('disabled')) {
                modal_submit_btn.classList.remove('disabled');
            }
            modal_submit_btn.setAttribute("aria-disabled", "false");
        } else {
            if (!modal_submit_btn.classList.contains('disabled')) {
                modal_submit_btn.classList.add('disabled');
            }
            modal_submit_btn.setAttribute("aria-disabled", "true");
        }
    }
}
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



        dateClick: function (info) {
            var myModal = new bootstrap.Modal(document.getElementById("calendermanagementModal"));

            // set the startdate and enddate to the user clicked date
            let startdate= document.getElementById('startdate');
            let enddate= document.getElementById('enddate');
            startdate.value = info.dateStr;
            enddate.value = info.dateStr;

            // check for right format
            const modal_submit_btn = document.getElementById('modal_submit_btn');
            startdate.addEventListener("change", (event) => {
                result.textContent = `You like ${event.target.value}`;
            });
            //alert('Termin\n Title: ' + info.event.title + '\n Beschreibung: ' + info.event.extendedProps.description + '\n Beginn: ' + info.event.start + '\n Ende: ' + info.event.end);
            myModal.show();
        }
    });

    calendar.render();
});