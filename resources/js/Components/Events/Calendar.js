import(/* webpackPrefetch: true, webpackChunkName: "calendar-scss" */ '../../../sass/_calendar.scss');

import moment from 'moment';
import PropTypes from "prop-types";
import React, { PureComponent } from 'react';
import { attendance } from "../../vendor/data";

class BaseView extends PureComponent {
}

BaseView.propTypes = {
    start: PropTypes.object,
    end: PropTypes.object,
    events: PropTypes.arrayOf(attendance),
};

class MonthView extends BaseView {
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
        const startDate = start && start instanceof moment ? moment(start) : moment().startOf('month');
        const endDate = end && end instanceof moment ? moment(end) : moment().endOf('month');

        let aWeekRendered = [];
        const calendarRendered = [];
        const startingNumberOfGaps = startDate.isoWeekday() - 1;
        if (startingNumberOfGaps) {
            aWeekRendered.push(<td key="starting-gap" colSpan={startingNumberOfGaps} />);
        }
        for (
            let date = startDate.clone().startOf('day');
            date.isSameOrBefore(endDate, 'second');
            date = date.clone().add(1, 'days')
        ) {
            if (date.isoWeekday() === 1 && aWeekRendered.length) {
                calendarRendered.push(
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
            let test = moment('2020-06-10');
            console.log(endDate, endDate.isoWeekday(), test, test.isoWeekday())
            aWeekRendered.push(<td key="ending-gap" data-ending-gap={endingNumberOfGaps} colSpan={endingNumberOfGaps} />);
        }
        if (aWeekRendered.length) {
            calendarRendered.push(
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
                <tbody>{calendarRendered}</tbody>
            </table>
        );
    };
}

export { MonthView };
