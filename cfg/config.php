<?php

    /*Set Server Connect*/
    define('DB_SERVER', 'localhost:3306');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'staffing');

    /*Email Settings*/
    define('EMAIL_TO', 'appointments@toscors2.com');
    define('EMAIL_FROM_ADDR', 'www.toscors2.com');
    define('MESSAGE_SUBJECT', 'New Message From Contact Form');
    define('SEND_EMAIL', false); /* if set to true, an email is sent to admin after a message is inserted */

?>