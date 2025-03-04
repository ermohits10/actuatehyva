# MageWorx_DeliveryDate

[Magento 2 Delivery Date](https://www.mageworx.com/delivery-date-magento-2.html) extension lets you optimize your delivery 
mechanisms by smart distribution of your delivery resources. With the extension, you can let customer choose the desired 
delivery date and time intervals. Also, you can set limits to eliminate the possibility of the shipments 
overbooking on the chosen day. It allows to define the correct and appropriate orders delivery schedule and exclude 
some special days (like holidays, days off etc).

## Installation

**1) Installation using composer (from packagist)**
- Execute the following command: `composer require mageworx/module-deliverydate`

**2) Copy-to-paste method**
- Download this module and upload it to the `app/code/MageWorx/DeliveryDate` directory *(create "DeliveryDate" first if missing)*

## How to use

### REST API Support

Get the Order Delivery Date by Order ID

The extension supports Magento 2 standard API to retrieve the details for specific orders.

**Request Format**:

> GET /V1/orders/{order_id}

**Example**:

> GET /V1/orders/68

**Response JSON example**:

```json
"extension_attributes": {
    "delivery_day": "2019-05-08",
    "delivery_hours_from": "12",
    "delivery_minutes_from": "31",
    "delivery_hours_to": "14",
    "delivery_minutes_to": "55",
    "delivery_comment": "Please call me before the delivery.",
    "delivery_time": "12:31_14:55"
}
```

**Display the delivery slots in 3rd party front-ends**

The extension allows you to retrieve the available & occupied delivery dates & time slots for certain quotes, 
even created in 3rd party front-ends (like during the mobile apps checkout).

**Request format**:

> GET /V1/delivery_date/{quote_id}/{advanced_flag}

The "advanced_flag" can be 0 or 1. If it is set to 0, the request will return the available delivery date & time slots only. 
If it is set to 1, the request will return the available AND occupied delivery date & time slots. 
It might be helpful if you need to disable the unavailable dates as well.

**Example**:

> GET /V1/delivery_date/2/1

**Response JSON example**:

```json
[
    {
        "entity_id": "1",
        "name": "Default Delivery Configuration",
        "methods": null,
        "is_active": "1",
        "sort_order": "1",
        "future_days_limit": "3",
        "start_days_limit": "0",
        "active_from": null,
        "active_to": null,
        "limits_serialized": {
            "default": {
                "time_limits": [
                    {
                        "from": "12:00",
                        "to": "13:00",
                        "quote_limit": "",
                        "extra_charge": "",
                        "position": "1",
                        "record_id": "0",
                        "initialize": "true"
                    },
                    {
                        "record_id": "1",
                        "from": "14:00",
                        "to": "21:00",
                        "quote_limit": "",
                        "extra_charge": "",
                        "position": "2",
                        "initialize": "true"
                    },
                    {
                        "record_id": "2",
                        "from": "10:00",
                        "to": "11:00",
                        "quote_limit": "",
                        "extra_charge": "",
                        "position": "3",
                        "initialize": "true"
                    },
                    {
                        "record_id": "3",
                        "from": "09:00",
                        "to": "10:00",
                        "quote_limit": "",
                        "extra_charge": "",
                        "position": "4",
                        "initialize": "true"
                    },
                    {
                        "record_id": "4",
                        "from": "08:00",
                        "to": "09:00",
                        "quote_limit": "",
                        "extra_charge": "",
                        "position": "5",
                        "initialize": "true"
                    }
                ],
                "daily_quotes": "",
                "extra_charge": "",
                "active": "1"
            },
            "sunday": {
                "daily_quotes": "",
                "active": "0"
            },
            "monday": {
                "daily_quotes": "",
                "extra_charge": "",
                "active": "0"
            },
            "tuesday": {
                "daily_quotes": "",
                "extra_charge": "",
                "active": "0"
            },
            "wednesday": {
                "daily_quotes": "",
                "extra_charge": "",
                "active": "0"
            },
            "thursday": {
                "daily_quotes": "",
                "extra_charge": "",
                "active": "0"
            },
            "friday": {
                "daily_quotes": "",
                "extra_charge": "",
                "active": "0"
            },
            "saturday": {
                "daily_quotes": "",
                "extra_charge": "",
                "active": "0"
            }
        },
        "holidays_serialized": [],
        "shipping_methods_choice_limiter": "0",
        "working_days": "sunday,monday,tuesday,wednesday,thursday,friday,saturday",
        "cut_off_time": "23:00",
        "quotes_scope": "1",
        "store_ids": [
            "0"
        ],
        "customer_group_ids": [],
        "day_limits": [
            {
                "active": true,
                "available": 0,
                "reserved": 0,
                "date_formatted": "Today",
                "status": "available",
                "time_limits": [
                    {
                        "from": "12:00",
                        "to": "13:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "14:00",
                        "to": "21:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "10:00",
                        "to": "11:00",
                        "extra_charge": ""
                    }
                ]
            },
            {
                "active": true,
                "available": 0,
                "reserved": 0,
                "date_formatted": "Tomorrow",
                "status": "available",
                "time_limits": [
                    {
                        "from": "12:00",
                        "to": "13:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "14:00",
                        "to": "21:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "10:00",
                        "to": "11:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "09:00",
                        "to": "10:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "08:00",
                        "to": "09:00",
                        "extra_charge": ""
                    }
                ]
            },
            {
                "active": true,
                "available": 0,
                "reserved": 0,
                "date_formatted": "July 20, 2019",
                "status": "available",
                "time_limits": [
                    {
                        "from": "12:00",
                        "to": "13:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "14:00",
                        "to": "21:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "10:00",
                        "to": "11:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "09:00",
                        "to": "10:00",
                        "extra_charge": ""
                    },
                    {
                        "from": "08:00",
                        "to": "09:00",
                        "extra_charge": ""
                    }
                ]
            }
        ],
        "method": "tablerate_bestway"
    }
]
```

