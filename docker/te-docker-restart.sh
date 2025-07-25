#!/bin/bash
sudo service nginx stop
sudo service mysql stop
cd /var/www/timeeffect/docker
sudo docker-compose down
sudo docker-compose up -d