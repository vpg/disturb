import rx from 'rxjs'

import Topic from '../services/Topic'
import * as actionTypes from '../config/actionTypes'


const fetchTopicList = () => {
    return function (dispatch, getState) {
        console.log('actions.topics.fetchTopicList')
        const state = getState()
        console.log('STATE', state)
        const topic = new Topic();
        topic.fetchTopicList(topicHashList => {
            dispatch(displayTopicList(topicHashList))
        })
    }
}

const displayTopicList = (topicHashList) => {
    console.log('actions.topics.displayTopicList')
    return {
        type: actionTypes.DISPLAY_TOPIC_LIST,
        topicHashList
    }
}

export {
    fetchTopicList
}
