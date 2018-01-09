import { combineReducers } from 'redux';

// All app reducers
import workflow from './workflow';
import workers from './workers';
import topics from './topics';

export default combineReducers({
    topics,
    workflow,
    workers
})
