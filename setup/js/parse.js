$('document').ready(function () {

    $('#test').text('Are You Kidding Me');

    console.log("WTF");

    function getFilesToParse() {

        console.log("getting files to parse");

        $.post('getFilesToParse.php', null, function (data) {

            console.log("parsed file");
            console.log(data.toParse.length);

            $('#test').html(data.html);

            if (data.toParse.length > 0) {
                sendFilesToParse(data.toParse)
            } else {
                console.log("All Parsed");
            }

        }, 'json')

    }

    function sendFilesToParse(toParse) {

        for (id of toParse) {

            fileName = $('#fileName' + id).text();

            $('#status' + id).text('***[parsing...]***');

            sendData = {'fileName': fileName, 'id': id};
            console.log(sendData);
            $.post('parseFile.php', sendData, function(data){

                console.log(data);

                if (data.id !== undefined && data.id > 0) {
                    status = '***[parsed file in ' + data.totalTime + ' seconds]***';
                } else {
                    status = '***[possible error parsing...]***';
                }

                $('#status' + data.id).text(status);
                $('#times' + data.id).html(data.functionTimes);

            }, 'json')


        }

    }

    getFilesToParse();


});