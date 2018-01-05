import React from 'react'
import PropTypes from 'prop-types'


// UI
import {withTheme, withStyles} from 'material-ui/styles'

import Menu from './Menu.jsx'
import TopBar from './TopBar.jsx'

import { APP_SETTING } from '../config'


// XXX Move css outside
const styles = theme => ({
    root: {
        width: '100%',
        height: '100%',
        zIndex: 1,
        overflow: 'hidden',
    },
    appFrame: {
        position: 'relative',
        display: 'flex',
        width: '100%',
        height: '100%',
    },
    content: {
        backgroundColor: theme.palette.background.default,
        width: '100%',
        padding: theme.spacing.unit * 3,
        height: 'calc(100% - 56px)',
        marginTop: 56,
        [theme.breakpoints.up('sm')]: {
            height: 'calc(100% - 64px)',
            marginTop: 64,
        },
    },
})

class App extends React.Component {
    constructor(props){
        super(props);
        this.state = {
        }
    }

    render() {
        const { children, classes } = this.props
        return (
            <div className={classes.root}>
                <div className={classes.appFrame}>
                    <TopBar />
                    <Menu />
                    <main className={classes.content}>
                        {children}
                    </main>
                </div>
            </div>
        )
    }
}

App.propTypes = {
    children: PropTypes.node.isRequired,
    classes: PropTypes.object.isRequired,
};

export default withTheme()(withStyles(styles)(App));
