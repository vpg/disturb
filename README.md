# Disturb
*Distributed Workflow Processor*  
Based on Phalcon and Kafka  

*This project is not nammed : FlowEr, PlayFlow, DistriFlow, Sharp, Flowber, OctoFlow, ParaFlow, Flowter, Flowel, Flowable, Flowbert, Next*

## Arch.
![Arch](https://raw.githubusercontent.com/wiki/vpg/disturb/images/disturb_arch.png)

## TODO
 See `xxx` tag in the code

## Install
### Install Kafka + Zookeeper
```
wget ftp://mirrors.ircam.fr/pub/apache/kafka/0.10.1.0/kafka_2.11-0.10.1.0.tgz  
tar --extract --gzip --file kafka*
```
either use standalone zookeeper bin included in kafka or install it
cd kafka_*
./bin/zookeeper-server-start.sh config/zookeeper.properties

```
sudo apt-get install zookeeper
sudo apt-get install zookeeperd
sudo service zookeeper start
```

### Run Kafka
```
./bin/kafka-server-start.sh config/server.properties
```

## Run Example
### See Config
```
app/Config/workflow.json
```
### Start Loading Manager Worker
```
php app/cli.php "Tasks\\LoadingManager" start
```
### Start Steps Worker
```
php cli.php "Tasks\\Step" start step0
php cli.php "Tasks\\Step" start step1
```
