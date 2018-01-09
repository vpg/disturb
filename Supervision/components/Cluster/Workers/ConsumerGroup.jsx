import React, { Component } from 'react';
import PropTypes from 'prop-types'

import {withTheme, withStyles} from 'material-ui/styles'
import Typography from 'material-ui/Typography'
import Card, { CardHeader, CardMedia, CardContent, CardActions } from 'material-ui/Card';
import Avatar from 'material-ui/Avatar';
import IconButton from 'material-ui/IconButton';
import {red, green} from 'material-ui/colors';
import ShareIcon from 'material-ui-icons/Share';
import MoreVertIcon from 'material-ui-icons/MoreVert';

const styles = theme => ({
    card: {
        width: 420,
        margin: 1,
        backgroundColor: "#163f46",
    },
    title: {
        marginBottom: 16,
        fontSize: 14,
        color: theme.palette.text.secondary,
    },
    content: {
        fontSize: 8,
        color: theme.palette.text.secondary,
    },
    Stable: {
        backgroundColor: green[500],
    },
    Empty: {
        backgroundColor: red[500],
    },
    helper: {
        borderLeft: `2px solid ${theme.palette.text.lightDivider}`,
        padding: `${theme.spacing.unit}px ${theme.spacing.unit * 2}px`,
    }
});

class ConsumerGroup extends Component {
    constructor(props){
        super(props);
    }

    render() {
        const { classes, groupName, groupState, consumerList } = this.props;
        const groupClassStatus = classes[groupState]

        return (
            <Card className={classes.card}>
                <CardHeader
                    avatar={
                        <Avatar aria-label="Recipe" className={groupClassStatus}>
                            {groupName.substr(0,3)}
                        </Avatar>
                    }
                    action={
                        <IconButton>
                            <MoreVertIcon />
                        </IconButton>
                    }
                    title={groupName}
                    subheader={`${groupState} with ${consumerList.length} consumer(s)`}
                />
                <CardContent className={classes.content}>
                    <Typography className={classes.title}>Consumers :</Typography>
                    <div className={classes.helper}>
                    <Typography type="caption">
                        {consumerList.map( consumer => {
                            const assignment = consumer.memberAssignment.partitionAssignment;
                            return <span key={consumer.memberId}>
                                {`${consumer.clientHost} : ${assignment[0].topic}#${assignment[0].partitions[0]}`}<br />
                                </span>
                        })}
                    </Typography>
                </div>
                </CardContent>
            </Card>
        )
    }
}

ConsumerGroup.propTypes = {
    classes: PropTypes.object.isRequired,
    groupName: PropTypes.string.isRequired,
    groupState: PropTypes.string.isRequired,
    consumerList: PropTypes.array.isRequired
}

export default withTheme()(withStyles(styles)(ConsumerGroup));
