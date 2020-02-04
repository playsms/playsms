# INSTALL

This document explains about how to install and setup playSMS version **1.4.3**


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
* Database Server MySQL 5.x.x or latest stable release
* PHP 5.3 or latest stable release
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

1.  Extract playSMS package in playSMS source directory

    In this example your Linux user is `komodo` and your home directory is `/home/komodo`. Your playSMS source directory will be `/home/komodo/src/playSMS-1.4.3`.
    
    In real installation you need to use your own Linux user and home directory or wherever you want to and have access to install playSMS.
    
    ```bash
    mkdir -p /home/komodo/src
    tar -zxf playsms-1.4.3.tar.gz -C /home/komodo/src
    ls -l /home/komodo/src/
    ```

2.  Copy `install.conf.dist` to `install.conf` and edit `install.conf`

    Read `install.conf` and make changes to suit your system configuration

    ```bash
    cd /home/komodo/src/playsms-1.4.3/
    cp install.conf.dist install.conf
    vi install.conf
    ```

3.  Run playSMS install script

    ```bash
    cd /home/komodo/src/playsms-1.4.3/
    ./install-playsms.sh
    ```

4.  Configure `rc.local` to get `playsmsd` started on boot

    Look for `rc.local` on `/etc`, `/etc/init.d` or `/etc/rc.d/init.d`

    When you found it edit that `rc.local` and put:

    `/home/komodo/bin/playsmsd start`

    on the bottom of the file (before exit if theres an exit command).

    This way `playsmsd` will start automatically on boot.

Note:

* After successful installation, please run command `ps ax` and see if `playsmsd` is running

  ```bash
  ps ax | grep playsms
  4069 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /etc/playsmsd.conf schedule
  4071 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /etc/playsmsd.conf dlrssmsd
  4073 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /etc/playsmsd.conf recvsmsd
  4075 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /etc/playsmsd.conf sendsmsd
  ```

* Run several checks

  ```bash
  /home/komodo/bin/playsmsd status
  /home/komodo/bin/playsmsd check
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

1.  Extract playSMS package in playSMS source directory

    In this example your Linux user is `komodo` and your home directory is `/home/komodo`. Your playSMS source directory will be `/home/komodo/src/playSMS-1.4.3`.
    
    In real installation you need to use your own Linux user and home directory or wherever you want to and have access to install playSMS.
    
    ```bash
    mkdir -p /home/komodo/src
    tar -zxf playsms-1.4.3.tar.gz -C /home/komodo/src
    ls -l /home/komodo/src/
    ```

2.  Run `getcomposer.sh`

    ```bash
    cd /home/komodo/src/playsms-1.4.3/
    ./getcomposer.sh
    ```

3.  Create playSMS web root, log, lib, bin and set ownership to web server's user for example **www-data**

    Assumed that your web root is `/home/komodo/public_html`.
    
    In real installation you need to use your own web root and you must have access to it.

    ```bash
    mkdir -p /home/komodo/public_html/playsms /home/komodo/log /home/komodo/lib /home/komodo/bin
    ```

4.  Copy files and directories inside `web` directory to playSMS web root and set ownership to web server user

    ```bash
    cd /home/komodo/src/playsms-1.4.3/
    cp -R web/* /home/komodo/public_html/playsms
    ```
    
    Next, you need to set permission to web server's user, in this example to **www-data**.
    
    Please note that there are Linux distribution using different user name for web server's user.
    
    ```bash
    sudo chown -R www-data /home/komodo/public_html/playsms
    ```

5.  Setup database (import database)

    ```bash
    sudo mysqladmin -u root -p create playsms
    cat db/playsms.sql | mysql -u root -p playsms
    ```
    
    Please note it is recommended to create non-root MySQL user and use it on playSMS.

6.  Copy `config-dist.php` to `config.php` and then edit `config.php`

    Go to playSMS installation web root.

    ```bash
    cd /home/komodo/public_html/playsms
    cp config-dist.php config.php
    vi config.php
    ```

    Please read and fill all fields with correct values

7.  Enter daemon/linux directory, copy files and folder, and set correct permission

    Go back to playSMS source directory.

    ```bash
    cd /home/komodo/src/playsms-1.4.3/
    sudo cp daemon/linux/etc/playsmsd.conf /etc/playsmsd.conf
    cp daemon/linux/bin/playsmsd.php /home/komodo/bin/playsmsd
    chmod +x /home/komodo/bin/playsmsd
    ```

8.  Just to make sure every paths are correct, please edit `/etc/playsmsd.conf`

    ```bash
    sudo vi /etc/playsmsd.conf
    ```

    Make sure that `PLAYSMS_PATH` is pointing to a correct playSMS installation path (in this example to `/home/komodo/public_html/playsms`)

    Also Make sure that `PLAYSMS_BIN` is pointing to a correct playSMS daemon scripts path (in this example to `/home/komodo/bin`)

9.  Start `playsmsd` now from Linux console, no need to reboot

    ```bash
    /home/komodo/bin/playsmsd start
    ```

10. Configure `rc.local` to get `playsmsd` started on boot

    Look for `rc.local` in `/etc`, `/etc/init.d` or `/etc/rc.d/init.d`

    When you found it edit that `rc.local` and put:

    `/home/komodo/bin/playsmsd start`

    on the bottom of the file (before exit if theres an exit command).

    This way `playsmsd` will start automatically on boot.

Note:

* After successful installation, please run command `ps ax` and see if `playsmsd` is running

  ```bash
  ps ax | grep playsms
  4069 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /etc/playsmsd.conf schedule
  4071 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /etc/playsmsd.conf dlrssmsd
  4073 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /etc/playsmsd.conf recvsmsd
  4075 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /etc/playsmsd.conf sendsmsd
  ```

* Run several checks

  ```bash
  /home/komodo/bin/playsmsd status
  /home/komodo/bin/playsmsd check
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
