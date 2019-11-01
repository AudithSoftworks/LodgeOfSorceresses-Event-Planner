import { faCalendarPlus } from '@fortawesome/pro-duotone-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import * as Calendar from '../Components/Events/Calendar';
import Notification from '../Components/Notification';
import { authorizeAdmin, renderActionList } from '../helpers';
import { user } from '../vendor/data';

class Events extends PureComponent {
    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    };

    render = () => {
        const { me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }

        const actionList = {
            create: authorizeAdmin(this.props) ? (
                <Link to="/events/create" className="ne-corner" title="Add New Event">
                    <FontAwesomeIcon icon={faCalendarPlus} />
                </Link>
            ) : null,
        };

        return [
            <section className="col-md-24 p-0 mb-4" key="calendar">
                <h2 className="form-title col-md-24" title="Calendar">
                    Calendar
                </h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <Calendar.Month />
            </section>,
            <Notification key="notifications" />,
        ];
    };
}

Events.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    groups: PropTypes.object,
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
    me: state.getIn(['me']),
    groups: state.getIn(['groups']),
    notifications: state.getIn(['notifications']),
});

export default connect(mapStateToProps)(Events);
