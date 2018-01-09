import React, { Component } from 'react';

import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import {withTheme, withStyles} from 'material-ui/styles'
import AppBar from 'material-ui/AppBar';
import Toolbar from 'material-ui/Toolbar';
import Button from 'material-ui/Button';
import IconButton from 'material-ui/IconButton';
import Paper from 'material-ui/Paper';
import Typography from 'material-ui/Typography';
import RefreshIcon from 'material-ui-icons/Refresh';
import DateRangeIcon from 'material-ui-icons/DateRange';

import Dialog, {
  DialogActions,
  DialogContent,
  DialogContentText,
  DialogTitle,
} from 'material-ui/Dialog';
import Slide from 'material-ui/transitions/Slide';


import { DateRange } from 'react-date-range';

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
    },
    dateRangeModal: {
        width: 400,
        height: 200,
        border: '1px solid #e5e5e5',
        backgroundColor: '#fff',
        boxShadow: '0 5px 15px rgba(0, 0, 0, .5)',
    },
    menuBar:{
        marginBottom: "10px"
    }
});

class Workflows extends Component {
    constructor(props) {
        super(props);
        this.state = {
            stepsExectimeData: props.stepsExectimeData || [],
            stepsPendingtimeData: props.stepsPendingtimeData || [],
            anchorEl: null,
            open: false
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

    handleSelect = dateHash => {
        this.props.fetchStats(dateHash)
        this.closeDateRange()
    }

    openDateRange = event => {
        this.setState(
            {
                open: true,
                anchorEl: event.currentTarget
            }
        );
    }
    closeDateRange = () => {
        this.setState(
            {
                open: false,
                anchorEl: null
            }
        );
    }
    Transition(props) {
      return <Slide direction="up" {...props} />;
    }


    render() {
        const { classes } = this.props;
        const { anchorEl } = this.state;
        const open = Boolean(anchorEl);
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
                <AppBar position="static" color="default" className={classes.menuBar}>
                    <Toolbar>
                        <IconButton
                            className={classes.button}
                            aria-label="Refresh"
                            onClick={this.props.fetchStats}
                        >
                            <RefreshIcon />
                        </IconButton>
                        <Typography type="title" color="inherit" className={classes.flex} />
                        <IconButton
                            className={classes.button}
                            aria-label="Filtre date range"
                            onClick={this.openDateRange}
                        >
                            <DateRangeIcon />
                        </IconButton>
                    </Toolbar>
                </AppBar>
                <Dialog
                    open={this.state.open}
                    transition={this.Transition}
                    keepMounted
                    onClose={this.closeDateRange}
                    aria-labelledby="alert-dialog-slide-title"
                    aria-describedby="alert-dialog-slide-description"
                >
                    <DialogTitle id="alert-dialog-slide-title">
                        {"Select the date range"}
                    </DialogTitle>
                    <DialogContent>
                        <DateRange
                            onInit={this.handleSelect}
                            onChange={this.handleSelect}
                            twoStepChange={true}
                            calendars={1}
                            lang="fr"
                        />
                    </DialogContent>
                </Dialog>
                <div style={{ display: "flex", flexWrap: "wrap", height:"400px", justifyContent: "center" }}>
                    <LineChartV
                        title="Nb Workflows"
                        maxWidth="100%"
                        width={800}
                        height={400}
                        xLabel="Nb"
                        yLabel="Date"
                        data={(this.state.wfHistoData ? this.state.wfHistoData.wfCountHashList || [] : [])}
                    />
                    <BarChartV
                        title="Steps avg Waiting time"
                        maxWidth="100%"
                        width={400}
                        height={200}
                        xLabel="Steps"
                        yLabel="Time"
                        data={this.state.stepsPendingtimeData}
                    />
                </div>
            </div>
        )
    }
}

const mapStateToProps = (state, ownProps) => {
    console.log('workflows.mapStateToProps', state, ownProps);
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
