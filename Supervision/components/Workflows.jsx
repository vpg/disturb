import React, { Component } from 'react';

import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import {withTheme, withStyles} from 'material-ui/styles'
import Button from 'material-ui/Button';
import IconButton from 'material-ui/IconButton';
import Paper from 'material-ui/Paper';
import Typography from 'material-ui/Typography';
import RefreshIcon from 'material-ui-icons/Refresh';


import BarChartV from './charts/BarChartV.jsx';
import StackChartV from './charts/StackChartV.jsx';
import LineChartV from './charts/LineChartV.jsx';
import * as actions from '../actions/workflows.js';


const styles = theme => ({
    button: {
        margin: theme.spacing.unit,
    },
    input: {
        display: 'none',
    },
    paperFullWidth: {
        padding: '8px',
        margin: '8px',
        width: '100%',
        height: '400px'
    },
    paper: {
        padding: '8px',
        margin: '8px',
        width: '600px',
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
            stepsPendingtimeData: props.stepsPendingtimeData,
            wfHistoData: props.wfHistoData
        })
    }
    render() {
        const { classes } = this.props;
        const timelineDateList = [];
        for(let i = 10; i > 0; i--) {
            let d = new Date();
            d.setDate(d.getDate() - i);
            timelineDateList.push(
                {
                    x: d,
                    y: 0
                }
            );
        }
        return (
            <div>
                <Typography type="headline" component="h1">
                    Workflows
                </Typography>
                <IconButton className={classes.button} aria-label="Refresh"
                    onClick={this.props.fetchStats}
                >
                    <RefreshIcon />
                </IconButton>
                <div style={{ display: "flex", flexWrap: "wrap", height:"300px" }}>
                    <LineChartV
                        title="Nb Workflows"
                        maxWidth="100%"
                        width={800}
                        height={200}
                        xLabel="Nb"
                        yLabel="Date"
                        data={(this.state.wfHistoData ? this.state.wfHistoData.wfCountHashList || [] : [])}
                    />
                    <BarChartV
                        title="Steps avg Waiting time"
                        maxWidth="50%"
                        width={400}
                        height={200}
                        xLabel="Steps"
                        yLabel="Time"
                        data={this.state.stepsPendingtimeData} />
                    <StackChartV
                        title="Steps avg Waiting time"
                        maxWidth="50%"
                        width={400}
                        height={200}
                        xLabel="Nb"
                        yLabel="Date"
                        data={(this.state.wfHistoData ? this.state.wfHistoData.wfStatusCountHash || [] : [])}
                    />
                </div>
            </div>
        )
    }
}

const mapStateToProps = (state, ownProps) => {
    console.log('workflows.mapStateToProps', state, ownProps)
    return {
        stepsExectimeData: state.workflow.stats.stepsExectimeData,
        stepsPendingtimeData: state.workflow.stats.stepsPendingtimeData,
        wfHistoData: state.workflow.stats.wfHistoData
    }
}

const mapDispatchToProps = (dispatch, ownProps) => {
    console.log('workflows.mapDispatchToProps', dispatch, ownProps)
    return {
        fetchWorkflowById: bindActionCreators(actions.fetchWorkflowById, dispatch),
        fetchStats: bindActionCreators(actions.fetchStats, dispatch)
    }
}
export default connect(mapStateToProps, mapDispatchToProps)(withTheme()(withStyles(styles)(Workflows)));
