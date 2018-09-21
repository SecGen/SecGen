$(document).ready(function() {
    //var activeSystemClass = $('.list-group-item.active');
    // affect all table rows on in systems table
    var tableBody = $('.table-list-search tbody');
    var tableRowsClass = $('.table-list-search tbody tr');

    //something is entered in search form
    $('#system-search').keyup( function() {
       var that = this;
        $('.search-sf').remove();
        tableRowsClass.each( function(i, val) {
            //Lower text for case insensitive
            var rowText = $(val).text().toLowerCase();
            var inputText = $(that).val().toLowerCase();
            if(inputText != '')
            {
                $('.search-query-sf').remove();
                $('.alluinfo').html('<strong class="text-success">Searching for: "'+ $(that).val() + '"</strong>');
            }
            else
            {
                $('.search-query-sf').remove();
                $('.alluinfo').html('');
            }

            if( rowText.indexOf( inputText ) == -1 )
            {
                //hide rows
                tableRowsClass.eq(i).hide();

            }
            else
            {
                $('.search-sf').remove();
                tableRowsClass.eq(i).show();
            }
        });
        //all tr elements are hidden
        if(tableRowsClass.children(':visible').length == 0)
        {
            $('.alluinfo').append(' :: <span class="text-danger">No entries found.</span>');
        }
    });

    $('.reset').click(function() {
        $('.search-query-sf').remove();
        $('.alluinfo').html('');
        $('#system-search').val('');
        tableRowsClass.each( function(i, val) {
            tableRowsClass.eq(i).show();
        });
    });
});
