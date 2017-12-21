import React, { Component } from 'react';
import ReactDOM from 'react-dom'
import { BrowserRouter, Switch, Route } from 'react-router-dom'

// Redux
import { createStore, applyMiddleware } from 'redux'
import { Provider, connect } from 'react-redux'
import thunk from 'redux-thunk';
import { createEpicMiddleware } from 'redux-observable';

import rx from 'rxjs'

// UI
import { MuiThemeProvider, createMuiTheme, withTheme } from 'material-ui/styles';

//import relectronReducers from './reducers/index'
//import {relectronEpics} from './epics/index'

import App from './components/App.jsx'
import Home from './components/Home.jsx'
import Workers from './components/Workers.jsx'
import Workflows from './components/Workflows.jsx'
import Settings from './components/Settings.jsx'

const notificationStream = new rx.Subject();

// Reduc Store
var initialState = {
    notification: {notificationStream}
}
/*
const epicMiddleware = createEpicMiddleware(relectronEpics)
const createStoreWithMiddleware = applyMiddleware(thunk, epicMiddleware)(createStore)
*/
//const store = createStoreWithMiddleware(relectronReducers, initialState);

const theme = createMuiTheme({
    palette: {
        type: 'dark',
    },
});

class Main extends Component {
    constructor (props) {
        super(props)
    }
            //<Provider store={store}>
    render() {
        return (
            <Provider>
                <BrowserRouter>
                    <MuiThemeProvider theme={theme}>
                        <App loginStream={this.loginStream} repositoryStream={this.repositoryStream}>
                            <Switch>
                                <Route exact path="/" render={() => <Home />} />
                                <Route path="/workflows" render={() => <Workflows />} />
                                <Route path="/workers" render={() => <Workers />} />
                                <Route path="/Settings" render={() => <Settings />} />
                            </Switch>
                        </App>
                    </MuiThemeProvider>
                </BrowserRouter>
            </Provider>
        )
    }
}
ReactDOM.render(<Main />, document.getElementById('root'))
