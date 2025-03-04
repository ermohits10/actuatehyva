var config = {
    paths: {
        'CalendarList': 'MageWorx_DeliveryDate/js/data/calendars',
        'findCalendar': 'MageWorx_DeliveryDate/js/data/find-calendar',
        'schedules': 'MageWorx_DeliveryDate/js/data/schedules',
        'customTimePicker': 'MageWorx_DeliveryDate/js/jquery.timepicker'
    },
    shim: {
        'customTimePicker': {
            deps: [
                'jquery'
            ]
        }
    }
};
