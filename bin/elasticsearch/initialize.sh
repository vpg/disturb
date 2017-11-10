#!/bin/bash

echo "------------------------------------------------"
echo "- DISTURB Elasticsearch context initialization -"
echo "------------------------------------------------"

if [[ $# != 1  ]]; then
    echo "You must set elasticsearch host as parameter (ex: http://localhost, https://localhost, http://localhost::443)"
    exit 0
fi

HOST=$1

INIT_FOLDER_PATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# Elasticsearch indexes to create
INDEX_LIST=('disturb_context-1.0.0-beta')

for index in $INDEX_LIST; do

    INDEX_EXISTS_HTTP_CODE="$(curl -k -sL -w "%{http_code}\\n" $HOST/$index -o /dev/null)"
    if [[ $INDEX_EXISTS_HTTP_CODE == 200 ]]; then
        echo "-> $index : index already exists"
        continue
    fi

    #create index with mapping
    INDEX_HTTP_CODE="$(curl -k -w "%{http_code}\\n" -X PUT -sH "Content-Type: application/json" --data "@$INIT_FOLDER_PATH/init_files/${index}_index.json" $HOST/$index -o /dev/null)"
    if [[ $INDEX_HTTP_CODE == 200 ]]; then
        echo "-> $index : index created"
    else
       echo "-> $index : Error on create index code $INDEX_HTTP_CODE"
    fi

    #create alias
    ALIAS_HTTP_CODE="$(curl -k -w "%{http_code}\\n" -X POST -sH "Content-Type: application/json" --data "@$INIT_FOLDER_PATH/init_files/${index}_alias.json" $HOST/_aliases -o /dev/null)"
    if [[ $ALIAS_HTTP_CODE == 200 ]]; then
        echo "-> $index : alias created"
    else
       echo "-> $index : Error on create alias $ALIAS_HTTP_CODE"
    fi
done;
