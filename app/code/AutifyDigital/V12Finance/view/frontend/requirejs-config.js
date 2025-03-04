/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

var config = {
    paths: {
        "slider-rtl": "AutifyDigital_V12Finance/plugins/slider/slider-rtl",
        "jquery-ui-slider": "AutifyDigital_V12Finance/plugins/jquery-ui-slider"
    },
    shim: {
        "slider-rtl": {
            deps: ["jquery", "jquery/ui"]
        },
        "jquery-ui-slider": {
            deps: ["jquery", "jquery/ui", "slider-rtl"]
        },
    }
}
