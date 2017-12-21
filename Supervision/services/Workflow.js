import Elasticsearch from 'elasticsearch'

import * as execTimeQuery from '../queries/stepsExecTime.json'

class Workflow {
    constructor() {
        this.client = new Elasticsearch.Client({
            host: 'http://192.168.100.100:9200',
            log: 'trace'
        });
    }

    get(id) {
        console.log(`Workflow.get`, id)
        return this.client.search(
            {
                index: 'disturb_context',
                type: 'workflow',
                body: {
                    query: {
                        match: {
                            _id: id
                        }
                    }
                }}
        )
    }

    execTime(date) {
        console.log(`Workflow.execTime`)
        return this.client.search(
            {
                index: 'disturb_context',
                type: 'workflow',
                body: execTimeQuery.query
            }
        )
        .then( data => {
            console.log(data)
            const stepHashList = data.aggregations.group_by_date.buckets[0].steps.group_by_stepname.buckets.map( step => {
                return {
                    code: step.key,
                    exectime: step.to_job.avg_job_exectime_in_sec.value
                };
            })
            return Promise.resolve(stepHashList);
        
        })
    }
}


export default Workflow
