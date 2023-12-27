# INSTALL

This document explains about how to install and setup playSMS version **1.4.7**


## Requirements

You will need a system consists of a Linux server with a working and running HTTPS enabled web server and PHP, also access to MySQL database server.

You will also need to have enough knowledge to install, upgrade and maintain above system.


## Installation

There are 2 methods explained in this document to install playSMS:

1. Installation on Linux using install-script
2. Installation on Linux step by step

You should pick **only one** method, do not do both methods.

Again, please do **only one** method, do not do both methods, unless of course you know what you're doing.


### Method 1: Installation on Linux using install-script

Install playSMS using install script `install-playsms.sh`

1.  Extract playSMS package in playSMS source directory

    In this example your Linux user is `komodo` and your home directory is `/home/komodo`. Your playSMS source directory will be `/home/komodo/src/playsms-1.4.7`.
    
    Note: In real installation you need to use your own Linux user and home directory or wherever you want to and have access to install playSMS.
    
    ```bash
    mkdir -p /home/komodo/src
    cd /home/komodo/src
    wget -c https://github.com/playsms/playsms/archive/refs/tags/1.4.7.tar.gz
    ls -l 1.4.7.tar.gz
    tar -zxf 1.4.7.tar.gz
    ls -l playsms-1.4.7
    ```

2.  Copy `install.conf.dist` to `install.conf` and edit `install.conf`

    Read `install.conf` and make changes to suit your system configuration

    ```bash
    cd /home/komodo/src/playsms-1.4.7/
    cp install.conf.dist install.conf
    vi install.conf
    ```

3.  Run playSMS install script

    ```bash
    cd /home/komodo/src/playsms-1.4.7/
    ./install-playsms.sh
    ```

4.  Configure `crontab` so that `playsmsd` will run automatically on boot and when its down accidentally

    ```bash
    crontab -e
    ```

    Insert this line to crontab's editor:

    `* * * * * /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf start`

    Save crontab's editor

Note:

* After successful installation, please run command `ps ax` and see if `playsmsd` is running

  ```bash
  ps ax | grep playsms
  4069 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf schedule
  4071 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf dlrssmsd
  4073 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf recvsmsd
  4075 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf sendsmsd
  ```

* Run several checks

  ```bash
  /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf status
  /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf check
  ```

* Stop here and review your installation steps when `playsmsd` is not running
* Consider to ask question in playSMS forum when you encountered any problem
* If all seems to be correctly installed you may try to login from web browser

  ```
  URL                    : http://[your web server IP]
  Default admin username : admin
  Default admin password : admin
  ```


### Method 2: Installation on Linux step by step

Install playSMS by following step-by-step:

1.  Extract playSMS package in playSMS source directory

    In this example your Linux user is `komodo` and your home directory is `/home/komodo`. Your playSMS source directory will be `/home/komodo/src/playSMS-1.4.7`.
    
    In real installation you need to use your own Linux user and home directory or wherever you want to and have access to install playSMS.
    
    ```bash
    mkdir -p /home/komodo/src
    cd /home/komodo/src
    wget -c https://github.com/playsms/playsms/archive/refs/tags/1.4.7.tar.gz
    ls -l 1.4.7.tar.gz
    tar -zxf 1.4.7.tar.gz
    ls -l playsms-1.4.7
    ```

2.  Run `getcomposer.sh`

    ```bash
    cd /home/komodo/src/playsms-1.4.7/
    ./getcomposer.sh
    ```

3.  Create playSMS web root, log, lib, bin and set ownership to web server's user for example **www-data**

    Assumed that your web root is `/home/komodo/web`.
    
    In real installation you need to use your own web root and you must have access to it.

    ```bash
    mkdir -p /home/komodo/etc /home/komodo/log /home/komodo/lib /home/komodo/bin
    ```

4.  Copy files and directories inside `web` directory to playSMS web root and set ownership to web server user

    ```bash
    cd /home/komodo/src/playsms-1.4.7/
    cp -R web/* /home/komodo/web/
    ```
    
    Next, you need to set permission to web server's user, in this example to **www-data**.
    
    Please note that there are Linux distribution using different user name for web server's user.
    
    ```bash
    sudo chown -R www-data /home/komodo/web
    ```

5.  Setup database (import database)

    ```bash
    sudo mysqladmin -u root -p create playsms
    cd /home/komodo/src/playsms-1.4.7/
    cat db/playsms.sql | mysql -u root -p playsms
    ```
    
    Please note it is recommended to create non-root MySQL user and use it on playSMS.

6.  Copy `config-dist.php` to `config.php` and then edit `config.php`

    Go to playSMS installation web root.

    ```bash
    cd /home/komodo/web
    cp config-dist.php config.php
    vi config.php
    ```

    Please read and fill all fields with correct values

7.  Enter daemon/linux directory, copy files and folder, and set correct permission

    Go back to playSMS source directory.

    ```bash
    cd /home/komodo/src/playsms-1.4.7/
    sudo cp daemon/linux/home/komodo/etc/playsmsd.conf /home/komodo/etc/playsmsd.conf
    cp daemon/linux/bin/playsmsd.php /home/komodo/bin/playsmsd
    chmod +x /home/komodo/bin/playsmsd
    ```

8.  Just to make sure every paths are correct, please edit `/home/komodo/etc/playsmsd.conf`

    ```bash
    sudo vi /home/komodo/etc/playsmsd.conf
    ```

    Make sure that `PLAYSMS_PATH` is pointing to a correct playSMS installation path (in this example to `/home/komodo/web`)

    Also Make sure that `PLAYSMS_BIN` is pointing to a correct playSMS daemon scripts path (in this example to `/home/komodo/bin`)

9.  Start `playsmsd` now from Linux console, no need to reboot

    ```bash
    /home/komodo/bin/playsmsd start
    ```

10. Configure `crontab` so that `playsmsd` will run automatically on boot and when its down accidentally

    ```bash
    crontab -e
    ```

    Insert this line to crontab's editor:

    `* * * * * /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf start`

    Save crontab's editor

Note:

* After successful installation, please run command `ps ax` and see if `playsmsd` is running

  ```bash
  ps ax | grep playsms
  4069 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf schedule
  4071 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf dlrssmsd
  4073 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf recvsmsd
  4075 pts/12  S    0:00 /usr/bin/php -q /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf sendsmsd
  ```

* Run several checks

  ```bash
  /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf status
  /home/komodo/bin/playsmsd /home/komodo/etc/playsmsd.conf check
  ```
  
* Stop here and review your installation steps when `playsmsd` is not running
* Consider to ask question in playSMS forum when you encountered any problem
* If all seems to be correctly installed you may try to login from web browser

  ```
  URL                    : http://[your web server IP]
  Default admin username : admin
  Default admin password : admin
  ```

### Closing

Further steps usually required to solidify playSMS installation, for example:

- To make sure the web server is HTTPS enabled with correct certificate
- To make sure that playSMS log files are writable by web server
- To add indexes for faster database access
- To install and maintain fail2ban and its playSMS jail configuration
- Other steps to improve performance and security

Discuss them more in https://playsms.discourse.group
