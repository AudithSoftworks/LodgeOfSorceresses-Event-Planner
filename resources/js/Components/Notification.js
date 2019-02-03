import { library } from '@fortawesome/fontawesome-svg-core';
import { faCheckCircle, faExclamationCircle, faInfoCircle } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { Component } from 'react';
import ReactNotification from "react-notifications-component";

library.add(faCheckCircle, faInfoCircle, faExclamationCircle);

class Notification extends Component {
    constructor(props) {
        super(props);
        this.notificationDOMRef = React.createRef();
        this.getSnapshotBeforeUpdate = this.getSnapshotBeforeUpdate.bind(this);
        this.add = (item) => {
            if (!this.notificationDOMRef.current) {
                return;
            }
            let icon = 'info-circle';
            if (item.type === 'success') {
                icon = 'check-circle';
            } else if (item.type === 'danger') {
                icon = 'exclamation-circle';
            }
            this.notificationDOMRef.current.addNotification({
                title: "Test",
                message: item.message,
                type: item.type,
                content: (
                    <div className={'notification-custom notification-' + item.type}>
                        <div className="notification-icon">
                            <FontAwesomeIcon icon={icon}/>
                        </div>
                        <div className="notification-content">
                            <p className="notification-message">
                                {item.message}
                            </p>
                        </div>
                    </div>
                ),
                insert: "top",
                container: "top-right",
                animationIn: ["animated", "flash"],
                animationOut: ["animated", "fadeOut"],
                dismiss: {duration: 5000},
                dismissable: {click: true},
            });
        };
    };

    getSnapshotBeforeUpdate = (prevProps, prevState) => {
        if (prevProps.messages !== this.props.messages) {
            return this.props.messages;
        }

        return null;
    };

    componentDidMount = () => {
        this.props.messages.map((item) => this.add(item));
    };

    componentDidUpdate = (prevProps, prevState, snapshot) => {
        if (snapshot && snapshot.length) {
            snapshot.map((item) => this.add(item));
        }
    };

    render = () => {
        return (
            <ReactNotification key='notifications'
                               ref={this.notificationDOMRef}
                               types={[
                                   {
                                       htmlClasses: ["notification-awesome"],
                                       name: "awesome"
                                   }
                               ]}
                               isMobile={true}
            />
        );
    }
}

export default Notification;