<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['interaction', 'dayGrid', 'list'],

            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listYear'
            },
            events: [
                <?php
                foreach ($events as $event) {
                    echo "{
                        id: '" . $event['id'] . "',
                        title: '" . $event['title'] . "',
                        start: '" . $event['from_date'] . "',
                        end: '" . $event['to_date'] . "',
                        color: '" . $event['bg_color'] . "',
                        textColor: '" . $event['text_color'] . "',

                    },";
                } ?>
            ],
            // an option!
            // an option!
            displayEventTime: true,
            editable: true,
            selectable: true,
            selectHelper: true,

            // don't show the time column in list view

            eventClick: function(info) {
                console.log(info.event)
                $.ajax({
                    type: "POST",
                    url: base_url + "calendar/get_event_by_id",
                    data: "id=" + info.event.id + "&" + csrfName + "=" + csrfHash,
                    dataType: "json",
                    success: function(result) {
                        csrfName = result.csrfName;
                        csrfHash = result.csrfHash;
                        $('#update_event_title').val(result.title);
                        $('#update_event_id').val(result.id);
                        $('#update_event_end_date').val(result.to_date);
                        $('#update_event_start_date').val(result.from_date);
                        $('#update_background_color').val(result.bg_color);
                        $('#update_text_color').val(result.text_color);
                        $("#customCheck2").prop('checked', false);
                        if (result.is_public == 1) {
                            $("#customCheck2").prop('checked', true);
                        }

                        // }
                        $(".modal-edit-event").trigger("click");

                    }
                });

            },
            dateClick: function(info) {
                var start = moment(info.dateStr).format('DD-MMM-YYYY HH:mm:ss');
                $('#start_date').val(start);
                $('#end_date').val(start);

                $("#modal-add-event").trigger("click");
            },
            select: function(info) {

                var startDate = moment(info.startStr).format('DD-MMM-YYYY HH:mm:ss');
                var endDate = moment(info.endStr).format('DD-MMM-YYYY HH:mm:ss');

                $('#start_date').val(startDate);
                $('#end_date').val(endDate);

                $("#modal-add-event").trigger("click");


            },
            eventDrop: function(info) {

                swal({
                        title: 'Are you sure?',
                        text: 'You want to update event',
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            var start = moment(info.event.start).format('DD-MMM-YYYY HH:mm:ss');
                            var end = moment(info.event.end).format('DD-MMM-YYYY HH:mm:ss');
                            if (end == 'Invalid date') {
                                end = start;
                            }
                            var id = info.event.id;
                            $.ajax({
                                type: "POST",
                                url: base_url + 'calendar/drag',
                                data: "id=" + id + "&start_date=" + start + "&end_date=" + end + "&" + csrfName + "=" + csrfHash,
                                dataType: "json",
                                success: function(result) {
                                    location.reload();
                                    csrfName = result.csrfName;
                                    csrfHash = result.csrfHash;

                                }
                            });
                        } else {
                            info.revert();
                        }
                    });
            },
        });
        calendar.render();
    });
</script>