where everything before the `"day_limits": [` row is the configuration of the current delivery option. 
The response after this row is the information for the calculated delivery slots for the certain cart quote. 
The available row shows the limit for the particular date (unlimited in the example above). 
The reserved row means the number of placed orders for the particular date.
If you need to calculate the number of orders, that can be placed for particular date, you should just deduct the reserved value from the available number.

The status means the current status of the delivery date. If you use the advanced flag in your API request, 
you might see the unaavailable dates as well. It might be helpful if you need to display available and unavailable dates in your custom front-end.

The `time_limits` displays the available time periods for each date.

**List all available delivery dates for guest cart**

The extension allows you to retrieve the available & occupied delivery dates & time slots for specific guest cart (require the admin authorization)

**Request format**:

> GET /V1/guest-carts/delivery-date-list/{cartId}/{advanced_flag}

**Example**:

> GET /V1/guest-carts/delivery-date-list/wNyMIj6VDIXSnPRFCNQsvltL6benXZMS/0

**Response JSON example**:

```json
[
    {
        "method": "flatrate_flatrate",
        "entity_id": 1,
        "name": "Default Delivery Configuration",
        "active": true,
        "sort_order": 1,
        "future_days_limit": 5,
        "start_days_limit": 0,
        "shipping_methods_choice_limiter": 0,
        "working_days": "monday,tuesday,wednesday,thursday,friday",
        "quotes_scope": 2,
        "store_ids": [
            0
        ],
        "customer_group_ids": [],
        "day_limits": [
            {
                "day_index": 1,
                "date_formatted": "2022-05-24",
                "date": "2022-05-24",
                "time_limits": [],
                "extra_charge": 0,
                "extra_charge_message": ""
            },
            {
                "day_index": 2,
                "date_formatted": "2022-05-25",
                "date": "2022-05-25",
                "time_limits": [
                    {
                        "from": "10:00",
                        "to": "12:00",
                        "extra_charge": "US$2.00"
                    },
                    {
                        "from": "14:00",
                        "to": "16:00",
                        "extra_charge": "US$5.00"
                    },
                    {
                        "from": "17:05",
                        "to": "23:30",
                        "extra_charge": ""
                    }
                ],
                "extra_charge": 0,
                "extra_charge_message": ""
            },
            {
                "day_index": 3,
                "date_formatted": "2022-05-26",
                "date": "2022-05-26",
                "time_limits": [],
                "extra_charge": 0,
                "extra_charge_message": ""
            },
            {
                "day_index": 4,
                "date_formatted": "2022-05-27",
                "date": "2022-05-27",
                "time_limits": [],
                "extra_charge": 0,
                "extra_charge_message": ""
            }
        ]
    }
]
```

