import(/* webpackPrefetch: true, webpackChunkName: "calendar-scss" */ "../../../sass/_attendance.scss");

import { Markup } from "interweave";
import moment from "moment";
import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { Link } from "react-router-dom";
import v4 from "react-uuid";
import { transformAnchors } from "../../helpers";
import { attendance } from "../../vendor/data";
import Loading from "../Loading";
import Notification from "../Notification";

class BaseView extends PureComponent {
    noEvent = () => [
        <table
            key={"week-table-empty"}
            className="attendances list-view col-md-24">
            <caption
                data-current-week='true'
                data-count='0 attendance(s)'>
                {moment().format("[Weeks #]WW[]") + ' through ' + moment().subtract(3, 'weeks').format("[#]WW[]")}
            </caption>
            <tbody>
                <tr key={"event-none"}>
                    <td>No attendance records found for the past 3 weeks.</td>
                </tr>
            </tbody>
        </table>
    ];

    getEventsOfTheSameWeekAsGivenWeek = date => {
        const { events } = this.props;

        return events.filter(event => moment(event["created_at"]).isSame(date, "isoWeek"));
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

        const startDate = start !== null && start instanceof moment ? moment(start) : moment();
        startDate.startOf("isoWeek");
        const endDate = end !== null && end instanceof moment ? moment(end) : moment();
        endDate.endOf("isoWeek");

        const daysRendered = [];
        for (
            let date = moment(endDate), weekOfYear = date.isoWeek();
            date.isSameOrAfter(startDate, "second");
            date = moment(date).subtract(1, "weeks")
        ) {
            if (!date.isSame(startDate, "second") && date.isoWeek() !== weekOfYear) {
                weekOfYear = date.isoWeek();
            }

            const eventsRendered = [];
            this.getEventsOfTheSameWeekAsGivenWeek(date).forEach(event => {
                const galleryImagesRendered = event.gallery_images.map(image => (<li key={v4()}><a href={image.large} target='_blank'><img alt='' src={image.small} /></a></li>));
                const createdByLink = event.created_by ? (
                    <>-- <i><Link to={'/users/' + event.created_by.id}>{event.created_by.name}</Link></i></>
                ) : null;
                eventsRendered.push(
                    <tr key={"event-" + event["id"]}>
                        <td>{moment(event["created_at"]).format("MMM Do, HH:mm")}</td>
                        <td>
                            <Markup content={event["text_for_planner"]} noWrap={true} transform={transformAnchors} key="content" />
                            {createdByLink}
                        </td>
                        <td>
                            <ul className='gallery-images'>
                                {galleryImagesRendered}
                            </ul>
                        </td>
                    </tr>
                );
            });

            if (eventsRendered.length) {
                daysRendered.push(
                    <table
                        key={"week-table-" + date.format("WW")}
                        className="attendances list-view col-md-24">
                        <caption
                            data-count={eventsRendered.length + " attendance(s)"}
                            data-current-week={date.isSame(moment(), 'isoWeek') ? 'true' : 'false'}>
                            {date.format("[Week #]WW[]")}
                        </caption>
                        <tbody>{eventsRendered}</tbody>
                    </table>
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
