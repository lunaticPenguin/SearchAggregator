$(document).ready(function () {

    var objField = $('INPUT#q_field');

    objField.autocomplete({
        delay: 400,
        source: function (request, response) {
            var hashParameters = {'q':objField.val()};
            $.ajax({
                    url: '/home/suggest',
                    cache: false,
                    data: hashParameters,
                    dataType: 'json',
                    success: function (hashData) {
                        response($.map(hashData, function (object) {
                            return object;
                        }));
                    }
                }
            );
        },
        select: function (index, ui) {
            doSearch();
        }
    });

    $('#searchForm').submit(function (e) {
        e.preventDefault();
        doSearch();
        return false;
    });

    function doSearch() {
        var hashParameters = {'q':objField.val()};
        var strActiveTabName = $('#available-engines LI.active').data('type');
        var objPanel = $('DIV:[data-type="'+strActiveTabName+'"]');
        console.log(objPanel.length);
        objPanel.html('');
        objPanel.progressbar({
            max: 50,
            value: false
        });

        $.ajax({
            url: '/home/index',
            cache: false,
            data: hashParameters,
            dataType: 'json',
            success: function (hashData) {
                objPanel.progressbar("destroy");
                console.log(hashData);
            }
        });
    }
});
