#!/bin/bash
# Customer zero download report
# (C) 2024 - Mansur Ali<mansuralih@gmail.com, mandumah.com

#EMAILS="mansuralih@gmail.com,salah@mandumah.com,mehidy@mandumah.com,tharwet@mandumah.com,ali@alshowaish.com,faisal@mandumah.com,mstayyar@mandumah.com" #list of email addresses to receive alerts (comma separated)
EMAILS="mansuralih@gmail.com,salah@mandumah.com,mehidy@mandumah.com,tharwet@mandumah.com,mstayyar@mandumah.com,mstayyar@gmail.com,ali@alshowaish.com,faisal@mandumah.com,techmandumah@gmail.com,omr@mandumah.com"
#EMAILS="mansuralih@gmail.com" #list of email addresses to receive alerts (comma separated)
FROM="stats@mandumah.com" 
FILE=/tmp/zero-download-report.html
SUBJECT="Zero Download Report"
REPORTDATE=`date -d "-1 days" +"%Y-%m-%d"`

/usr/bin/php /root/mansur/zero_download_report.php $REPORTDATE > $FILE

if [ -s "$FILE" ]
then
  for EMAIL in $(echo $EMAILS | tr "," " "); do
    mail -Ssendmail=/usr/bin/sendmail-mail-html-hook -s "$(echo -e "$SUBJECT ($REPORTDATE)")" -r "$FROM" "$EMAIL" < $FILE
  done
fi

