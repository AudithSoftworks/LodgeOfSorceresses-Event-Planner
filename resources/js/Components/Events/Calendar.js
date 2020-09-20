import(/* webpackPrefetch: true, webpackChunkName: "calendar-scss" */ '../../../sass/_calendar.scss');

import { DateTime } from 'luxon';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { attendance } from '../../vendor/data';

class BaseView extends PureComponent {}

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
        const startDate = start && start instanceof DateTime ? start : DateTime.local().startOf('month');
        const endDate = end && end instanceof DateTime ? end : DateTime.local().endOf('month');

        let aWeekRendered = [];
        const calendarRendered = [];
        const startingNumberOfGaps = startDate.weekday - 1;
        if (startingNumberOfGaps) {
            aWeekRendered.push(<td key="starting-gap" colSpan={startingNumberOfGaps} />);
        }
        for (let date = startDate.startOf('day'); date <= endDate; date = date.plus({ days: 1 })) {
            if (date.weekday === 1 && aWeekRendered.length) {
                calendarRendered.push(
                    <tr key={'week-row-' + date.minus({ weeks: 1 }).toFormat('W')}>
                        <td key={'week-label-' + date.minus({ weeks: 1 }).toFormat('W')} className="week-number">
                            {date.minus({ weeks: 1 }).toFormat('\'W\'W')}
                        </td>
                        {aWeekRendered}
                    </tr>,
                );
                aWeekRendered = [];
            }
            aWeekRendered.push(<td key={'date-' + date.toFormat('d')} className="days" data-date={date.toFormat('d')} />);
        }
        const endingNumberOfGaps = 7 - endDate.weekday;
        if (endingNumberOfGaps) {
            aWeekRendered.push(<td key="ending-gap" data-ending-gap={endingNumberOfGaps} colSpan={endingNumberOfGaps} />);
        }
        if (aWeekRendered.length) {
            calendarRendered.push(
                <tr key={'week-row-' + endDate.weekYear}>
                    <td key={'week-label-' + endDate.weekYear} className="week-number">{endDate.toFormat('\'W\'W')}</td>
                    {aWeekRendered}
                </tr>,
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
