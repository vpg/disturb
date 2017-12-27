import Elasticsearch from 'elasticsearch'

import moment from 'moment';

import * as wfHistoQuery from '../queries/workflowHisto.json'
import * as execTimeQuery from '../queries/stepsExecTime.json'
import * as pendingTimeQuery from '../queries/stepsPendingTime.json'

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

    execTime(from, to) {
        console.log(`Workflow.execTime`)
        from = typeof from == 'undefined' ? 'now-10d/d' : moment(from).format('YYYY-MM-DD h:mm:ss');
        to = typeof to == 'undefined' ? 'now/d' : moment(to).format('YYYY-MM-DD h:mm:ss');
        execTimeQuery.query.query.range.startedAt.gte = from;
        execTimeQuery.query.query.range.startedAt.lte = to;
        console.log('exec', JSON.stringify(execTimeQuery.query));
        return this.client.search(
            {
                index: 'disturb_context',
                type: 'workflow',
                body: execTimeQuery.query
            }
        )
        .then( data => {
            console.log('exec', data)
            const stepHashList = data.aggregations.group_by_date.buckets[0].steps.group_by_stepname.buckets.map( step => {
            console.log(step.key);
                return {
                    x: step.key,
                    y: step.to_job.avg_job_exectime_in_sec.value
                };
            })
            return Promise.resolve(stepHashList);
        })
    }


    pendingTime(from, to) {
        console.log(`Workflow.pendingTime`, from, to)
        from = typeof from == 'undefined' ? 'now-10d/d' : moment(from).format('YYYY-MM-DD h:mm:ss');
        to = typeof to == 'undefined' ? 'now/d' : moment(to).format('YYYY-MM-DD h:mm:ss');
        pendingTimeQuery.query.query.range.startedAt.gte = from;
        pendingTimeQuery.query.query.range.startedAt.lte = to;
        console.log(pendingTimeQuery.query);
        return this.client.search(
            {
                index: 'disturb_context',
                type: 'workflow',
                body: pendingTimeQuery.query
            }
        )

        .then( data => {
            console.log(data)
            const stepHashList = data.aggregations.group_by_date.buckets[0].steps.group_by_stepname.buckets.map( step => {
                return {
                    x: step.key,
                    y: step.to_job.avg_job_waiting_time_in_sec.value
                };
            })
            return Promise.resolve(stepHashList);
        })
    }

    getHisto2(from, to) {
        console.log(`Workflow.getHisto`, from, to)
        from = typeof from == 'undefined' ? 'now-10d/d' : moment(from).format('YYYY-MM-DD h:mm:ss');
        to = typeof to == 'undefined' ? 'now/d' : moment(to).format('YYYY-MM-DD h:mm:ss');
        execTimeQuery.query.query.range.startedAt.gte = from;
        execTimeQuery.query.query.range.startedAt.lte = to;
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
                    time: step.to_job.avg_job_exectime_in_sec.value
                };
            })
            return Promise.resolve(stepHashList);
        })
    }
    getHisto(from, to) {
        console.log(`Workflow.getHisto`, from, to)
        from = typeof from == 'undefined' ? 'now-10d/d' : moment(from).format('YYYY-MM-DD h:mm:ss');
        to = typeof to == 'undefined' ? 'now/d' : moment(to).format('YYYY-MM-DD h:mm:ss');
        wfHistoQuery.query.query.range.startedAt.gte = from;
        wfHistoQuery.query.query.range.startedAt.lte = to;
        return this.client.search(
            {
                index: 'disturb_context',
                type: 'workflow',
                body: wfHistoQuery.query
            }
        )
        .then( data => {
            const wfCountHashList = data.aggregations.group_by_date.buckets.map( step => {
                const statusAggs = step.group_by_status.buckets.reduce( 
                    (statusHash, status)  => {
                        statusHash[status.key] = status.doc_count;
                        return statusHash;
                    },
                    {
                        STARTED: 0,
                        SUCCESS: 0,
                        FAILED: 0
                    }
                );
                return {
                    x: step.key,
                    y: step.doc_count,
                    statusAggs
                };
            })
            const wfStatusCountHash = {
                started: [],
                success: [],
                failed: []
            }
            wfCountHashList.sort((a,b) => {
                return (a.x < b.x) ? -1 : (a.x > b.x) ? 1 : 0;
            });
            wfCountHashList.forEach( dailyStatsHash => {
                wfStatusCountHash.started.push( { x: dailyStatsHash.x, y: dailyStatsHash.statusAggs.STARTED })
                wfStatusCountHash.failed.push( { x: dailyStatsHash.x, y: dailyStatsHash.statusAggs.FAILED })
                wfStatusCountHash.success.push( { x: dailyStatsHash.x, y: dailyStatsHash.statusAggs.SUCCESS })
            });
            console.log(wfCountHashList)
            return Promise.resolve({wfCountHashList, wfStatusCountHash});
        })
    }
}


export default Workflow
