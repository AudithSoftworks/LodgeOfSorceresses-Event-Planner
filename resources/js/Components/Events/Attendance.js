import(/* webpackPrefetch: true, webpackChunkName: "calendar-scss" */ "../../../sass/_attendance.scss");

import { Markup } from "interweave";
import moment from "moment";
import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { transformAnchors } from "../../helpers";
import { attendance } from "../../vendor/data";

class BaseView extends PureComponent {
    noEvent = () => [
        <tr key={"event-none"}>
            <td>No attendance records</td>
        </tr>,
    ];

    getEventsForGivenWeek = date => {
        const { events } = this.props;

        return events.filter(event => moment(event["created_at"]).isSame(date, "week"));
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
        const startDate = start && start instanceof moment ? moment(start).startOf("week") : moment().startOf("month");
        const endDate = end && end instanceof moment ? moment(end).endOf("week") : moment().endOf("month");

        const daysRendered = [];
        for (let date = startDate.clone(), weekOfYear = date.isoWeek(), colorHue = 360 * Math.random(); date.isSameOrBefore(endDate, "second"); date = date.clone().add(1, "weeks")) {
            if (!date.isSame(startDate, "second") && date.isoWeek() !== weekOfYear) {
                colorHue = 360 * Math.random();
                weekOfYear = date.isoWeek();
            }

            const eventsRendered = [];
            this.getEventsForGivenWeek(date).forEach(event => {
                const galleryImagesRendered = event.gallery_images.map(image => (<li><a href={image.large} target='_blank'><img alt='' src={image.small} /></a></li>));
                eventsRendered.push(
                    <tr key={"event-" + event["id"]}>
                        <td>{moment(event["created_at"]).format("MMM Do, HH:mm")}</td>
                        <td>
                            <Markup content={event["text_for_planner"]} noWrap={true} transform={transformAnchors} key="content" /> -- <i>{event.created_by.name}</i>
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
                            title="Each week colored differently"
                            data-count={eventsRendered.length + " attendance(s)"}
                            data-current-week={date.isSame(moment(), 'week') ? 'true' : 'false'}>
                            {date.format("[Week #]WW[]")}
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
