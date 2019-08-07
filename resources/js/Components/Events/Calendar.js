import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "calendar-scss" */
    '../../../sass/_calendar.scss'
    );

import moment from "moment";
import React, { PureComponent } from 'react';

export class Month extends PureComponent {
    renderHeadings = () => {
        const headings = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const headingsRendered = [];
        for (let i = 0; i < headings.length; i++) {
            headingsRendered.push(
                <th>{headings[i]}</th>
            );
        }

        return headingsRendered;
    };

    render = () => {
        const date = new moment();
        date.startOf('month');
        const daysInMonth = date.daysInMonth();

        let aWeekRendered = [];
        const aMonthRendered = [];
        let startingNumberOfGaps = date.isoWeekday() - 1;
        if (startingNumberOfGaps) {
            aWeekRendered.push(
                <td colSpan={startingNumberOfGaps} />
            );
        }
        for (let i = 1; i <= daysInMonth; i++) {
            if (date.isoWeekday() === 1 && aWeekRendered.length) {
                aMonthRendered.push(
                    <tr>
                        <td className='week-number'>W{date.isoWeek() - 1}</td>
                        {aWeekRendered}
                    </tr>
                );
                aWeekRendered = [];
            }
            aWeekRendered.push(
                <td className='days' data-date={date.format('DD')}/>
            );
            if (date.date() !== daysInMonth) {
                date.add(1, 'days');
            }
        }
        let endingNumberOfGaps = 7 - date.isoWeekday();
        if (endingNumberOfGaps) {
            aWeekRendered.push(
                <td colSpan={endingNumberOfGaps} />
            );
        }
        if (aWeekRendered.length) {
            aMonthRendered.push(
                <tr>
                    <td className='week-number'>W{date.isoWeek()}</td>
                    {aWeekRendered}
                </tr>
            );
        }

        return (
            <table className='calendar month-view col-lg-24'>
                <thead>
                    <tr>{this.renderHeadings()}</tr>
                </thead>
                <tbody>{aMonthRendered}</tbody>
            </table>
        );
    };
}