**Get currently selected delivery date for guest cart**

The extension allows you to retrieve the selected delivery date & time for specific guest cart (require the admin authorization)

**Request format**:

> GET /V1/guest-carts/{cartId}/delivery-date

**Example**:

> GET /V1/guest-carts/wNyMIj6VDIXSnPRFCNQsvltL6benXZMS/delivery-date

**Response JSON example**:

```json
{
    "entity_id": 7,
    "quote_address_id": 68,
    "order_address_id": 35,
    "shipping_method": "flatrate_flatrate",
    "carrier": "flatrate",
    "store_id": 1,
    "delivery_day": "2022-05-10",
    "delivery_hours_from": 17,
    "delivery_minutes_from": 0,
    "delivery_hours_to": 23,
    "delivery_minutes_to": 0,
    "delivery_comment": "",
    "delivery_time": "17:00_23:00",
    "delivery_option": 1
}
```

**Get currently selected delivery date for registered customer cart**

Allows you to retrieve the selected delivery date & time for specific cart (require the admin authorization)

**Request format**:

> GET /V1/carts/{cartId}/delivery-date

**Example**:

> GET /V1/carts/26/delivery-date

**Response JSON example**:

```json
{
    "delivery_day": "2022-05-03",
    "delivery_hours_from": 17,
    "delivery_minutes_from": 0,
    "delivery_hours_to": 23,
    "delivery_minutes_to": 0,
    "delivery_comment": "",
    "delivery_time": "17:00_23:00",
    "delivery_option": 1
}
```

**Get currently selected delivery date of mine cart**

Allows you to retrieve the selected delivery date & time for the current customer cart (require the customer authorization)

**Request format**:

> GET /V1/carts/mine/delivery-date

**Example**:

> GET /V1/carts/mine/delivery-date

**Response JSON example**:

```json
{
    "delivery_day": "2022-05-11",
    "delivery_hours_from": 14,
    "delivery_minutes_from": 0,
    "delivery_hours_to": 16,
    "delivery_minutes_to": 0,
    "delivery_comment": "",
    "delivery_time": "14:00_16:00",
    "delivery_option": 1
}
```

**Set delivery date for guest customer cart**

Allows you to set the delivery date, time, comment for specific guest customer cart (require the admin authorization)

**Request format**:

> POST /V1/guest-carts/{cartId}/delivery-date

Request Body [JSON]:

- `deliveryDateData` object with next parameters
    - `delivery_day` (**String**) [_Required_] - date in Y-m-d format
    - `delivery_hours_from` (**Int**) [_Optional_] Hours part of the "FROM" time diapason. Must be valid per delivery option configuration.
    - `delivery_minutes_from` (**Int**) [_Optional_] Minutes part of the "FROM" time diapason. Must be valid per delivery option configuration.
    - `delivery_hours_to` (**Int**) [_Optional_] Hours part of the "TO" time diapason. Must be valid per delivery option configuration.
    - `delivery_minutes_to` (**Int**) [_Optional_] Minutes part of the "TO" time diapason. Must be valid per delivery option configuration.
    - `delivery_comment` (**String**) [_Optional_] Comment for delivery

**Example**:

> POST /V1/guest-carts/DIdCJMTvqpCcDbAGknow0KzBmp5ZmoRj/delivery-date

**Request JSON body**:

```json
{
    "deliveryDateData": {
        "delivery_day": "2022-05-25",
        "delivery_hours_from": 17,
        "delivery_minutes_from": 5,
        "delivery_hours_to": 23,
        "delivery_minutes_to": 30,
        "delivery_comment": "Call me an hour before the courier arrives"
    }
}
```

