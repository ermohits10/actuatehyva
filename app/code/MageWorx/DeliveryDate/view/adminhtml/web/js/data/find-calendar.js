define(
    [
        'jquery',
        'underscore',
        'CalendarList',
        'jquery/validate'
    ],
    function ($, _, CalendarList) {
        'use strict';

        return function findCalendar(id) {
            var found;

            CalendarList.forEach(function (calendar) {
                if (calendar.id === id) {
                    found = calendar;
                }
            });

            return found;
        }
    }
);