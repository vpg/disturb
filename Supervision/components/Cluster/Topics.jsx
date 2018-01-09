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

import TopicList from './Topics/List.jsx';

const styles = theme => ({
    root: {
      width: '100%',
      height: '100%',
    },
    button: {
        marginLeft: -12,
        marginRight: 20
    },
    flex: {
        flex: 1,
    },
    content: {
    },
    formControl: {
        margin: theme.spacing.unit,
    },
    menuBar:{
        marginTop: "10px",
        marginBottom: "10px"
    }
});

class Topics extends Component {
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
        /*

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
         */

        return (
            <div className={classes.root}>
                <TopicList />
            </div>
        )
    }
}

export default withTheme()(withStyles(styles)(Topics));
