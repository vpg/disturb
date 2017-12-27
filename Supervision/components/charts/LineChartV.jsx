import React, { Component } from 'react';

import {
    VictoryLine,
    VictoryChart,
    VictoryAxis,
    VictoryTheme,
    VictoryTooltip,
    VictoryLabel
    } from 'victory';


export default class LineChartV extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
        };
    }

    render() {
        const { maxWidth, width, height, xLabel, yLabel, data, title } = this.props;

        return (
                <VictoryChart
                    style={{ parent: { maxWidth: maxWidth } }}
                    width={width}
                    height={height}
                >
                    <VictoryLabel text={title} textAnchor="middle"/>
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
        );
    }
}

