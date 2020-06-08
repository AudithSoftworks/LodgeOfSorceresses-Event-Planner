import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "calendar-scss" */
    '../../../sass/_calendar.scss'
    );

import moment from 'moment';
import PropTypes from "prop-types";
import React, { PureComponent } from 'react';
import { attendance, user } from "../../vendor/data";

class Month extends PureComponent {
    renderHeadings = () => {
        const headings = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const headingsRendered = [];
        for (let i = 0; i < headings.length; i++) {
            headingsRendered.push(<th key={'heading-' + i}>{headings[i]}</th>);
        }

        return headingsRendered;
    };

    render = () => {
        const { start, end } = this.props;
        const startDate = start && start.length ? moment(start) : moment().startOf('month');
        const endDate = end && end.length ? moment(end) : moment().endOf('month');

        let aWeekRendered = [];
        const aMonthRendered = [];
        const startingNumberOfGaps = startDate.isoWeekday() - 1;
        if (startingNumberOfGaps) {
            aWeekRendered.push(<td key="starting-gap" colSpan={startingNumberOfGaps} />);
        }
        for (let date = startDate; date.isBefore(endDate); date.add(1, 'days')) {
            if (date.isoWeekday() === 1 && aWeekRendered.length) {
                aMonthRendered.push(
                    <tr key={'week-row-' + (date.isoWeek() - 1)}>
                        <td key={'week-label-' + (date.isoWeek() - 1)} className="week-number">
                            W{date.isoWeek() - 1}
                        </td>
                        {aWeekRendered}
                    </tr>
                );
                aWeekRendered = [];
            }
            aWeekRendered.push(<td key={'date-' + date.format('DD')} className="days" data-date={date.format('DD')} />);
        }
        const endingNumberOfGaps = 7 - endDate.isoWeekday();
        if (endingNumberOfGaps) {
            aWeekRendered.push(<td key="ending-gap" colSpan={endingNumberOfGaps} />);
        }
        if (aWeekRendered.length) {
            aMonthRendered.push(
                <tr key={'week-row-' + endDate.isoWeek()}>
                    <td key={'week-label-' + endDate.isoWeek()} className="week-number">
                        W{endDate.isoWeek()}
                    </td>
                    {aWeekRendered}
                </tr>
            );
        }

        return (
            <table className="calendar month-view col-lg-24">
                <thead>
                    <tr>{this.renderHeadings()}</tr>
                </thead>
                <tbody>{aMonthRendered}</tbody>
            </table>
        );
    };
}

Month.propTypes = {
    start: PropTypes.string,
    end: PropTypes.string,
    data: PropTypes.arrayOf(attendance),
};

export { Month };
