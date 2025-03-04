define(
    [
        'jquery',
        'CalendarList',
        'moment',
        'underscore',
        'mage/translate',
        'jquery/validate'
    ],
    function ($, CalendarList, moment, _, $tr) {
        'use strict';

        function ScheduleInfo() {
            this.id = null;
            this.calendarId = null;

            this.title = null;
            this.isAllday = false;
            this.start = null;
            this.end = null;
            this.category = '';
            this.dueDateClass = '';

            this.color = null;
            this.bgColor = null;
            this.dragBgColor = null;
            this.borderColor = null;
            this.customStyle = '';

            this.isFocused = false;
            this.isPending = false;
            this.isVisible = true;
            this.isReadOnly = false;

            this.raw = {
                memo: '',
                hasToOrCc: false,
                hasRecurrenceRule: false,
                location: null,
                class: 'public', // or 'private'
                creator: {
                    name: '',
                    avatar: '',
                    company: '',
                    email: '',
                    phone: ''
                },
                deliveryData: {
                    "day": null,
                    "time_from": null,
                    "time_to": null,
                    "available": null,
                    "reserved": null
                }
            };
        }

        /**
         * Search in array
         *
         * @param nameKey
         * @param prop
         * @param myArray
         * @returns {*}
         */
        function searchByValueInArray(nameKey, prop, myArray) {
            for (var i = 0; i < myArray.length; i++) {
                if (String(myArray[i][prop]) === String(nameKey)) {
                    return myArray[i];
                }
            }

            return null;
        }

        var schedules = {
            ScheduleList: [],

            SCHEDULE_CATEGORY: [
                'milestone',
                'task'
            ],

            generateTime: function generateTime(schedule, renderStart, renderEnd) {
                var startDate = moment(renderStart.getTime());
                var endDate = moment(renderEnd.getTime());

                schedule.category = 'allday';
                schedule.start = startDate.toDate();
                schedule.end = endDate.toDate();
            },

            generateSchedule: function generateSchedule(viewName, renderStart, renderEnd) {
                this.ScheduleList = [];

                this.generateSchedulesForCalendars();
            },

            /**
             * Parse config and create schedules according provided data
             * @see schema_example.json
             */
            generateSchedulesForCalendars: function generateSchedulesForCalendars() {
                if (!_.isUndefined(window.mageworx.delivery_date.config.list.delivery_option.days) &&
                    window.mageworx.delivery_date.config.list.delivery_option.days.length > 0
                ) {
                    var daysList = window.mageworx.delivery_date.config.list.delivery_option.days,
                        deliveryOptionId = window.mageworx.delivery_date.config.list.delivery_option.id;

                    for (var key in daysList) {
                        if (!daysList.hasOwnProperty(key)) {
                            continue;
                        }

                        var currentDay = daysList[key],
                            calendar,
                            prevDay;

                        if (currentDay.holiday === true) {
                            calendar = searchByValueInArray('holiday', 'id', CalendarList);
                            this.addHolidaySchedule(calendar, currentDay, key);
                        }

                        if (currentDay.non_working_day === true) {
                            calendar = searchByValueInArray('non_working', 'id', CalendarList);
                            this.addNonWorkingDaysSchedule(calendar, currentDay, key);
                        }

                        var currentDayLimits = currentDay.limits,
                            limitsAvailable = !(typeof currentDayLimits === 'undefined' || currentDayLimits.length === 0),
                            singleQueue = !limitsAvailable;

                        if (currentDay.reserved > 0 || limitsAvailable) {
                            calendar = searchByValueInArray(deliveryOptionId, 'id', CalendarList);
                            if (calendar === null) {
                                console.log('Cant find calendar by delivery id saved in queue');
                                continue;
                            }

                            if (singleQueue) {
                                this.addSingleQueueSchedule(calendar, currentDay, key);
                            } else {
                                this.addTimedQueueSchedule(calendar, currentDay, key);
                            }
                        }

                        prevDay = daysList[key];
                    }
                }
            },

            /**
             * Adds schedule as a holiday
             *
             * @param calendar
             * @param currentDay
             * @param dayKey
             */
            addHolidaySchedule: function (calendar, currentDay, dayKey) {
                var schedule = new ScheduleInfo(),
                    title = $tr('Holiday');

                schedule.id = calendar.id + '_' + dayKey;
                schedule.calendarId = calendar.id;

                schedule.title = title;
                schedule.isReadOnly = true;
                schedule.isAllday = true;

                var timezoneOffset = (new Date()).getTimezoneOffset(),
                    dateFrom = new Date(new Date(currentDay.date).setMinutes(timezoneOffset)),
                    dateTo = new Date(new Date(currentDay.date).setMinutes(timezoneOffset));

                schedules.generateTime(
                    schedule,
                    dateFrom,
                    dateTo
                );

                // Delivery data
                schedule.raw.deliveryData.day = currentDay.date;
                schedule.raw.deliveryData.available = currentDay.available;
                schedule.raw.deliveryData.reserved = currentDay.reserved;
                schedule.raw.deliveryData.orderAddressIds = currentDay.order_address_ids;

                schedule.color = calendar.color; //'#ffffff';
                schedule.bgColor = calendar.bgColor; //'#6496ff';
                schedule.dragBgColor = calendar.dragBgColor; //'#3264ff';
                schedule.borderColor = calendar.borderColor; //'#6496ff';

                this.ScheduleList.push(schedule);
            },

            /**
             * Adds schedule as an unavailable
             *
             * @param calendar
             * @param currentDay
             * @param dayKey
             */
            addNonWorkingDaysSchedule: function (calendar, currentDay, dayKey) {
                var schedule = new ScheduleInfo(),
                    title = $tr('Non working days');

                schedule.id = calendar.id + '_' + dayKey;
                schedule.calendarId = calendar.id;

                schedule.title = title;
                schedule.isReadOnly = true;
                schedule.isAllday = true;

                var timezoneOffset = (new Date()).getTimezoneOffset(),
                    dateFrom = new Date(new Date(currentDay.date).setMinutes(timezoneOffset)),
                    dateTo = new Date(new Date(currentDay.date).setMinutes(timezoneOffset));

                schedules.generateTime(
                    schedule,
                    dateFrom,
                    dateTo
                );

                // Delivery data
                schedule.raw.deliveryData.day = currentDay.date;
                schedule.raw.deliveryData.available = currentDay.available;
                schedule.raw.deliveryData.reserved = currentDay.reserved;
                schedule.raw.deliveryData.orderAddressIds = currentDay.order_address_ids;

                schedule.color = calendar.color; //'#ffffff';
                schedule.bgColor = calendar.bgColor; //'#6496ff';
                schedule.dragBgColor = calendar.dragBgColor; //'#3264ff';
                schedule.borderColor = calendar.borderColor; //'#6496ff';

                this.ScheduleList.push(schedule);
            },

            /**
             * Adds single queue schedule (1 queue for 1 day as 1 row/record)
             *
             * @param calendar
             * @param currentDay
             * @param dayKey
             */
            addSingleQueueSchedule: function (calendar, currentDay, dayKey) {
                var schedule = new ScheduleInfo(),
                    title = '';

                schedule.id = calendar.id + '_' + dayKey;
                schedule.calendarId = calendar.id;

                if (currentDay.reserved) {
                    title += currentDay.reserved;
                }

                if (currentDay.available && !currentDay.holiday && !currentDay.non_working_day) {
                    title += '/' + currentDay.available;
                }

                schedule.title = title;
                schedule.isReadOnly = false;

                var timezoneOffset = (new Date()).getTimezoneOffset(),
                    dateFrom = new Date(new Date(currentDay.date).setMinutes(timezoneOffset)),
                    dateTo = new Date(new Date(currentDay.date).setMinutes(timezoneOffset));

                dateFrom = new Date(dateFrom.setHours(0, 0));
                dateTo = new Date(dateTo.setHours(23, 59, 59));

                schedules.generateTime(
                    schedule,
                    dateFrom,
                    dateTo
                );

                // Delivery data
                schedule.raw.deliveryData.day = currentDay.date;
                schedule.raw.deliveryData.available = currentDay.available;
                schedule.raw.deliveryData.reserved = currentDay.reserved;
                schedule.raw.deliveryData.orderAddressIds = currentDay.order_address_ids;

                schedule.color = calendar.color; //'#ffffff';
                schedule.bgColor = calendar.bgColor; //'#6496ff';
                schedule.dragBgColor = calendar.dragBgColor; //'#3264ff';
                schedule.borderColor = calendar.borderColor; //'#6496ff';

                this.ScheduleList.push(schedule);
            },

            addTimedQueueSchedule: function addTimedQueueSchedule(calendar, currentDay, dayKey) {
                var currentDayLimits = currentDay.limits;

                for (var key in currentDayLimits) {
                    // key == '5:30_10:45'
                    var parts = key.split('_'),
                        partFrom = parts[0],
                        partTo = parts[1],
                        fromParts = partFrom.split(':'),
                        toParts = partTo.split(':'),
                        fromHour = Number(fromParts[0]),
                        fromMinute = Number(fromParts[1]),
                        toHour = Number(toParts[0]),
                        toMinutes = Number(toParts[1]);

                    var timezoneOffset = (new Date()).getTimezoneOffset(),
                        dateFrom = new Date(new Date(currentDay.date).setMinutes(timezoneOffset)),
                        dateTo = new Date(new Date(currentDay.date).setMinutes(timezoneOffset)),
                        currentDayLimit = currentDayLimits[key];

                    if (fromHour && fromMinute) {
                        dateFrom = new Date(dateFrom.setHours(fromHour, fromMinute));
                    } else {
                        dateFrom = new Date(dateFrom.setHours(0, 0));
                    }

                    if (toHour && toMinutes) {
                        if (toHour === 24) {
                            dateTo = new Date(dateTo.setHours(23, 59, 59));
                        } else {
                            dateTo = new Date(dateTo.setHours(toHour, toMinutes));
                        }
                    } else {
                        dateTo = new Date(dateTo.setHours(23, 59));
                    }

                    var schedule = new ScheduleInfo(),
                        fromMinuteString = fromMinute === 0 ? '00' : String(fromMinute),
                        toMinutesString = toMinutes === 0 ? '00' : String(toMinutes);

                    if (fromMinuteString.length === 1) {
                        fromMinuteString = '0' + fromMinuteString;
                    }

                    if (toMinutesString.length === 1) {
                        toMinutesString = '0' + toMinutesString;
                    }

                    var title = fromHour + ':' + fromMinuteString + ' - ' + toHour + ':' + toMinutesString;

                    title += ' (';
                    if (currentDayLimit.reserved) {
                        title += currentDayLimit.reserved;
                    }
                    if (currentDayLimit.available) {
                        title += '/' + currentDayLimit.available;
                    }
                    title += ')';

                    schedule.title = title;
                    schedule.id = calendar.id + '_' + dayKey + '_' + key;
                    schedule.calendarId = calendar.id;
                    schedule.isReadOnly = false;
                    schedule.color = '#4286f4';
                    schedule.bgColor = '#ffffff';
                    schedule.dragBgColor = '#3264ff';
                    schedule.borderColor = '#6496ff';

                    schedules.generateTime(
                        schedule,
                        dateFrom,
                        dateTo
                    );

                    // Delivery data
                    schedule.raw.deliveryData.day = currentDay.date;
                    schedule.raw.deliveryData.available = currentDayLimit.available;
                    schedule.raw.deliveryData.reserved = currentDayLimit.reserved;
                    schedule.raw.deliveryData.orderAddressIds = currentDayLimit.order_address_ids;

                    this.ScheduleList.push(schedule);
                }
            },

            getScheduleList: function getScheduleList() {
                return this.ScheduleList;
            }
        };

        return schedules;
    }
);
