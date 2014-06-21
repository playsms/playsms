UPGRADE
=======

Upgrade to playSMS version 1.0.0 from unmodified playSMS version 0.9.9.2

Assumed that your old playSMS (0.9.9.2) was installed like this:

* Your playSMS 0.9.9.2 web location is in /var/www/playsms/
* Your playSMS 0.9.9.2 daemon files location are in /usr/local/bin/
* Your playSMS 0.9.9.2 database name is 'playsms'

And, assumed that your new playSMS (1.0.0) is already extracted at:

* /usr/local/src/playsms-1.0.0

Please note that you may have different system configuration than assumed above.

Follow below steps carefully to upgrade from unmodified 0.9.9.2 to 1.0.0:

1. Stop playsmsd and sendsmsd:

   ```
   /etc/init.d/playsms stop
   ```

2. Move and backup old files and database (please note on the 'slash'):

   ```
   mkdir -p /root/backup-0.9.9.2
   cd /root/backup-0.9.9.2
   mkdir www bin etc db
   mv /var/www/playsms/* www/
   mv /usr/local/bin/playsms* /usr/local/bin/sendsms* bin/
   mysqldump -uroot -p playsms --add-drop-table > db/playsms.sql
   mv /etc/default/playsms etc/playsms
   mv /etc/init.d/playsms etc/playsms.init
   ```

3. Do playSMS version 1.0.0 installation as if installing for the first time,
   on the same server, same paths, same database name.

   Please follow the guide on INSTALL.md

4. Compare these files:

   Version 0.9.9.2 files               | Version 1.0.0 files
   ----------------------------------- | -------------------
   /root/backup-0.9.9.2/www/config.php | /var/www/playsms/config.php
   /root/backup-0.9.9.2/etc/playsms    | /etc/playsmsd.conf

   Once compared, make adjustment to the new config files.

5. Re-insert old database sql file on backup

   ```
   cd /root/backup-0.9.9.2
   mysql -uroot -p playsms < db/playsms.sql
   ```

6. Insert upgrade sql file

   ```
   cd /usr/local/src/playsms-1.0.0
   mysql -uroot -p playsms < db/playsms-upgrade-0992-to-100.sql
   ```

7. Start again playsmsd and sendsmsd:

   ```
   /etc/init.d/playsms start
   ```

8. Upgrade complete, browse playSMS as usual.
