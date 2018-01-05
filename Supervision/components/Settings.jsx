import React, { Component } from 'react';
import PropTypes from 'prop-types'

import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import {withTheme, withStyles} from 'material-ui/styles'
import AppBar from 'material-ui/AppBar';
import Tabs, { Tab } from 'material-ui/Tabs';
import TimelineIcon from 'material-ui-icons/Timeline';
import SettingsIcon from 'material-ui-icons/Settings';
import PersonPinIcon from 'material-ui-icons/PersonPin';
import HelpIcon from 'material-ui-icons/Help';
import Typography from 'material-ui/Typography';

import * as actions from '../actions/workers.js';

const styles = theme => ({
  root: {
      flexGrow: 1,
      width: '100%',
      marginTop: theme.spacing.unit * 3,
      backgroundColor: theme.palette.background.paper,
    },
});

function TabContainer(props) {
  return (
      <Typography component="div" style={{ padding: 8 * 3 }}>
        {props.children}
      </Typography>
    );
}

class Settings extends Component {
    constructor(props){
        super(props);
        this.state = {
            tabId: 0
        }
    }

    switchTab = (event, tabId) => {
        this.setState({ tabId });
    }

    render() {
        const { classes } = this.props;
        const { tabId } = this.state;

        return (
            <div className={classes.root}>
                <AppBar position="static" color="default">
                    <Tabs
                        value={tabId}
                        onChange={this.switchTab}
                        indicatorColor="accent"
                        textColor="accent"
                        centered
                    >
                        <Tab label="General" icon={<SettingsIcon />} />
                        <Tab label="Graph" icon={<TimelineIcon />} />
                        <Tab label="Help" icon={<HelpIcon />} />
                    </Tabs>
                </AppBar>
                {tabId === 0 && <TabContainer>General settings</TabContainer>}
                {tabId === 1 && <TabContainer>Graph</TabContainer>}
                {tabId === 2 && <TabContainer>Help</TabContainer>}
            </div>
        )
    }
}

Settings.propTypes = {
  classes: PropTypes.object.isRequired,
};

const mapStateToProps = (state, ownProps) => {
    console.log('workflows.mapStateToProps', state, ownProps)
    return {
    }
}

const mapDispatchToProps = (dispatch, ownProps) => {
    console.log('workflows.mapDispatchToProps', dispatch, ownProps)
    return {
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withTheme()(withStyles(styles)(Settings)));
