# Disturb
*Distributed Workflow Processor*  
Based on Phalcon and Kafka  

*This project is not nammed : FlowEr, PlayFlow, DistriFlow, Sharp, Flowber, OctoFlow, ParaFlow, Flowter, Flowel, Flowable, Flowbert, Next*

## Arch.
![Arch](https://raw.githubusercontent.com/wiki/vpg/disturb/images/disturb_arch.png)

## TODO
 See `xxx` tag in the code

## Usage
### prep requirements
Kafka + Zookeeper : https://kafka.apache.org/quickstart

### Install \Vpg\Disturb
Add it to your project w/ compposer
```
    "require": {
        "vpg/disturb": "dev-poc"
    }
```

### Configure Context Storage 
Elasticsearch 

Initialize index
```
   ./bin/elasticsearch/initialize.sh YOUR_ELASTICSEARCH_HOST
```

### Define workflow
![see example](https://github.com/vpg/disturb/tree/poc/example/Config)

### Code Logic
Code your services (Steps and Manager) logic
![see example](https://github.com/vpg/disturb/tree/poc/example/Services)

### Run
Run Manager
```
vendor/bin/disturb-manager  --workflow="/path/to/disturb/example/Config/parallelized.json"
```

### Run Step
```
vendor/bin/disturb-step --step="step0" --workflow="/path/to/disturb/example/Config/parallelized.json"
vendor/bin/disturb-step --step="step1" --workflow="/path/to/disturb/example/Config/parallelized.json"
vendor/bin/disturb-step --step="step2" --workflow="/path/to/disturb/example/Config/parallelized.json"
vendor/bin/disturb-step --step="step3" --workflow="/path/to/disturb/example/Config/parallelized.json"
```

### Start a workflow exec
```
php producer.php '{"contract":"FR-10-BOOOM", "type" : "WF-CONTROL", "action":"start"}' disturb-foo-manager
```
