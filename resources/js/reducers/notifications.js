import React from 'react';
import v4 from 'react-uuid';
import * as actions from '../actions/notifications';

const notificationsReducer = (state = [], action) => {
    let listOfNotifications = [...state];
    let { type, key, message, options, uuidToRemove } = action;
    listOfNotifications = listOfNotifications.filter(n => n.persist === true);
    const notificationProps = { persist: true };
    if (type === actions.TYPE_TRIGGER_ERROR || type.indexOf('_FAILURE') !== -1) {
        if (message) {
            notificationProps.message = message;
            notificationProps.key = key || v4();
            notificationProps.type = 'danger';
            listOfNotifications.push({ ...notificationProps, options });
        }
    } else if (type === actions.TYPE_TRIGGER_WARNING) {
        if (message) {
            notificationProps.message = message;
            notificationProps.key = key || v4();
            notificationProps.type = 'warning';
            listOfNotifications.push({ ...notificationProps, options });
        }
    } else if (type === actions.TYPE_TRIGGER_INFO) {
        if (message) {
            notificationProps.message = message;
            notificationProps.key = key || v4();
            notificationProps.type = 'info';
            listOfNotifications.push({ ...notificationProps, options });
        }
    } else if (type === actions.TYPE_TRIGGER_SUCCESS || type.indexOf('_SUCCESS') !== -1) {
        if (message) {
            notificationProps.message = message;
            notificationProps.key = key || v4();
            notificationProps.type = 'success';
            listOfNotifications.push({ ...notificationProps, options });
        }
    } else if (type === actions.TYPE_DEQUEUE_NOTIFICATION) {
        const notificationToRemove = listOfNotifications.find(n => n.key === uuidToRemove);
        if (notificationToRemove) {
            notificationToRemove.persist = false;
        }
    }

    return listOfNotifications;
};

export default notificationsReducer;
