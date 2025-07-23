#!/bin/bash

#!/usr/bin/tmux source-file

tmux new-session -d
tmux new-window \; split-window -p 66 #\; split-window -d \; split-window -h

cd /var/www/spacetrace/dev
tmux send-keys -t 0 'sudo service nginx stop; sudo service docker start; cd /var/www/timeeffect/docker; sudo docker-compose up' enter
tmux send-keys -t 1 'cd /var/www/timeeffect/docker; sudo docker logs timeeffect_app_1 -f' 
tmux send-keys -t 2 'cd /var/www/timeeffect' enter

tmux select-pane -t 0

tmux attach

