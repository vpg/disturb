 <h1 align="center">DISTURB</h1>
 
 <p align="center">
 <b>Distributed Workflow Processor</b>, based on <a href="https://github.com/phalcon/cphalcon">Phalcon</a> and <a href="https://kafka.apache.org/">Kafka</a>
 </p>

----

[![](https://img.shields.io/travis/vpg/disturb.svg)](https://travis-ci.org/vpg/disturb)
[![Coverage Status](https://coveralls.io/repos/github/vpg/disturb/badge.svg?branch=alpha&service=github)](https://coveralls.io/github/vpg/disturb?branch=alpha)
[![license](https://img.shields.io/github/license/vpg/disturb.svg)]()


## Arch
![Arch](https://raw.githubusercontent.com/wiki/vpg/disturb/images/disturb_arch.png)

## Usage

### Requirements
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

## Run

### Run Kafka + Zookeeper

```
wget ftp://mirrors.ircam.fr/pub/apache/kafka/0.10.1.0/kafka_2.11-0.10.1.0.tgz  
tar --extract --gzip --file kafka*
```
either use standalone zookeeper bin included in kafka or install it
cd kafka_*
./bin/zookeeper-server-start.sh config/zookeeper.properties
 
```
$ sudo service zookeeper start
$ ./bin/kafka-server-start.sh config/server.properties
```

### Run Manager
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

## Debug

### Enable verbose debug
```
$ export DISTURB_DEBUG=true
```
