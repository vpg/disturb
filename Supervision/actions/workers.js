import rx from 'rxjs'

import Worker from '../services/Worker'
import * as actionTypes from '../config/actionTypes'


const fetchConsumerGroupList = () => {
    return function (dispatch, getState) {
        console.log('actions.workers.fetchConsumerGroupList')
        const state = getState()
        console.log('STATE', state)
        const workers = new Worker();
        workers.fetchConsumerGroupList().then( consumerGroupList => {
            console.log('ok', consumerGroupList)
            dispatch(displayConsumerGroupList(consumerGroupList))
        })

    }
}

const displayConsumerGroupList = (consumerGroupList) => {
    console.log('actions.workflows.displayExectimeGraph')
    return {
        type: actionTypes.DISPLAY_CONSUMER_GROUP_LIST,
        consumerGroupList
    }
}

export {
    fetchConsumerGroupList
}
