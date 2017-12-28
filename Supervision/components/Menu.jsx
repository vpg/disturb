import React from 'react'
import PropTypes from 'prop-types'
import { Link } from 'react-router-dom'

import {withTheme, withStyles} from 'material-ui/styles'
import Drawer from 'material-ui/Drawer'
import List, { ListItem, ListItemAvatar, ListItemIcon, ListItemText } from 'material-ui/List'
import Divider from 'material-ui/Divider'
import WorkflowIcon from 'material-ui-icons/Share'
import WorkerIcon from 'material-ui-icons/FitnessCenter'
import SettingsIcon from 'material-ui-icons/Settings'

import IconButton from 'material-ui/IconButton'

import { APP_SETTING } from '../config'

const styles = theme => ({
    drawerPaper: {
        position: 'relative',
        height: '100%',
        width: APP_SETTING.DrawerWidth,
      },
      drawerHeader: theme.mixins.toolbar,
})


class Menu extends React.Component {
    constructor(props){
        super(props);
        this.state = {
        };
    }

    render() {
        const { classes, className } = this.props;
        return (
            <div className={className}>
                <Drawer type="permanent" anchor="left" classes={{
                    paper: classes.drawerPaper,
                }}>
                <div className={classes.drawerHeader} />
                <List>
                    <ListItem button component={Link} to="/workflows">
                        <ListItemIcon>
                            <WorkflowIcon />
                        </ListItemIcon>
                        <ListItemText primary="Workflows" />
                    </ListItem>
                    <ListItem button component={Link} to="/workers">
                        <ListItemIcon>
                            <WorkerIcon />
                        </ListItemIcon>
                        <ListItemText primary="Workers" />
                    </ListItem>
                    <ListItem button component={Link} to="/settings">
                        <ListItemIcon>
                            <SettingsIcon />
                        </ListItemIcon>
                        <ListItemText primary="Settings" />
                    </ListItem>
                </List>
            </Drawer>
        </div>
        )
    }
}

Menu.propTypes = {
    classes: PropTypes.object.isRequired,
    className: PropTypes.string,
}

export default withTheme()(withStyles(styles)(Menu));

