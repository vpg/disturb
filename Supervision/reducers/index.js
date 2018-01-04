import { combineReducers } from 'redux';

// All app reducers
import workflow from './workflow';
import workers from './workers';

export default combineReducers({
    workflow,
    workers
})
