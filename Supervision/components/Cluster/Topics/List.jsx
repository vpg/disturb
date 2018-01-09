import React, { Component } from 'react';
import PropTypes from 'prop-types'
import classNames from 'classnames'

import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import {withTheme, withStyles} from 'material-ui/styles'

import Table, {
  TableBody,
  TableCell,
  TableFooter,
  TableHead,
  TablePagination,
  TableRow,
  TableSortLabel,
} from 'material-ui/Table';

import AppBar from 'material-ui/AppBar';
import Toolbar from 'material-ui/Toolbar';
import Typography from 'material-ui/Typography';
import Paper from 'material-ui/Paper';
import Checkbox from 'material-ui/Checkbox';
import IconButton from 'material-ui/IconButton';
import Tooltip from 'material-ui/Tooltip';
import DeleteIcon from 'material-ui-icons/Delete';
import FilterListIcon from 'material-ui-icons/FilterList';
import RefreshIcon from 'material-ui-icons/Refresh';
import Input, { InputLabel } from 'material-ui/Input';
import { FormControl, FormHelperText } from 'material-ui/Form';

import * as actions from '../../../actions/topics.js';

const styles = theme => ({
    root: {
        width: '100%',
        marginTop: "10px",
    },
    highlight: {
        color: theme.palette.secondary.A100,
        backgroundColor: theme.palette.secondary.A700,
    },
    spacer: {
        flex: '1 1 100%',
    },
    actions: {
        color: theme.palette.text.secondary,
    },
    title: {
        flex: '0 0 auto',
    },
    table: {
        minWidth: 800,
    },
    tableWrapper: {
        overflowX: 'auto',
    },
    menuBar:{
        marginBottom: "10px"
    }
});

const columnData = [
  { id: 'name', numeric: false, disablePadding: true, label: 'Topic Name' },
  { id: 'Nb of Partitions', numeric: true, disablePadding: false, label: 'Topic Partitions' },
  { id: 'Info', numeric: true, disablePadding: false, label: 'Topic Info' },
];

class TopicTableHead extends React.Component {
    static propTypes = {
        numSelected: PropTypes.number.isRequired,
        onRequestSort: PropTypes.func.isRequired,
        onSelectAllClick: PropTypes.func.isRequired,
        order: PropTypes.string.isRequired,
        orderBy: PropTypes.string.isRequired,
        rowCount: PropTypes.number.isRequired,
    };

    createSortHandler = property => event => {
        this.props.onRequestSort(event, property);
    };

    render() {
        const { onSelectAllClick, order, orderBy, numSelected, rowCount } = this.props;

        return (
            <TableHead>
                <TableRow>
                    <TableCell padding="checkbox">
                        <Checkbox
                            indeterminate={numSelected > 0 && numSelected < rowCount}
                            checked={numSelected === rowCount}
                            onChange={onSelectAllClick}
                        />
                    </TableCell>
                    {columnData.map(column => {
                        return (
                            <TableCell
                                key={column.id}
                                numeric={column.numeric}
                                padding={column.disablePadding ? 'none' : 'default'}
                                sortDirection={orderBy === column.id ? order : false}
                            >
                                <Tooltip
                                    title="Sort"
                                    placement={column.numeric ? 'bottom-end' : 'bottom-start'}
                                    enterDelay={300}
                                >
                                    <TableSortLabel
                                        active={orderBy === column.id}
                                        direction={order}
                                        onClick={this.createSortHandler(column.id)}
                                    >
                                        {column.label}
                                    </TableSortLabel>
                                </Tooltip>
                            </TableCell>
                        );
                    }, this)}
                </TableRow>
            </TableHead>
        );
    }
}

class List extends Component {
    constructor(props){
        super(props);

        const topicHashList = this.props.topicHashList || []
        //.sort((a, b) => (a.name < b.name ? -1 : 1))
        this.state = {
            order: 'asc',
            orderBy: 'name',
            selected: [],
            topicHashList: topicHashList,
            page: 0,
            rowsPerPage: 10,
            filter: {
                text:'',
                stable:true,
                empty:true,
            }
        };
    }

    componentWillReceiveProps(props) {
        this.setState({
            topicHashList: props.topicHashList
        })
    }

    handleRequestSort = (event, property) => {
        const orderBy = property;
        let order = 'desc';

        if (this.state.orderBy === property && this.state.order === 'desc') {
            order = 'asc';
        }

        const topicHashList =
            order === 'desc'
            ? this.state.topicHashList.sort((a, b) => (b[orderBy] < a[orderBy] ? -1 : 1))
            : this.state.topicHashList.sort((a, b) => (a[orderBy] < b[orderBy] ? -1 : 1));

        this.setState({ topicHashList, order, orderBy });
    };

    handleSelectAllClick = (event, checked) => {
        if (checked) {
            this.setState({ selected: this.state.topicHashList.map(n => n.id) });
            return;
        }
        this.setState({ selected: [] });
    };

