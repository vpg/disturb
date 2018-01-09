import React, { Component } from 'react';
import PropTypes from 'prop-types'

import {withTheme, withStyles} from 'material-ui/styles'
import AppBar from 'material-ui/AppBar';
import Tabs, { Tab } from 'material-ui/Tabs';
import TopicIcon from 'material-ui-icons/Subject';
import ConsumerIcon from 'material-ui-icons/SwapVert';
import WorkerIcon from 'material-ui-icons/FitnessCenter'
import Typography from 'material-ui/Typography';

import Workers from './Cluster/Workers.jsx'
import Topics from './Cluster/Topics.jsx'

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

class Cluster extends Component {
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
                        <Tab label="Workers" icon={<WorkerIcon />} />
                        <Tab label="Topics" icon={<TopicIcon />} />
                        <Tab label="Consumers" icon={<ConsumerIcon />} />
                    </Tabs>
                </AppBar>
                {tabId === 0 && <Workers />}
                {tabId === 1 && <Topics />}
                {tabId === 2 && <TabContainer>Help</TabContainer>}
            </div>
        )
    }
}

Cluster.propTypes = {
  classes: PropTypes.object.isRequired,
};

export default withTheme()(withStyles(styles)(Cluster));
