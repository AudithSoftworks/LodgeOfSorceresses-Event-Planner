import(/* webpackPrefetch: true, webpackChunkName: "calendar-scss" */ "../../../sass/_attendance.scss");

import { Markup } from "interweave";
import moment from "moment";
import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { Link } from "react-router-dom";
import v4 from "react-uuid";
import { transformAnchors } from "../../helpers";
import { attendance } from "../../vendor/data";

class BaseView extends PureComponent {
    noEvent = () => [
        <tr key={"event-none"}>
            <td>No attendance records</td>
        </tr>,
    ];

    getEventsOfTheSameWeekAsGivenWeek = date => {
        const { events } = this.props;

        return events.filter(event => moment(event["created_at"]).isSame(date, "isoWeek"));
    };
}

BaseView.propTypes = {
    start: PropTypes.object,
    end: PropTypes.object,
    events: PropTypes.arrayOf(attendance),
};

class ListView extends BaseView {
    render = () => {
        const { start, end } = this.props;
        const startDate = start && start instanceof moment ? moment(start).startOf("isoWeek") : moment().startOf("month");
        const endDate = end && end instanceof moment ? moment(end).endOf("isoWeek") : moment().endOf("month");

        const daysRendered = [];
        for (
            let date = moment(startDate), weekOfYear = date.isoWeek();
            date.isSameOrBefore(endDate, "second");
            date = moment(date).add(1, "weeks")
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
                            {date.format("[Week #]ww[]")}
                        </caption>
                        <tbody>{eventsRendered.length ? eventsRendered : this.noEvent()}</tbody>
                    </table>
                );
            }
        }

        return [...daysRendered];
    };
}

export { ListView };
