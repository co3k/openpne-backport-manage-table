/**
 * Copyright (c) 2011 Kousuke Ebihara
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
*/

$(document).ready(function() { 
    $("#issues_table").tablesorter();
});

$("#filter_button").click(function() {
    $("#issues_table tbody tr").hide();

    var conditions = {
        "tracker" : $("#filter_tracker").val(),
        "status" : $("#filter_status").val(),
        "priority" : $("#filter_priority").val(),
        "36" : $("#filter_36").val(),
        "34" : $("#filter_34").val(),
        "30" : $("#filter_30").val(),
    };

    $("#issues_table tbody tr").filter(function (index) {
        if (conditions["tracker"] && -1 == jQuery.inArray($("td:nth-child(2)", this).text(), conditions["tracker"])) {
            console.debug(index+" : tracker");
            return false;
        }

        if (conditions["status"] && -1 == jQuery.inArray($("td:nth-child(3)", this).text(), conditions["status"])) {
            console.debug(index+" : status");
            return false;
        }

        if (conditions["priority"] && -1 == jQuery.inArray($("td:nth-child(4)", this).text(), conditions["priority"])) {
            console.debug(index+" : priority");
            return false;
        }

        if (conditions["36"] && -1 == jQuery.inArray($("td:nth-child(7)", this).text(), conditions["36"])) {
            console.debug(index+" : 36");
            return false;
        }

        if (conditions["34"] && -1 == jQuery.inArray($("td:nth-child(8)", this).text(), conditions["34"])) {
            console.debug(index+" : 34");
            return false;
        }

        if (conditions["30"] && -1 == jQuery.inArray($("td:nth-child(9)", this).text(), conditions["30"])) {
            console.debug(index+" : 30");
            return false;
        }

            console.debug(index+" : complete");
        return true;
    }).show();
});
