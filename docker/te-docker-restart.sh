#!/bin/bash
sudo service nginx stop
sudo service mysql stop
sudo systemctl disable mysql
sudo systemctl disable nginx
cd /var/www/timeeffect/docker
sudo docker-compose down
sudo docker-compose up -d