    handleClick = (event, id) => {
        const { selected } = this.state;
        const selectedIndex = selected.indexOf(id);
        let newSelected = [];

        if (selectedIndex === -1) {
            newSelected = newSelected.concat(selected, id);
        } else if (selectedIndex === 0) {
            newSelected = newSelected.concat(selected.slice(1));
        } else if (selectedIndex === selected.length - 1) {
            newSelected = newSelected.concat(selected.slice(0, -1));
        } else if (selectedIndex > 0) {
            newSelected = newSelected.concat(
                selected.slice(0, selectedIndex),
                selected.slice(selectedIndex + 1),
            );
        }

        this.setState({ selected: newSelected });
    };

    handleChangePage = (event, page) => {
        this.setState({ page });
    };

    handleChangeRowsPerPage = event => {
        this.setState({ rowsPerPage: event.target.value });
    };

    isSelected = id => this.state.selected.indexOf(id) !== -1;

    render() {
        const { classes, fetchTopicList } = this.props;
        const { topicHashList, order, orderBy, selected, rowsPerPage, page } = this.state;
        const emptyRows = rowsPerPage - Math.min(rowsPerPage, topicHashList.length - page * rowsPerPage);
        const numSelected = selected.length;

        return (
            <Paper className={classes.root}>
                <AppBar position="static" color="default" className={classes.menuBar}>
                    <Toolbar
                        className={classNames({
                            [classes.highlight]: numSelected > 0,
                        })}
                    >
                        <div className={classes.title}>
                            {numSelected > 0 ? (
                                <Typography type="subheading">{numSelected} selected</Typography>
                            ) : (
                                <Typography type="title">Topics</Typography>
                            )}
                        </div>
                        <IconButton className={classes.button} aria-label="Refresh" onClick={fetchTopicList} >
                            <RefreshIcon />
                        </IconButton>
                        <FormControl className={classes.formControl}>
                            <InputLabel htmlFor="name-simple">Filter</InputLabel>
                            <Input id="name-simple" value={this.state.name} onChange={this.handleChange} />
                        </FormControl>
                        <div className={classes.spacer} />
                        <div className={classes.actions}>
                            {numSelected > 0 ? (
                                <Tooltip title="Delete">
                                    <IconButton aria-label="Delete"><DeleteIcon /></IconButton>
                                </Tooltip>
                            ) : (
                                <Tooltip title="Filter list">
                                    <IconButton aria-label="Filter list"><FilterListIcon /></IconButton>
                                </Tooltip>
                            )}
                        </div>
                    </Toolbar>
                </AppBar>
                <div className={classes.tableWrapper}>
                    <Table className={classes.table}>
                        <TopicTableHead
                            numSelected={selected.length}
                            order={order}
                            orderBy={orderBy}
                            onSelectAllClick={this.handleSelectAllClick}
                            onRequestSort={this.handleRequestSort}
                            rowCount={topicHashList.length}
                        />
                        <TableBody>
                            {topicHashList.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map(topic => {
                                const isSelected = this.isSelected(topic.id);
                                return (
                                    <TableRow
                                        hover
                                        onClick={event => this.handleClick(event, topic.id)}
                                        role="checkbox"
                                        aria-checked={isSelected}
                                        tabIndex={-1}
                                        key={topic.name}
                                        selected={isSelected}
                                    >
                                        <TableCell padding="checkbox">
                                            <Checkbox checked={isSelected} />
                                        </TableCell>
                                        <TableCell padding="none">{topic.name}</TableCell>
                                        <TableCell numeric>{topic.partition}</TableCell>
                                        <TableCell numeric>{topic.info}</TableCell>

                                    </TableRow>
                                );
                            })}
                            {emptyRows > 0 && (
                                <TableRow style={{ height: 49 * emptyRows }}>
                                    <TableCell colSpan={6} />
                                </TableRow>
                            )}
                        </TableBody>
                        <TableFooter>
                            <TableRow>
                                <TablePagination
                                    count={topicHashList.length}
                                    rowsPerPage={rowsPerPage}
                                    page={page}
                                    backIconButtonProps={{
                                        'aria-label': 'Previous Page',
                                    }}
                                    nextIconButtonProps={{
                                        'aria-label': 'Next Page',
                                    }}
                                    onChangePage={this.handleChangePage}
                                    onChangeRowsPerPage={this.handleChangeRowsPerPage}
                                />
                            </TableRow>
                        </TableFooter>
                    </Table>
                </div>
            </Paper>
        );
    }
}

List.propTypes = {
    classes: PropTypes.object.isRequired
}

const mapStateToProps = (state, ownProps) => {
    return {
        topicHashList: state.topics.topicHashList
    }
}
const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        fetchTopicList: bindActionCreators(actions.fetchTopicList, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withTheme()(withStyles(styles)(List)));
