import React, { Component } from 'react';

import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import {withTheme, withStyles} from 'material-ui/styles'
import Button from 'material-ui/Button';
import IconButton from 'material-ui/IconButton';
import Paper from 'material-ui/Paper';
import Typography from 'material-ui/Typography';
import RefreshIcon from 'material-ui-icons/Refresh';


import StepExectimeBarChart from './charts/StepExectimeBarChart.jsx';
import * as actions from '../actions/workflows.js';


const styles = theme => ({
    button: {
        margin: theme.spacing.unit,
    },
    input: {
        display: 'none',
    },
    paper: {
        margin: '8px',
        width: '800px',
        height: '400px'
    }
});

class Workflows extends Component {
    constructor(props) {
        super(props);
        this.state = {
            stepsExectimeData: props.stepsExectimeData || [],
            stepsPendingtimeData: props.stepsPendingtimeData || []
        }
    }
    componentWillReceiveProps(props) {
        console.log('componentWillReceiveProp', props);
        this.setState({
            stepsExectimeData: props.stepsExectimeData,
            stepsPendingtimeData: props.stepsPendingtimeData
        })
    }
    render() {
        const { classes } = this.props;
        console.log(this.state)
        const b = <StepExectimeBarChart width="800" height="300" xLabel="Steps" yLabel="Time" data={this.state.stepsExectimeData} />
        const pendingTimeChart = <StepExectimeBarChart width="800" height="300" xLabel="Steps" yLabel="Time" data={this.state.stepsPendingtimeData} />
        return (
            <div>
                <Typography type="headline" component="h1">
                    Workflows
                </Typography>
                <IconButton className={classes.button} aria-label="Refresh"
                    onClick={this.props.fetchStepsExecTime}
                >
                    <RefreshIcon />
                </IconButton>
                <Button raised color="accent" className={classes.button}
                    onClick={this.props.fetchWorkflowById}
                >
                    Fetch Workflow
                </Button>
                <Paper className={classes.paper} elevation={4}>
                    <Typography type="headline" component="h3">
                        Steps avg execution time
                    </Typography>
                    {b}
                </Paper>
                <Paper className={classes.paper} elevation={4}>
                    <Typography type="headline" component="h3">
                        Steps avg Waiting time
                    </Typography>
                    {pendingTimeChart}
                </Paper>
            </div>
        )
    }
}

const mapStateToProps = (state, ownProps) => {
    console.log('workflows.mapStateToProps', state, ownProps)
    return {
        stepsExectimeData: state.workflow.stats.stepsExectimeData,
        stepsPendingtimeData: state.workflow.stats.stepsPendingtimeData
    }
}

const mapDispatchToProps = (dispatch, ownProps) => {
    console.log('workflows.mapDispatchToProps', dispatch, ownProps)
    return {
        fetchWorkflowById: bindActionCreators(actions.fetchWorkflowById, dispatch),
        fetchStepsExecTime: bindActionCreators(actions.fetchStepsExecTime, dispatch)
    }
}
export default connect(mapStateToProps, mapDispatchToProps)(withTheme()(withStyles(styles)(Workflows)));
