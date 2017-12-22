import React from 'react'

import { Bar } from '@vx/shape';
import { Group } from '@vx/group';
import { GradientTealBlue } from '@vx/gradient';
import { scaleBand, scaleLinear } from '@vx/scale';
import { AxisLeft, AxisBottom } from '@vx/axis';

import { extent, max } from 'd3-array';

class StepExectimeBarChart extends React.Component {
    constructor(props) {
        super(props);
    }

    round = (value, precision) => {
        var multiplier = Math.pow(10, precision || 0);
        return Math.round(value * multiplier) / multiplier;
    }


    render () {
        const { width, height, xLabel, yLabel, data } = this.props;
        console.log('render graph', data);
        

        const x = d => d.code;
        const y = d => +d.time;

        if (width < 10) return null;

        // bounds
        const xMax = width;
        const yMax = height - 120;

        // scales
        const xScale = scaleBand({
            rangeRound: [0, xMax],
            domain: data.map(x),
            padding: 0.4,
        });
        const yScale = scaleLinear({
            rangeRound: [yMax, 0],
            domain: [0, max(data, y)],
        });


        return (
            <svg width={width} height={height}>
                <GradientTealBlue id="teal" />
                <rect
                    x={0}
                    y={0}
                    width={width}
                    height={height}
                    fill={`url(#teal)`}
                    rx={14}
                />
            <AxisBottom
                scale={xScale}
                top={yMax + 40}
                left={0}
                label={xLabel}
                stroke={'#1b1a1e'}
                tickTextFill={'#1b1a1e'}
            />
            <AxisLeft
                scale={yScale}
                top={40}
                left={30}
                label={yLabel}
                stroke={'#1b1a1e'}
                tickTextFill={'#1b1a1e'}
            />
                <Group left={0} top={40}>
                    {data.map((d, i) => {
                        const barHeight = yMax - yScale(y(d));
                        return (
                            <Group key={`bar-${x(d)}`}>
                                <Bar
                                    width={xScale.bandwidth()}
                                    height={barHeight}
                                    x={xScale(x(d))}
                                    y={yMax - barHeight}
                                    fill="rgba(23, 233, 217, .5)"
                                    data={{ x: x(d), y: y(d) }}
                                    onClick={data => event => {
                                        alert(`clicked: ${JSON.stringify(data)}`)
                                    }}
                                />
                            </Group>
                        );
                    })}
                </Group>
            </svg>

        );
    }
}
export default StepExectimeBarChart;
