import * as actionTypes from '../config/actionTypes'

export default function(state, action) {
    console.log('reducer.topics', state, action)
    if(state === undefined) {
        return {}
    }
    switch(action.type) {
        case actionTypes.DISPLAY_TOPIC_LIST:
            return Object.assign(
                {},
                state,
                {
                    topicHashList: action.topicHashList
                }
            )
            break
    }
    return state
}
