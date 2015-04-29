# INSTALL

This document explains about how to install and setup playSMS version **1.0**


## Requirements

Most of on the requirements on this list must be fulfilled. Please read this
part before starting the installation.

**Minimum required hardware:**

* Web server capable hardware

Optional hardware or infrastructure:

* GSM modem, single/modem pool (only when you plan to use Kannel, Gammu, Gnokii or smstools gateway plugins)
* Internet connection (only when you plan to use Clickatell, Nexmo, Twilio, Infobip gateway plugins)
* LAN (only when you plan to link 2 playSMS on different server in the same network using Uplink gateway plugin)

**Minimum required softwares:**

* Operating System Linux (distro such as Ubuntu, Debian, CentOS etc)
* Web server software (for example Apache2, nginx or lighttpd)
* Database Server MySQL 5.x.x or latest stable release
* PHP 5.3 or latest stable release (must be at least version 5.3)
* PHP MySQL module must be installed and enabled
* PHP CLI must be installed
* PHP PEAR and PHP PEAR-DB must be installed correctly
* PHP gettext extension must be installed and enabled for text translation
* PHP mbstring extension must be installed and enabled for unicode detection
* PHP GD extension must be installed and enabled to draw graphs
* Access to SMTP server to send Email
* At least one console browser such as lynx, wget or curl should be installed
* Downloaded playSMS package from SF.net or latest source code from Github
* Properly installed composer from https://getcomposer.org

**Minimum required server administrator (or developer):**

* Understand howto make sure required softwares are installed
* Understand howto make sure installed PHP has MySQL module enabled/loaded
* Understand howto create/drop MySQL database
* Understand howto insert SQL statements into created database
* Basic knowledges to manage Linux (skill to navigate in console mode)


## Installation

There are 2 methods explained in this document to install playSMS:

1. Installation on Linux using install-script
2. Installation on Linux step by step

You should pick **only one** method, do not do both methods.


### Method 1: Installation on Linux using install-script

Install playSMS using install script `install-playsms.sh`

1.  Extract playSMS package and go there (For example in /usr/local/src)

    ```
    tar -zxf playsms-1.0.0.tar.gz -C /usr/local/src
    ls -l /usr/local/src/
    cd /usr/local/src/playsms-1.0.0/
    ```

2.  Copy install.conf.dist to install.conf and edit install.conf

    Read install.conf and make changes to suit your system configuration

    ```
    cp install.conf.dist install.conf
    vi install.conf
    ```

3.  Run installer script

    ```
    ./install-playsms.sh
    ```

4.  Configure rc.local to get playsmsd started on boot

    Look for rc.local on /etc, /etc/init.d, /etc/rc.d/init.d

    When you found it edit that rc.local and put:

    `/usr/local/bin/playsmsd start`

    on the bottom of the file (before exit if theres an exit command).

    This way playsmsd will start automatically on boot.

Note:

* After successful installation, please run command `ps ax` and see if playsmsd is running

  ```
  ps ax | grep playsms
  4069 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd schedule
  4071 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd dlrssmsd
  4073 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd recvsmsd
  4075 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd sendsmsd
  ```

* Run several checks

  ```
  playsmsd status
  playsmsd check
  ```

* Stop here and review your installation steps when playsmsd is not running
* Consider to ask question in playSMS forum when you encountered a problem
* If all seems to be correctly installed you may try to login from web browser

  ```
  URL                    : http://[your web server IP]/playsms/
  Default admin username : admin
  Default admin password : admin
  ```


### Method 2: Installation on Linux step by step

Install playSMS by following step-by-step:

1.  Extract playSMS package and go there (For example in /usr/local/src)

    ```
    tar -zxf playsms-1.0.0.tar.gz -C /usr/local/src
    ls -l /usr/local/src/
    cd /usr/local/src/playsms-1.0.0/
    ```

2.  Run getcomposer.sh

    ```
    ./getcomposer.sh
    ```

    You may see the following warning, that can safely be ignored:
    ```
    Warning: Ambiguous class resolution, "PEAR_ErrorStack" was found in both "/usr/local/src/playsms-1.0.0/web/lib/composer/vendor/pear/pear/PEAR/ErrorStack.php" and "/usr/local/src/playsms-1.0.0/web/lib/composer/vendor/pear/pear/PEAR/ErrorStack5.php", the first will be used.
    ```

3.  Create playSMS web root, log, lib and set ownership to user www-data or web server user

    Assumed that your web root is `/var/www` and your web server user is `www-data`

    ```
    mkdir -p /var/www/playsms /var/log/playsms /var/lib/playsms
    chown -R www-data /var/www/playsms /var/log/playsms /var/lib/playsms
    ```

    Please note that there are Linux distributions using `apache` as web server user instead of `www-data`

    Also note that there are Linux distributions set `/var/www/html` as web root instead of `/var/www`

4.  Copy files and directories inside `web` directory to playSMS web root and set ownership to web server user

    ```
    cp -R web/* /var/www/playsms
    chown -R www-data /var/www/playsms
    ```

5.  Setup database (import database)

    ```
    mysqladmin -u root -p create playsms
    cat db/playsms.sql | mysql -u root -p playsms
    ```

6.  Copy config-dist.php to config.php and then edit config.php

    ```
    cp /var/www/playsms/config-dist.php /var/www/playsms/config.php
    vi /var/www/playsms/config.php
    ```

    Please read and fill all fields with correct values

7.  Enter daemon/linux directory, copy files and folder inside

    ```
    cp daemon/linux/etc/playsmsd.conf /etc/playsmsd.conf
    cp daemon/linux/bin/playsmsd /usr/local/bin/playsmsd
    ```

8.  Just to make sure every paths are correct, please edit /etc/playsmsd.conf

    ```
    vi /etc/playsmsd.conf
    ```

    Make sure that `PLAYSMS_PATH` is pointing to a correct playSMS installation path (in this example to /var/www/playsms)

    Also Make sure that `PLAYSMS_BIN` is pointing to a correct playSMS daemon scripts path (in this example to /usr/local/bin)

9.  Start playsmsd now from Linux console, no need to reboot

    ```
    playsmsd start
    ```

10. Configure rc.local to get playsmsd started on boot

    Look for rc.local on /etc, /etc/init.d, /etc/rc.d/init.d

    When you found it edit that rc.local and put:

    `/usr/local/bin/playsmsd start`

    on the bottom of the file (before exit if theres an exit command).

    This way playsmsd will start automatically on boot.

Note:

* After successful installation, please run command `ps ax` and see if playsmsd is running

  ```
  ps ax | grep playsms
  4069 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd schedule
  4071 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd dlrssmsd
  4073 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd recvsmsd
  4075 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd sendsmsd
  ```

* Run several checks

  ```
  playsmsd status
  playsmsd check
  ```

* Stop here and review your installation steps when playsmsd is not running
* Consider to ask question in playSMS forum when you encountered a problem
* If all seems to be correctly installed you may try to login from web browser

  ```
  URL                    : http://[your web server IP]/playsms/
  Default admin username : admin
  Default admin password : admin
  ```


## Gateway Installation

Next, choose a gateway.

If you have GSM modem and plan to use it with playSMS, please continue to follow
instructions in `INSTALL_SMSSERVERTOOLS` to use SMS Server Tools (smstools3) as
your gateway module, or follow `INSTALL_KANNEL` if you want to use Kannel.

Gnokii and Gammu also supported, please follow `INSTALL_GNOKII` if you want to use
Gnokii as your gateway module, or `INSTALL_GAMMU` if you want to use Gammu.
