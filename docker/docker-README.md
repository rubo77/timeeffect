# install

# If you want to install a minimal docker-compose without loads of not-needed stuff:
sudo apt install docker-compose --no-install-recommends
sudo apt remove docker-compose
sudo apt install docker-compose  bridge-utils cgroupfs-mount containerd docker.io pigz runc 
# I removed it and installed again, because at the first round it suggested to install these too which I don't need:
# bridge-utils cgroupfs-mount containerd docker.io golang-docker-credential-helpers pigz python3-cached-property python3-docker python3-dockerpty python3-dockerpycreds python3-docopt python3-jsonschema python3-texttable  python3-websocket runc ubuntu-fan

To get the app up and running in docker, follow these 3 steps:

# 1. start in tmux with

    tmux
    sudo su
    # just in case you have it running:
    systemctl stop mysql
    systemctl stop nginx

# 1. Automated Setup (RECOMMENDED):

    cd /var/www/timeeffect/docker/
    ./setup.sh

# OR Manual start:

    #systemctl disable mysql
    #systemctl disable nginx
    # now start the docker container:
    cd /var/www/timeeffect/docker/
    sudo docker-compose up --build

# 2. install fresh:

    sudo docker exec -i -t docker_app_1 bash -l
    # workaround:
    apt update; apt install php-cli php-mysql

**Note**: Updated to PHP 8.4 with modern infrastructure!
- Composer dependencies are automatically available
- Modern logging to `/var/www/html/logs/app.log`
- PEAR DB compatibility layer active

open in webbrowser:

http://localhost/install

# or import mysql tables:


    # install downloaded database:
    cd /var/www/html/dev/db
    echo "CREATE database timeeffect_db;"|mysql -u root -pvery_unsecure_timeeffect_PW1 --protocol tcp
    mysql timeeffect_db -u root -pvery_unsecure_timeeffect_PW1 --protocol tcp < db.sql
    # minimal tables so the app runs:
    for SQL in timeeffect*.sql; do
      echo "importing $SQL ...";
      mysql timeeffect_db -u root -pvery_unsecure_timeeffect_PW1 --protocol tcp < $SQL
    done
    # better download an actual db and then:
    # mysql timeeffect_db -u root -pvery_unsecure_timeeffect_PW1 --protocol tcp</var/tmp/timeeffect.sql

    echo "GRANT ALL PRIVILEGES ON timeeffect_db.* TO 'timeeffect'@'%' WITH GRANT OPTION;GRANT ALL PRIVILEGES ON timeeffect_db.* TO 'timeeffect'@'%' WITH GRANT OPTION;"|mysql timeeffect_db -u root -pvery_unsecure_timeeffect_PW1 --protocol tcp
    
## alternative: mysql import
    
    import your mysql files into `/var/lib/docker/volumes/timeeffect_db/_data/timeeffect` 
    and `/var/lib/docker/volumes/timeeffect_db/_data/timeeffect_db`

# 3. ssh into the docker-container

    #docker exec -i -t timeeffect_db_1 bash -l
    sudo docker exec -i -t timeeffect_app_1 bash -l
    apt update; apt install nano vim mlocate --no-install-recommends
    updatedb
    nano /etc/apache2/sites-enabled/000-default*
    # or vi /etc/apache2/sites-enabled/000-default*

    add this:
    <VirtualHost *:443>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
    </VirtualHost>
    
    in nano press ^X to save, in `vi` use i to enter insert mode and ESC Shift+Z+Z to save and exit

# 4. Login in webbrowser
open in webbrowser:

http://localhost
## or 
http://timeeffect.lvh.me

login as user: pirates, pass: vt8yhnan and build its diplomatic vessel and move it into fleet 1

# Now everything is set up!

# next time start
next time, you can simply start 

    docker-compose up

And leave that window open. To do this in tmux automatically, use the script `docker/tmux-timeeffect`

---    
  
# debugging

leave the `docker-compose up` shell open, there you see the debug output of the container.

## ssh into the docker-container:

    sudo docker exec -i -t timeeffect_app_1 bash -l
    tail -f /var/log/apache2/error.log


## check if database is running:

    mysql timeeffect -u timeeffect -pvery_unsecure_timeeffect_PW1 --protocol tcp

## dump a databasetable:
    mysqldump timeeffect example -u timeeffect -pvery_unsecure_timeeffect_PW1 --protocol tcp

## call a php command inside

restart apache if it hangs:

    sudo docker exec -i -t timeeffect_app_1 bash -c 'apache2ctl restart'

