**To launch disturb tests suite**

`./vendor/phpunit/phpunit/phpunit -c Tests/phpunit.xml`


**Configure context storage for testing**

ElasticsearchAdapter

Initialize index : `./bin/elasticsearch/initialize.sh YOUR_ELASTICSEARCH_HOST`

Change test config host to YOUR_ELASTICSEARCH_HOST in `./Tests/Library/ContextStorageAdapters/Config/elasticsearchConfig.json`
