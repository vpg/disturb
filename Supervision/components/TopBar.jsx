import React from 'react'
import PropTypes from 'prop-types'
import {withRouter} from 'react-router-dom';

import {withTheme, withStyles} from 'material-ui/styles'
import Typography from 'material-ui/Typography'
import AppBar from 'material-ui/AppBar'
import Toolbar from 'material-ui/Toolbar'
import WorkflowIcon from 'material-ui-icons/Share'
import WorkerIcon from 'material-ui-icons/FitnessCenter'
import SettingsIcon from 'material-ui-icons/Settings'
import HomeIcon from 'material-ui-icons/Home'

import { APP_SETTING } from '../config'

const styles = theme => ({
    appBar: {
        position: 'absolute',
        width: `calc(100% - ${APP_SETTING.DrawerWidth}px)`,
        marginLeft: APP_SETTING.DrawerWidth,
    }
});

const getPathNameLabel = (pathName) => {
    let routeName = pathName.substring(pathName.lastIndexOf("/") + 1);

    switch(routeName) {
        case "workflows": {
            return {
                icon: <WorkflowIcon style={{width: 24, height: 24, marginBottom: -3}}/>,
                name: uppercaseRoute(routeName)
            };
            break;
        }
        case "workers": {
            return {
                icon: <WorkerIcon style={{width: 24, height: 24, marginBottom: -3}}/>,
                name: uppercaseRoute(routeName)
            };
            break;
        }
        case "settings": {
            return {
                icon: <SettingsIcon style={{width: 24, height: 24, marginBottom: -3}}/>,
                name: uppercaseRoute(routeName)
            };
            break;
        }

        default: {
            return {
                icon: <HomeIcon style={{width: 24, height: 24, marginBottom: -3}}/>,
                name: 'Home'
            };
            break;
        }
    }
};

const uppercaseRoute = (routeName) => {
    return routeName.charAt(0).toUpperCase() + routeName.slice(1)
}

class TopBar extends React.Component {
    constructor(props){
        super(props);
        this.state = {
        };

        console.log(PropTypes);
    }

    render() {
        const { classes, className, location } = this.props;

        return (
            <div className={className}>
                <AppBar position="static" color="default" className={classes.appBar}>
                    <Toolbar>
                        <Typography type="title" color="inherit">
                            Supervision / {getPathNameLabel(location.pathname).icon} {getPathNameLabel(location.pathname).name}
                        </Typography>
                    </Toolbar>
                </AppBar>
            </div>
        )
    }
}

TopBar.propTypes = {
    classes: PropTypes.object.isRequired,
    className: PropTypes.string,
    location: PropTypes.object.isRequired
}

export default withTheme()(withStyles(styles)(withRouter(TopBar)));