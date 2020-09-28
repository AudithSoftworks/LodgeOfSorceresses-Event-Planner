import { library } from '@fortawesome/fontawesome-svg-core';
import { faCheckCircle, faExclamationCircle, faInfoCircle } from '@fortawesome/pro-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Component } from 'react';
import ReactNotification from 'react-notifications-component';
import { connect } from 'react-redux';
import { dequeueAction } from '../actions/notifications';

library.add(faCheckCircle, faInfoCircle, faExclamationCircle);

class Notification extends Component {
    constructor(props) {
        super(props);
        this.notificationDOMRef = React.createRef();
    }

    addNotification = ({ message, type, options }) => {
        if (!this.notificationDOMRef.current) {
            return;
        }
        let icon = 'info-circle';
        if (type === 'success') {
            icon = 'check-circle';
        } else if (type === 'danger') {
            icon = 'exclamation-circle';
        }
        const { title, insert, container, animationIn, animationOut, dismiss, dismissable, width } = options || {};
        this.notificationDOMRef.current.addNotification({
            title: title || '',
            message,
            type,
            content: (
                <div className={'notification-custom notification-' + type}>
                    <div className="notification-icon">
                        <FontAwesomeIcon icon={icon} />
                    </div>
                    <div className="notification-content">
                        <p className="notification-message">{message}</p>
                    </div>
                </div>
            ),
            insert: insert || 'top',
            container: container || 'top-right',
            animationIn: animationIn || ['animated', 'flash'],
            animationOut: animationOut || ['animated', 'fadeOut'],
            dismiss: dismiss || { duration: 10000 },
            dismissable: dismissable || { click: true },
            width,
        });
    };

    getSnapshotBeforeUpdate = prevProps => {
        return this.props.notifications.filter(n1 => !prevProps.notifications.includes(n1));
    };

    componentDidUpdate = (prevProps, prevState, snapshot) => {
        snapshot.map(notification => {
            this.addNotification(notification);
            if (notification.persist) {
                this.props.dequeueAction(notification.key);
            }
        });
    };

    componentDidMount = () => {
        const { notifications } = this.props;
        notifications.map(notification => {
            this.addNotification(notification);
            if (notification.persist) {
                this.props.dequeueAction(notification.key);
            }
        });
    };

    render = () => {
        return (
            <ReactNotification
                key="notifications"
                ref={this.notificationDOMRef}
                types={[
                    {
                        htmlClasses: ['notification-awesome'],
                        name: 'awesome',
                    },
                ]}
                isMobile={true}
            />
        );
    };
}

Notification.propTypes = {
    notifications: PropTypes.array,

    dispatch: PropTypes.func.isRequired,
    dequeueAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    dequeueAction: uuidToRemove => dispatch(dequeueAction(uuidToRemove)),
});

export default connect(mapStateToProps, mapDispatchToProps)(Notification);
