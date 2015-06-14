$(document).ready(function () {

    var funcExpiring;
    var objField = $('INPUT#q_field');

    function handleSearchChanges(objElement)
    {
        clearTimeout(funcExpiring);
        funcExpiring = setTimeout(function () {
                var hashParameters = {};
                hashParameters['q'] = objField.val();
                $.ajax({
                    url: '/home/suggest',
                    cache: false,
                    data: hashParameters,
                    dataType: 'json',
                    success: function (hashData) {
                        var hashContent = [];
                        for (strKey in hashData) {
                            if (hashData.hasOwnProperty(strKey)) {
                                hashContent.push(strKey);
                                hashContent = hashContent.concat(hashData[strKey]);
                            }
                        }

                        console.log(hashContent);

                        objField.autocomplete({
                            source: hashContent
                        });
                    },
                    error: function(e, xhr){
                        console.log(e, xhr);
                    }
                });
                clearTimeout(funcExpiring);
            },
            400
        );
    }
    objField.unbind('keyup').on('keyup', handleSearchChanges);

    //objField.autocomplete({
    //    delay: 400,
    //    source: '/home/suggest'
    //});
});
