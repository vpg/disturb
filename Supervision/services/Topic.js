import Zookeeper from 'node-zookeeper-client'

class Topic {
    constructor() {
        const connectionString = '10.13.11.27:2181,10.13.11.28:2181,10.13.11.29:2181'
        this.zkClient = new Zookeeper.createClient(connectionString);
    }

    fetchTopicList(topicFetchedCallback) {
        console.log(`Worker.fetchConsumerGroupList`)
        const topicPath = '/brokers/topics'
        this.zkClient.once('connected', () => {
            this.zkClient.getChildren(
                topicPath,
                (error, topicList, stat) => {
                    if (error) {
                        console.log(`Failed to fetch topic list w/ zookeeper to: ${error}`);
                        return;
                    }
                    const topicHashList = [];
                    for (var key in topicList) {
                        let topicName = topicList[key]
                        let topicHash = {name : topicName}
                        this.zkClient.getChildren(
                            `${topicPath}/${topicName}/partitions`,
                            (error, partitionList, stat) => {
                                console.log('partitions', topicName, partitionList)
                                if (error) {
                                    console.log(`Failed to fetch topic list w/ zookeeper to: ${error}`);
                                    return;
                                }
                                topicHash.nbPartition = partitionList.length
                                topicHashList.push(topicHash);
                            }
                        );
                    }
                    topicFetchedCallback(topicHashList);
                }
            );
        })
        this.zkClient.connect();
    }
}


export default Topic
