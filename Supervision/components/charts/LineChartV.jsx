import React, { Component } from 'react';
import PropTypes from 'prop-types'

import Card, { CardHeader, CardMedia, CardContent, CardActions } from 'material-ui/Card';
import {withTheme, withStyles} from 'material-ui/styles'

import {
    VictoryLine,
    VictoryChart,
    VictoryAxis,
    VictoryTheme,
    VictoryTooltip,
    VictoryLabel
    } from 'victory';


const styles = theme => ({
    card: {
        margin: 1
    },
    title: {
        marginBottom: 10,
        fontSize: 14,
        color: theme.palette.text.secondary,
    },
    content: {
        fontSize: 8,
        color: theme.palette.text.secondary,
        flex: "1 0 0px",
        height: 'calc(100% - 38px)'
    },
});

class LineChartV extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
        };
    }

    render() {
        const { maxWidth, width, height, xLabel, yLabel, data, title, classes } = this.props;

        return (
            <Card className={classes.card}
            >
                <CardHeader
                    title={title}
                />
                <CardContent className={classes.content}
                style={{
                    width:width,
                    height:height
                }}
                >
                    <VictoryChart
                style={{ parent : { maxWidth: maxWidth },
                    width:width,
                    height:height
                }}
                    >
                        <VictoryAxis
                            label={yLabel}
                            style={{
                                axis: {stroke: "#494c50"},
                                axisLabel: {padding: 30, fill: "#494c50"},
                                tickLabels: {fontSize: 15, padding: 5, fill:"#494c50"}
                            }}
                        />
                        <VictoryAxis
                            dependentAxis
                            label={xLabel}
                            style={{
                                axis: {stroke: "#494c50"},
                                axisLabel: {padding: 30, fill: "#494c50"},
                                tickLabels: {fontSize: 15, padding: 5, fill:"#494c50"}
                            }}
                        />
                        <VictoryLine
                            style={{
                                data: { stroke: "#c43a31" },
                                parent: { border: "1px solid #ccc"}
                            }}
                            data={data}
                            interpolation="monotoneX"
                            animate={{
                                duration: 2000,
                                onLoad: { duration: 1000 }
                            }}
                        />
                    </VictoryChart>
                </CardContent>
            </Card>
        );
    }
}

LineChartV.propTypes = {
    classes: PropTypes.object.isRequired,
    title: PropTypes.string.isRequired,
}

export default withTheme()(withStyles(styles)(LineChartV));
