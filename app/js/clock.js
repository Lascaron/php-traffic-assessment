var input = $('#searchTime');

$('#clockImg').click(function(e){
    input.clockpicker({
        autoclose: true,
        afterHide: function() {
            input.clockpicker('remove')
        }
    });
    e.stopPropagation();
    input.clockpicker('show')
});