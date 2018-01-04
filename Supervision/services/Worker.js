import Kafka from 'no-kafka'

class Worker {
    constructor(kafkaHost) {

        const connectionHash = {
            connectionString: '10.13.11.27:9092,10.13.11.28:9092,10.13.11.29:9092'
        }
        this.admin = new Kafka.GroupAdmin(connectionHash);
    }

    fetchConsumerGroupList(onListFetchedCB) {
        console.log(`Worker.fetchConsumerGroupList`)
        return this.admin.init()
            .then(() => {
                return this.admin.listGroups()
            })
            .then(consumerGroupHash => {
                let consumerGroupPromiseList = [];
                consumerGroupHash.forEach(group => {
                    consumerGroupPromiseList.push(this.admin.describeGroup(group.groupId))
                })
                return Promise.all(consumerGroupPromiseList)
                    .then( resultList => {
                        console.log('consumerGroups', resultList);
                        return Promise.resolve(resultList);
                    })
            })
    }
}


export default Worker