**Response JSON**:

```json
{
    "delivery_day": "2022-05-25",
    "delivery_hours_from": 17,
    "delivery_minutes_from": 5,
    "delivery_hours_to": 23,
    "delivery_minutes_to": 30,
    "delivery_comment": "Call me an hour before the courier arrives",
    "delivery_time": "17:05_23:30",
    "delivery_option": 1
}
```

If you select an unavailable delivery time, an error will be returned.

**Example of error response**

```json
{
  "message": "Compatible time limit not found",
  "trace": "Trace here"
}
```

**Set delivery date for registered customer cart**

Allows you to set the delivery date, time, comment for specific registered customer cart (require the admin authorization)

**Request format**:

> POST /V1/carts/{cartId}/delivery-date

Request Body [JSON]:

- `deliveryDateData` object with next parameters
    - `delivery_day` (**String**) [_Required_] - date in Y-m-d format
    - `delivery_hours_from` (**Int**) [_Optional_] Hours part of the "FROM" time diapason. Must be valid per delivery option configuration.
    - `delivery_minutes_from` (**Int**) [_Optional_] Minutes part of the "FROM" time diapason. Must be valid per delivery option configuration.
    - `delivery_hours_to` (**Int**) [_Optional_] Hours part of the "TO" time diapason. Must be valid per delivery option configuration.
    - `delivery_minutes_to` (**Int**) [_Optional_] Minutes part of the "TO" time diapason. Must be valid per delivery option configuration.
    - `delivery_comment` (**String**) [_Optional_] Comment for delivery

**Example**:

> POST /V1/carts/26/delivery-date

**Request JSON body**:

```json
{
    "deliveryDateData": {
        "delivery_day": "2022-05-18",
        "delivery_hours_from": 17,
        "delivery_minutes_from": 5,
        "delivery_hours_to": 23,
        "delivery_minutes_to": 30,
        "delivery_comment": "Call me an hour before the courier arrives"
    }
}
```

**Response JSON**:

```json
{
    "delivery_day": "2022-05-18",
    "delivery_hours_from": 17,
    "delivery_minutes_from": 5,
    "delivery_hours_to": 23,
    "delivery_minutes_to": 30,
    "delivery_comment": "Call me an hour before the courier arrives",
    "delivery_time": "17:05_23:30",
    "delivery_option": 1
}
```

**Set delivery date for mine cart**

Allows you to set the delivery date, time, comment for current customer cart (require the customer authorization)

**Request format**:

> POST /V1/carts/mine/delivery-date

Request Body [JSON]:

- `deliveryDateData` object with next parameters
    - `delivery_day` (**String**) [_Required_] - date in Y-m-d format
    - `delivery_hours_from` (**Int**) [_Optional_] Hours part of the "FROM" time diapason. Must be valid per delivery option configuration.
    - `delivery_minutes_from` (**Int**) [_Optional_] Minutes part of the "FROM" time diapason. Must be valid per delivery option configuration.
    - `delivery_hours_to` (**Int**) [_Optional_] Hours part of the "TO" time diapason. Must be valid per delivery option configuration.
    - `delivery_minutes_to` (**Int**) [_Optional_] Minutes part of the "TO" time diapason. Must be valid per delivery option configuration.
    - `delivery_comment` (**String**) [_Optional_] Comment for delivery

**Example**:

> POST /V1/carts/mine/delivery-date

**Request JSON body**:

```json
{
    "deliveryDateData": {
        "delivery_day": "2022-05-25",
        "delivery_hours_from": 17,
        "delivery_minutes_from": 5,
        "delivery_hours_to": 23,
        "delivery_minutes_to": 30,
        "delivery_comment": "Call me an hour before the courier arrives"
    }
}
```

**Response JSON**:

```json
{
    "delivery_day": "2022-05-25",
    "delivery_hours_from": 17,
    "delivery_minutes_from": 5,
    "delivery_hours_to": 23,
    "delivery_minutes_to": 30,
    "delivery_comment": "Call me an hour before the courier arrives",
    "delivery_time": "17:05_23:30",
    "delivery_option": 1
}
```
