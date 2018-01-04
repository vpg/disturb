import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import {withTheme, withStyles} from 'material-ui/styles'

import AppBar from 'material-ui/AppBar';
import Toolbar from 'material-ui/Toolbar';
import Typography from 'material-ui/Typography';
import IconButton from 'material-ui/IconButton';
import RefreshIcon from 'material-ui-icons/Refresh';
import MenuIcon from 'material-ui-icons/Menu';
import FilterIcon from 'material-ui-icons/FilterList';
import Menu, { MenuItem } from 'material-ui/Menu';
import Input, { InputLabel } from 'material-ui/Input';
import { FormControl, FormHelperText } from 'material-ui/Form';
import Switch from 'material-ui/Switch';


import ConsumerGroup from './workers/ConsumerGroup.jsx';
import * as actions from '../actions/workers.js';

const styles = theme => ({
    button: {
        marginLeft: -12,
        marginRight: 20
    },
    flex: {
        flex: 1,
    },
    content: {
        display: "flex",
        flexWrap: "wrap",
        height:"300px",
        justifyContent: "center"
    },
    formControl: {
        margin: theme.spacing.unit,
    },
});

class Workers extends Component {
    constructor(props) {
        super(props);
        this.state = {
            consumerGroupList: props.consumerGroupList || [],
            anchorEl: null,
            filter: {
                text:'',
                stable:true,
                empty:true,
            }
        }
    }
    componentWillReceiveProps(props) {
        console.log('componentWillReceiveProp', props);
        this.setState({
            consumerGroupList: props.consumerGroupList
        })
    }

    handleMenu = event => {
        this.setState({ anchorEl: event.currentTarget });
    }
    handleToggle = filterKey => () => {
        let filter = this.state.filter
        filter[filterKey] = !filter[filterKey]
        this.setState({
            filter
        });
        console.log(this.state)
    }

    handleClose = () => {
        this.setState({ anchorEl: null });
    }
    handleChange = event => {
        let filter = this.state.filter
        filter.text = event.target.value
        this.setState({ filter });
        console.log(this.state)
    }

    render() {
        const { classes } = this.props;
        const { anchorEl } = this.state;
        const open = Boolean(anchorEl);

        return (
            <div>
                <AppBar position="static">
                    <Toolbar>
                        <IconButton className={classes.button} aria-label="Refresh"
                            onClick={this.props.fetchConsumerGroupList}
                        >
                            <RefreshIcon />
                        </IconButton>
                        <FormControl className={classes.formControl}>
                            <InputLabel htmlFor="name-simple">Filter</InputLabel>
                            <Input id="name-simple" value={this.state.name} onChange={this.handleChange} />
                        </FormControl>
                        <Typography type="title" color="inherit" className={classes.flex}>
                        </Typography>
                        <IconButton
                            aria-owns={open ? 'menu-appbar' : null}
                            aria-haspopup="true"
                            onClick={this.handleMenu}
                            color="contrast"
                        >
                            <FilterIcon />
                        </IconButton>
                        <Menu
                            id="menu-appbar"
                            anchorEl={anchorEl}
                            anchorOrigin={{
                                vertical: 'top',
                                horizontal: 'right',
                            }}
                            transformOrigin={{
                                vertical: 'top',
                                horizontal: 'right',
                            }}
                            open={open}
                            onClose={this.handleClose}
                        >
                            <MenuItem onClick={this.handleClose}>Stable
                                <Switch onChange={this.handleToggle('stable')} checked={this.state.filter.stable} />
                            </MenuItem>
                            <MenuItem onClick={this.handleClose}>Empty
                                <Switch onChange={this.handleToggle('empty')} checked={this.state.filter.empty} />
                            </MenuItem>
                        </Menu>
                    </Toolbar>
                </AppBar>
                <div className={classes.content}>
                    {this.state.consumerGroupList
                        .filter( group => {
                            let keepIt = true
                            const filter = this.state.filter
                            console.log(filter)
                            const filterRegex = new RegExp(filter.text, 'i')
                            if (filter.text && !filterRegex.test(group.groupId)) keepIt = false
                            if (!filter.stable && group.state == 'Stable' ) keepIt = false
                            if (!filter.empty && group.state == 'Empty' ) keepIt = false
                            console.log(keepIt)
                            return keepIt
                        })
                        .map( group => {
                        return <ConsumerGroup key={group.groupId} groupName={group.groupId} groupState={group.state} consumerList={group.members}/>
                    })}
                </div>
            </div>
        )
    }
}

const mapStateToProps = (state, ownProps) => {
    console.log('workflows.mapStateToProps', state, ownProps)
    return {
        consumerGroupList: state.workers.consumerGroupList
    }
}

const mapDispatchToProps = (dispatch, ownProps) => {
    console.log('workflows.mapDispatchToProps', dispatch, ownProps)
    return {
        fetchConsumerGroupList: bindActionCreators(actions.fetchConsumerGroupList, dispatch)
    }
}
export default connect(mapStateToProps, mapDispatchToProps)(withTheme()(withStyles(styles)(Workers)));
