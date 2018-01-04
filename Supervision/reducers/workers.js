import * as actionTypes from '../config/actionTypes'

export default function(state, action) {
    console.log('reducer.workers', state, action)
    if(state === undefined) {
        return {}
    }
    switch(action.type) {
        case actionTypes.DISPLAY_CONSUMER_GROUP_LIST:
            return Object.assign(
                {},
                state,
                {
                    consumerGroupList: action.consumerGroupList
                }
            )
            break
    }
    return state
}
