INSTALL GAMMU
=============

This is an example of Gammu installation in **Ubuntu 15.04**.

Your have to install both **playSMS** and **Gammu** on the same server.

There are 2 methods of installation available:

- first is to install from source
- secondly is to install via apt-get.


I. Install Gammu from source
----------------------------

1. Make sure you have root access

    ```
    sudo su -
    ```

2. Install required packages for building Gammu from source in Ubuntu

    ```
    apt-get install libcurl4-openssl-dev libusb-1.0-0-dev libbluetooth-dev libmysqlclient15-dev cmake
    ```

3. Download Gammu, navigate to Gammu source package file location and extract
   
   Download `gammu-1.28.0.tar.bz2` from http://wammu.eu and save it in /usr/local/src
   
    ```
    cd /usr/local/src
    tar -jxf gammu-1.28.0.tar.bz2
    ```

4. Enter extracted Gammu source package directory and compile Gammu

    ```
    cd gammu-1.28.0
    ./configure
    make
    make test
    make install
    ```

5. Create required directories (case sensitive)

    ```
    mkdir -p /var/log/gammu /var/spool/gammu/{inbox,outbox,sent,error}
    ```

6. Setup Gammu spool directories owner and permission

   In this example your webserver user and group is `www-data`
   
    ```
    chown www-data:www-data -R /var/spool/gammu/*
    ```
    
    OR
    
    ```
    chmod 777 -R /var/spool/gammu/*
    ```

7. Copy `[this_playsms_package]/contrib/gammu/gammu-smsdrc` to `/etc/`

    ```
    cp [this_playsms_package]/contrib/gammu/gammu-smsdrc /etc/
    ```
    
   Note:
   
   - Before continue to step 8 you might want to take a look `/etc/gammu-smsdrc` and edit the file accordingly. Do not change file or directory paths.

8. Start `gammu-smsd` using example script `gammu_smsd_start` from `[this_playsms_package]/bin`

    ```
    cd [this_playsms_package]/bin
    cp gammu_smsd_start /usr/local/bin
    ```
    
   Note:
   
   - You might also want to put `gammu_smsd_start` in `/etc/rc.local`to get `gammu-smsd` started on boot

9. Run gammu_smsd_start to start `gammu-smsd`

    ```
    gammu_smsd_start
    ```


II. Install via apt-get
-----------------------

1. Make sure you have root access
    
    ```
    sudo su -
    ```

2. Install Gammu via `apt-get`

    ```
    apt-get install gammu-smsd
    ```

3. Create required directories (case sensitive)

    ```
    mkdir -p /var/log/gammu /var/spool/gammu/{inbox,outbox,sent,error}
    ```

4. Setup Gammu spool directories owner and permission

   In this example your webserver user and group is `www-data`
   
    ```
    chown www-data:www-data -R /var/spool/gammu/*
    ```
    
    OR
    
    ```
    chmod 777 -R /var/spool/gammu/*
    ```

5. Copy `[this_playsms_package]/contrib/gammu/gammu-smsdrc` to `/etc/`

    ```
    cp [this_playsms_package]/contrib/gammu/gammu-smsdrc /etc/
    ```
    
   Note:
   
   - Before continue to step 6 you might want to take a look `/etc/gammu-smsdrc`and edit the file accordingly. Do not change file or directory paths.

6. Run gammu-smsd

    ```
    # /etc/init.d/gammu-smsd start
    ```
   Note:
   
   - You don't need to put the script in /etc/rc.local

