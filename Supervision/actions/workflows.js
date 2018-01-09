import rx from 'rxjs'

import Workflow from '../services/Workflow'
import * as actionTypes from '../config/actionTypes'

const fetchWorkflowById = () => {
    return function (dispatch, getState) {
        console.log('actions.workflows.fetchWorkflowById')
        const state = getState()
        console.log('STATE', state)
        const wf = new Workflow();
        wf.get('test_50')
        .then( workflow => {
            console.log(workflow)
        });
    }
}

const fetchStats = (dateHash) => {
    return function (dispatch, getState) {
        console.log('actions.workflows.fetchStats')
        const state = getState()
        console.log('STATE', state)

        const wf = new Workflow();
        let promList = [
            wf.execTime(dateHash['startDate'], dateHash['endDate']),
            wf.pendingTime(dateHash['startDate'], dateHash['endDate']),
            wf.getHisto(dateHash['startDate'], dateHash['endDate'])
        ]
        Promise.all(promList)
        .then( resultList => {
            dispatch(displayExectimeGraph(dateHash, resultList))
        });
    }
}

const displayExectimeGraph = (dateRange, data) => {
    console.log('actions.workflows.displayExectimeGraph')
    return {
        type: actionTypes.DISPLAY_EXECTIME_GRAPH,
        dateRange: dateRange,
        data: data
    }
}

export {
    fetchWorkflowById,
    fetchStats
}
