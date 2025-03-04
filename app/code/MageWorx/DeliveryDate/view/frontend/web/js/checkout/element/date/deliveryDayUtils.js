define(
    [
        'underscore',
        'moment',
        'moment-timezone-with-data',
        'jsDate'
    ],
    function (_, moment, tz) {
        "use strict";

        return {

            getDayByIndexFromToday: function getDayByIndexFromToday(index) {
                var dayByIndex = moment().add(index, 'd').format('YYYY-MM-DD');

                return dayByIndex.toString();
            },

            /**
             * Calculate day index from current date (rounded up)
             *
             * @param date
             * @returns {number}
             */
            getDayIndexFromToday: function getDayIndexFromToday(date) {
                var today, diff, dateMoment,
                    now = mwDeliveryDateConfig.now,
                    timezoneOffset = (new Date()).getTimezoneOffset();

                today = now ? new Date(now) : new Date();

                dateMoment = new Date(moment(date).toDate());
                dateMoment.setHours(today.getHours());
                dateMoment.setMinutes(today.getMinutes() + 1);

                diff = this.getDiffInDays(dateMoment, today);

                if (diff < 0) {
                    return -1;
                } else if (diff === 0) {
                    var result = dateMoment.getFullYear() === today.getFullYear() &&
                        dateMoment.getMonth() === today.getMonth() &&
                        dateMoment.getDay() === today.getDay();

                    if (result) {
                        return 0;
                    } else {
                        return -1;
                    }
                }

                return diff;
            },

            /**
             * Get difference in days: date - anotherDate
             *
             * @param date
             * @param anotherDate
             * @returns {number}
             */
            getDiffInDays: function (date, anotherDate) {
                return Math.floor(
                    (
                        Date.UTC(
                            date.getFullYear(),
                            date.getMonth(),
                            date.getDate()
                        )
                        - Date.UTC(
                            anotherDate.getFullYear(),
                            anotherDate.getMonth(),
                            anotherDate.getDate()
                        )
                    ) / (1000 * 60 * 60 * 24));
            },

            /**
             * Get day index and return formatted date
             *
             * @param dayIndex
             * @param format
             * @returns {*}
             */
            createDateObjectFromDayIndexFromToday: function (dayIndex, format) {
                var result,
                    now = mwDeliveryDateConfig.now,
                    dateString = now,
                    date = new Date(dateString),
                    intDays = parseInt(dayIndex),
                    dateDays = date.getDate(),
                    dateDaysPlusDays = dateDays + intDays;
                date.setDate(dateDaysPlusDays);

                result = moment(date).locale('en').format(format);

                if (result === 'Invalid date') {
                    result = '';
                }

                return result;
            }
        };
    }
);
