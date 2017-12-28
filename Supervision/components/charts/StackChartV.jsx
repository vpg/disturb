import React, { Component } from 'react';

import {
    VictoryStack,
    VictoryArea,
    VictoryChart,
    VictoryAxis,
    VictoryTheme,
    VictoryTooltip,
    VictoryLabel,
    VictoryLegend
    } from 'victory';


export default class StackChartV extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
        };
    }

    render() {
        const { maxWidth, width, height, xLabel, yLabel, data, title } = this.props;
        

        const areaList = [];
        Object.keys(data).forEach( key => {
            areaList.push(
                <VictoryArea key={key}
                    data={data[key]}
                    labels={(d) => `${d.x} : ${d.y}`}
                />
            );
        });



        return (
            <VictoryChart
                style={{ parent: { maxWidth: maxWidth } }}
                width={width}
                height={height}
            >
                <VictoryLegend x={125} y={50}
                    title="Legend"
                    centerTitle
                    orientation="horizontal"
                    gutter={20}
                    style={{ border: { stroke: "black" }, title: {fontSize: 20 } }}
                    data={[
                        { name: "Started", symbol: { fill: "tomato", type: "star" } },
                        { name: "Failed", symbol: { fill: "orange" } },
                        { name: "Sucess", symbol: { fill: "gold" } }
                    ]}
                />
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
                <VictoryStack
                    animate={{
                        duration: 2000,
                        onLoad: { duration: 1000 }
                    }}
                >
                    {areaList}
                </VictoryStack>
            </VictoryChart>
        );
    }
}

