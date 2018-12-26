/***
 * Set the max and disable the button.
 * Submit the form.
 */
function submitForm() {

    var searchDate = document.getElementById('searchDate');
    var searchTime = document.getElementById('searchTime');

    // Date is given but time not.
    if (searchDate.value != '' && searchTime.value == '') {

        searchTime.style = 'background-color: red;';
        searchTime.focus();
    // Time is given but date not.
    } else if (searchDate.value == '' && searchTime.value != '') {

        searchDate.style = 'background-color: red;';
        searchDate.focus();
    } else {

        // Both date and time and given or empty. When given, check the formats.
        if ((searchDate.value == '' && searchTime.value == '') ||
            checkSearchfields(searchDate, searchTime)) {

            if (document.getElementById('checklimit').checked) {
    
                document.getElementById('limit').value = '10';
            } else {

                document.getElementById('limit').value = '1000';
            }

            document.getElementById('searchDate').focus();
            document.getElementById('search').disabled = true;
            document.getElementById('erase').disabled = true;
            document.getElementById('form').submit();
        }
    }
}

/***
* Erase the searchfields.
***/
function eraseSearchFields() {


    document.getElementById('searchDate').value = '';
    document.getElementById('searchDate').style = 'background-color: white;';
    document.getElementById('searchTime').value = '';
    document.getElementById('searchTime').style = 'background-color: white;';
    document.getElementById('searchDate').focus();
}

/***
 * Check if entered searchfields are valid.
 * Date must be format dd-mm-yyyy.
 * Time must be format hh:mm.
 */
function checkSearchfields(date, time) {

    var regDate = /(0[1-9]|[12][0-9]|3[01])[-](0[1-9]|1[012])[-](19|20)\d\d/;
    var regTime = /([01][0-9]|[2][0-3])[:]([0-5][0-9])/;

    var returnValue = true;

    if (!date.value.match(regDate)) {

        date.style = 'background-color: orange;';
        date.focus();
        returnValue = false;
    }

    if (!time.value.match(regTime)) {

        time.style = 'background-color: orange;';
        if (returnValue) {
            
            time.focus();
            returnValue = false;
        }
    }

    return returnValue;
}

/***
 * Search.
 */
function goSearch() {

    document.getElementById('offset').value = '0';
    submitForm();
}


/**
 * Next page.
 */
function nextPage() {

    document.getElementById('offset').value = parseInt(document.getElementById('offset').value) + parseInt(document.getElementById('limit').value);
    submitForm();
}

/**
 * Previous page.
 */
function previousPage() {

    document.getElementById('offset').value = parseInt(document.getElementById('offset').value) - parseInt(document.getElementById('limit').value);
    submitForm();
}
