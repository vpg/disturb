#!/bin/bash

# TODO : Get host & port parameters
HOST="192.168.100.100"
PORT="9200"


echo "ELS INIT INDEX"
echo "--------------"

INIT_FOLDER_PATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# Elasticsearch indexes to create
INDEX_LIST=('disturb_context_storage')

for index in $INDEX_LIST; do

    echo "Initializing index $index"

    INDEX_EXISTS_HTTP_CODE="$(curl -sL -w "%{http_code}\\n" $HOST:$PORT/$index -o /dev/null)"
    if [[ $INDEX_EXISTS_HTTP_CODE == 200 ]]; then
        echo "'$index' index already exists"
        continue
    fi
    #create index with mapping
    curl -sH "Content-Type: application/json" --data "@$INIT_FOLDER_PATH/init_files/${index}_index.json" $HOST:$PORT/$index -o /dev/null
    #create alias
    curl -sH "Content-Type: application/json" --data "@$INIT_FOLDER_PATH/init_files/${index}_alias.json" $HOST:$PORT/_aliases -o /dev/null

    echo "$index index created"
done;