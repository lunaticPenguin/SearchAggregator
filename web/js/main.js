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
                        response($.map(hashData, function (arrayData, strSEType) {
                            return $.map(arrayData, function (strSuggestion) {
                                return strSEType + ' - ' + strSuggestion;
                            });
                        }));
                    }
                }
            );
        },
        select: function (index, ui) {
            // suppression du nom du moteur
            objField.val(ui.item['value'].replace('Google - ', '').replace('Bing - ', '').replace('Yahoo! - ', ''));
            //doSearch();
            return false;
        }
    });
});
