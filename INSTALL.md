# INSTALL

This document explains about how to install and setup playSMS version **1.4.2**


## Requirements

Most of on the requirements on this list must be fulfilled. Please read this
part before starting the installation.

**Minimum required hardware:**

* Web server capable hardware

Optional hardware or infrastructure:

* GSM modem, single/modem pool
* Internet connection
* LAN

**Minimum required softwares:**

* Operating System Linux (distro such as Ubuntu, Debian, CentOS etc)
* Web server software (for example Apache2, nginx or lighttpd)
* Database Server MySQL 5.x.x or latest stable release (with adjustments for MySQL 5.7.x)
* PHP 5.3 or latest stable release (must be at least version 5.3)
* PHP MySQL module must be installed and enabled
* PHP CLI must be installed
* PHP gettext extension must be installed and enabled for text translation
* PHP mbstring extension must be installed and enabled for unicode detection
* PHP GD extension must be installed and enabled to draw graphs
* Access to SMTP server to send Email
* Downloaded playSMS official release package from SF.net or master version from Github
* Properly installed composer from https://getcomposer.org (will be installed by playSMS install script)

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

Again, please do **only one** method, do not do both methods, unless of course you know what you're doing.


### Method 1: Installation on Linux using install-script

Install playSMS using install script `install-playsms.sh`

1.  Extract playSMS package and go there (For example in `/usr/local/src`)

    ```bash
    tar -zxf playsms-1.4.2.tar.gz -C /usr/local/src
    ls -l /usr/local/src/
    cd /usr/local/src/playsms-1.4.2/
    ```

2.  Copy `install.conf.dist` to `install.conf` and edit `install.conf`

    Read `install.conf` and make changes to suit your system configuration

    ```bash
    cp install.conf.dist install.conf
    vi install.conf
    ```

3.  Run playSMS install script

    ```bash
    ./install-playsms.sh
    ```

4.  Configure `rc.local` to get `playsmsd` started on boot

    Look for `rc.local` on `/etc`, `/etc/init.d` or `/etc/rc.d/init.d`

    When you found it edit that `rc.local` and put:

    `/usr/local/bin/playsmsd start`

    on the bottom of the file (before exit if theres an exit command).

    This way `playsmsd` will start automatically on boot.

Note:

* After successful installation, please run command `ps ax` and see if `playsmsd` is running

  ```bash
  ps ax | grep playsms
  4069 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd /etc/playsmsd.conf schedule
  4071 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd /etc/playsmsd.conf dlrssmsd
  4073 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd /etc/playsmsd.conf recvsmsd
  4075 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd /etc/playsmsd.conf sendsmsd
  ```

* Run several checks

  ```bash
  playsmsd status
  playsmsd check
  ```

* Stop here and review your installation steps when `playsmsd` is not running
* Consider to ask question in playSMS forum when you encountered any problem
* If all seems to be correctly installed you may try to login from web browser

  ```
  URL                    : http://[your web server IP]/playsms/
  Default admin username : admin
  Default admin password : admin
  ```


### Method 2: Installation on Linux step by step

Install playSMS by following step-by-step:

1.  Extract playSMS package and go there (For example in `/usr/local/src`)

    ```bash
    tar -zxf playsms-1.4.2.tar.gz -C /usr/local/src
    ls -l /usr/local/src/
    cd /usr/local/src/playsms-1.4.2/
    ```

2.  Run `getcomposer.sh`

    ```bash
    ./getcomposer.sh
    ```

3.  Create playSMS web root, log, lib and set ownership to user **www-data** or web server user

    Assumed that your web root is `/var/www/html` and your web server user is **www-data**

    ```bash
    mkdir -p /var/www/html/playsms /var/log/playsms /var/lib/playsms
    chown -R www-data /var/www/html/playsms /var/log/playsms /var/lib/playsms
    ```

    Please note that there are Linux distributions using **apache** as web server user instead of **www-data**

    And also note that there are Linux distributions having `/var/www` as default web root instead of `/var/www/html`

4.  Copy files and directories inside `web` directory to playSMS web root and set ownership to web server user

    ```bash
    cp -R web/* /var/www/html/playsms
    chown -R www-data /var/www/html/playsms
    ```

5.  Setup database (import database)

    ```bash
    mysqladmin -u root -p create playsms
    cat db/playsms.sql | mysql -u root -p playsms
    ```

6.  Copy `config-dist.php` to `config.php` and then edit `config.php`

    ```bash
    cp /var/www/html/playsms/config-dist.php /var/www/html/playsms/config.php
    vi /var/www/html/playsms/config.php
    ```

    Please read and fill all fields with correct values

7.  Enter daemon/linux directory, copy files and folder, and set correct permission

    ```bash
    cp daemon/linux/etc/playsmsd.conf /etc/playsmsd.conf
    cp daemon/linux/bin/playsmsd.php /usr/local/bin/playsmsd
    chmod +x /usr/local/bin/playsmsd
    ```

8.  Just to make sure every paths are correct, please edit `/etc/playsmsd.conf`

    ```bash
    vi /etc/playsmsd.conf
    ```

    Make sure that `PLAYSMS_PATH` is pointing to a correct playSMS installation path (in this example to `/var/www/html/playsms`)

    Also Make sure that `PLAYSMS_BIN` is pointing to a correct playSMS daemon scripts path (in this example to `/usr/local/bin`)

9.  Start `playsmsd` now from Linux console, no need to reboot

    ```bash
    playsmsd start
    ```

10. Configure `rc.local` to get `playsmsd` started on boot

    Look for `rc.local` in `/etc`, `/etc/init.d` or `/etc/rc.d/init.d`

    When you found it edit that `rc.local` and put:

    `/usr/local/bin/playsmsd start`

    on the bottom of the file (before exit if theres an exit command).

    This way `playsmsd` will start automatically on boot.

Note:

* After successful installation, please run command `ps ax` and see if `playsmsd` is running

  ```bash
  ps ax | grep playsms
  4069 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd /etc/playsmsd.conf schedule
  4071 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd /etc/playsmsd.conf dlrssmsd
  4073 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd /etc/playsmsd.conf recvsmsd
  4075 pts/12  S    0:00 /usr/bin/php -q /usr/local/bin/playsmsd /etc/playsmsd.conf sendsmsd
  ```

* Run several checks

  ```bash
  playsmsd status
  playsmsd check
  ```

* Stop here and review your installation steps when `playsmsd` is not running
* Consider to ask question in playSMS forum when you encountered any problem
* If all seems to be correctly installed you may try to login from web browser

  ```
  URL                    : http://[your web server IP]/playsms/
  Default admin username : admin
  Default admin password : admin
  ```


## Gateway Installation

Read more information and tutorial in [playSMS documentation](https://help.playsms.org/en).
