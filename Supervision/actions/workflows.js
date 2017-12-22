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

const fetchStepsExecTime = (date) => {
    return function (dispatch, getState) {
        console.log('actions.workflows.fetchStepsExecTime')
        const state = getState()
        console.log('STATE', state)
        const wf = new Workflow();
        let promList = [
            wf.execTime('test_50'),
            wf.pendingTime('test_50')
        ]
        Promise.all(promList)
        .then( resultList => {
            dispatch(displayExectimeGraph(date, resultList))
        });
    }
}

const displayExectimeGraph = (date, data) => {
    console.log('actions.workflows.displayExectimeGraph')
    return {
        type: actionTypes.DISPLAY_EXECTIME_GRAPH,
        date: date,
        data: data
    }
}

export {
    fetchWorkflowById,
    fetchStepsExecTime
}
