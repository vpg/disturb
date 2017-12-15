### Start PM2 example
#### Install PM2
```
npm install -g pm2
```

#### Define env vars
```
export DISTURB_ELASTIC_HOST=http://10.13.22.227:9200 DISTURB_KAFKA_BROKER=10.13.11.27,10.13.11.28,10.13.11.29
```

#### Start manager & workers
```
cd example
pm2 start ecosystem.config.js
```

#### Delete manager & workers
```
pm2 delete all
```

#### Monitor globbal logs
```
pm2 logs
```

#### Send msg to start the example workflow
```
php simple-producer.php '{"id":"test_'$(date +"%s")'", "type" : "WF-CONTROL", "action":"start", "payload": {"foo":"bar"}}' disturb-test-manager 10.13.11.27,10.13.11.28,10.13.11.29
```
