#!/bin/bash
(crontab -l | grep -v "/usr/bin/php /opt/lampp/htdocs/Backend-6amMart/artisan dm:disbursement") | crontab -
(crontab -l ; echo "44 18 * * * /usr/bin/php /opt/lampp/htdocs/Backend-6amMart/artisan dm:disbursement") | crontab -
(crontab -l | grep -v "/usr/bin/php /opt/lampp/htdocs/Backend-6amMart/artisan store:disbursement") | crontab -
(crontab -l ; echo "43 18 * * * /usr/bin/php /opt/lampp/htdocs/Backend-6amMart/artisan store:disbursement") | crontab -
