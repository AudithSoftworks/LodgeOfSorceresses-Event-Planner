import(/* webpackPrefetch: true, webpackChunkName: "calendar-scss" */ '../../../sass/_calendar.scss');

import { Markup } from "interweave";
import moment from 'moment';
import PropTypes from "prop-types";
import React, { PureComponent } from 'react';
import { transformAnchors } from "../../helpers";
import { attendance } from "../../vendor/data";

class BaseView extends PureComponent {
    noEvent = () => [
        <tr key={'event-none'}>
            <td>No attendance records</td>
        </tr>
    ];

    getEventsForGivenDate = date => {
        const { events } = this.props;
        const eventsForGivenDate = [];
        events.filter(event => {
            const eventDate = moment(event['created_at']);
            if (eventDate.isSame(date, 'day')) {
                eventsForGivenDate.push(event);
            }
        });

        return eventsForGivenDate;
    };
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

class ListView extends BaseView {
    render = () => {
        const { start, end } = this.props;
        const startDate = start && start instanceof moment ? moment(start).startOf('day') : moment().startOf('month');
        const endDate = end && end instanceof moment ? moment(end).endOf('day') : moment().endOf('month');

        const daysRendered = [];
        for (
            let date = startDate.clone(), weekOfYear = date.isoWeek(), colorHue = 360 * Math.random();
            date.isSameOrBefore(endDate, 'second');
            date = date.clone().add(1, 'days')
        ) {
            if (!date.isSame(startDate, 'second') && date.isoWeek() !== weekOfYear) {
                colorHue = 360 * Math.random();
                weekOfYear = date.isoWeek();
            }

            const eventsRendered = [];
            this.getEventsForGivenDate(date).forEach(event => {
                eventsRendered.push(
                    <tr key={'event-' + event['id']}>
                        <td>{moment(event['created_at']).format('HH:mm')}</td>
                        <td><Markup content={event['text']} noWrap={true} transform={transformAnchors} key='content' /></td>
                    </tr>
                )
            })
            if (eventsRendered.length) {
                daysRendered.push(
                    <table key={'day-table-' + date.format()}
                           style={{borderColor: "hsla(" + colorHue + ", 50%, 80%, 0.5)"}}
                           className="calendar list-view col-md-24">
                        <caption title='Each week colored differently' style={{
                            backgroundColor: "hsla(" + colorHue + ", 50%, 80%, 0.5)",
                            color: "hsla(" + colorHue + ", 70%, 30%, 1)",
                        }} data-weekday={date.format('[[W]WW[]] dddd')}>{date.format('MMMM Do, YYYY')}</caption>
                        <tbody>{eventsRendered.length ? eventsRendered : this.noEvent()}</tbody>
                    </table>
                );
            }

        }

        return [...daysRendered];
    };
}

export { MonthView, ListView };
