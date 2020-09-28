import(/* webpackPrefetch: true, webpackChunkName: "calendar-scss" */ '../../../sass/_attendance.scss');

import { Markup } from 'interweave';
import { DateTime } from 'luxon';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Link } from 'react-router-dom';
import v4 from 'react-uuid';
import { transformAnchors } from '../../helpers';
import { attendance } from '../../vendor/data';
import Loading from '../Loading';
import Notification from '../Notification';

class BaseView extends PureComponent {
    noEvent = () => [
        <table
            key={'week-table-empty'}
            className="attendances list-view col-md-24">
            <caption
                data-current-week='true'
                data-count='0 attendance(s)'>Past 3 weeks
            </caption>
            <tbody>
                <tr key={'event-none'}>
                    <td>No attendance records found for the past 3 weeks.</td>
                </tr>
            </tbody>
        </table>,
    ];

    getEventsOfTheSameWeekAsGivenWeek = date => {
        const { events } = this.props;

        return events.filter(event => DateTime.fromISO(event['created_at']).hasSame(date, 'week'));
    };
}

BaseView.propTypes = {
    start: PropTypes.object, // [null = no attendances found; undefined = attendances to be loaded]
    end: PropTypes.object, // [null = no attendances found; undefined = attendances to be loaded]
    events: PropTypes.arrayOf(attendance),
    heading: PropTypes.string,
};

class ListView extends BaseView {
    render = () => {
        const { heading, start, end } = this.props;

        if (start === undefined || end === undefined) {
            return [<Loading message="Fetching attendance data..." key="loading" />, <Notification key="notifications" />];
        }

        const startDate = start !== null && start instanceof DateTime ? start : DateTime.local();
        startDate.startOf('week');
        const endDate = end !== null && end instanceof DateTime ? end : DateTime.local();
        endDate.endOf('week');

        const daysRendered = [];
        for (
            let date = endDate, weekOfYear = date.weekYear;
            date >= startDate;
            date = date.minus({ weeks: 1 })
        ) {
            if (date.hasSame(startDate, 'second') && date.weekYear !== weekOfYear) {
                weekOfYear = date.weekYear;
            }

            const eventsRendered = [];
            this.getEventsOfTheSameWeekAsGivenWeek(date).forEach(event => {
                const galleryImagesRendered = event.gallery_images.map(image => (
                    <li key={v4()}><a href={image.large} target='_blank' rel='noreferrer'><img alt='' src={image.small} /></a></li>
                ));
                const createdByLink = event.created_by ? (
                    <>-- <i><Link to={'/users/' + event.created_by.id}>{event.created_by.name}</Link></i></>
                ) : null;
                eventsRendered.push(
                    <tr key={'event-' + event['id']}>
                        <td>{DateTime.fromISO(event['created_at']).toFormat('MMM d, HH:mm')}</td>
                        <td>
                            <Markup content={event['text_for_planner']} noWrap={true} transform={transformAnchors} key="content" />
                            {createdByLink}
                        </td>
                        <td>
                            <ul className='gallery-images'>
                                {galleryImagesRendered}
                            </ul>
                        </td>
                    </tr>,
                );
            });

            if (eventsRendered.length) {
                daysRendered.push(
                    <table
                        key={'week-table-' + date.toFormat('WW')}
                        className="attendances list-view col-md-24">
                        <caption
                            data-count={eventsRendered.length + ' attendance(s)'}
                            data-current-week={date.hasSame(DateTime.local(), 'week') ? 'true' : 'false'}>
                            {date.toFormat('\'W\'eek #WW')}
                        </caption>
                        <tbody>{eventsRendered}</tbody>
                    </table>,
                );
            }
        }

        if (daysRendered.length === 0) {
            daysRendered.push(this.noEvent());
        }

        return [
            <h3 className="col-md-24 mt-5" key="heading">{heading || 'Their Attendances'}</h3>,
            ...daysRendered,
        ];
    };
}

export { ListView };
