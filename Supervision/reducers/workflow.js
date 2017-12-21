import * as actionTypes from '../config/actionTypes'

export default function(state, action) {
    console.log('reducer.workflow', state, action)
    if(state === undefined) {
        return {}
    }
    let newState = null
    switch(action.type) {
        case actionTypes.DISPLAY_EXECTIME_GRAPH:
            console.log('FETCH_WF', state, action)
            return Object.assign(
                {},
                state,
                {
                    stats: {
                        stepsExectimeData: action.data
                    }
                }
            )
            break
    }
    return state
}
