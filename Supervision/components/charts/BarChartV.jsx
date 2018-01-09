import React, { Component } from 'react';

import { VictoryBar, VictoryChart, VictoryAxis,VictoryTooltip} from 'victory';

export default class BarChartV extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        const { maxWidth, width, height, xLabel, yLabel, data } = this.props;
        const sum = data.length ? data.reduce((a, b) => { return a + b.y; }, 0) : 0;
        const avg = sum / data.length;
        const avgCeil1 = avg * 1.6;
        const avgCeil2 = avg * 1.2;
        console.log('avg',  avgCeil1,  avgCeil2);
        return (
                <VictoryChart
                    domainPadding={20}
                    style={{ parent: { maxWidth: maxWidth } }}
                    width={width} height={height}
                >
                    <VictoryAxis
                        label={yLabel}
                        style={{
                            axis: {stroke: "#494c50"},
                            axisLabel: {padding: 30, fill: "#494c50"},
                            grid: {
                                    fill: "#494c50",
                                            stroke: "none",
                                                    pointerEvents: "visible"
                                                          },
                            tickLabels: {fontSize: 15, padding: 5, fill:"#494c50"}
                        }}
                    />
                    <VictoryAxis
                        dependentAxis
                        label="Time (ms)"
                        tickFormat={(x) => (`${x}s`)}
                        style={{
                            axis: {stroke: "#494c50"},
                            axisLabel: {padding: 50, fill: "#494c50"},
                            tickLabels: {fontSize: 15, padding: 5, fill:"#494c50"}
                        }}
                    />
                    <VictoryBar
                        style={{
                            data: {
                                fill: (d) => {
                                   return  parseFloat(d.y) >= avgCeil1 ?
                                        "#c43a31" :
                                        parseFloat(d.y) >= avgCeil2 ?
                                        "#c3793d" :
                                        "#087b1a"
                                }
                            }
                        }}
                        animate={{
                          duration: 2000,
                            onLoad: { duration: 1000 }
                            }}
                        labels={(d) => `${d.x} : ${d.y}s`}
                        labelComponent={<VictoryTooltip/>}
                        data={data}
                    />
                </VictoryChart>
        );
    }
}

