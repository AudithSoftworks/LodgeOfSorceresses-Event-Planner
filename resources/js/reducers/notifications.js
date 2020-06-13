import React from "react";
import v4 from "react-uuid";
import * as actions from "../actions/notifications";

const notificationsReducer = (state = [], action) => {
    let listOfNotifications = [...state];
    const { type, key, message, errors, options, uuidToRemove } = action;
    listOfNotifications = listOfNotifications.filter(n => n.persist === true);
    if (type === actions.TYPE_TRIGGER_ERROR || type.indexOf("_FAILURE") !== -1) {
        if (errors && typeof errors === "object" && Object.keys(errors).length) {
            Object.values(errors).forEach(error => {
                const notificationProps = { persist: true };
                notificationProps.message = error;
                notificationProps.key = key || v4();
                notificationProps.type = "danger";
                listOfNotifications.push({ ...notificationProps, options });
            });
        } else if (message) {
            const notificationProps = { persist: true };
            notificationProps.message = message;
            notificationProps.key = key || v4();
            notificationProps.type = "danger";
            listOfNotifications.push({ ...notificationProps, options });
        }
    } else if (type === actions.TYPE_TRIGGER_WARNING) {
        if (message) {
            const notificationProps = { persist: true };
            notificationProps.message = message;
            notificationProps.key = key || v4();
            notificationProps.type = "warning";
            listOfNotifications.push({ ...notificationProps, options });
        }
    } else if (type === actions.TYPE_TRIGGER_INFO) {
        if (message) {
            const notificationProps = { persist: true };
            notificationProps.message = message;
            notificationProps.key = key || v4();
            notificationProps.type = "info";
            listOfNotifications.push({ ...notificationProps, options });
        }
    } else if (type === actions.TYPE_TRIGGER_SUCCESS || type.indexOf("_SUCCESS") !== -1) {
        if (message && message.length) {
            const notificationProps = { persist: true };
            notificationProps.message = message;
            notificationProps.key = key || v4();
            notificationProps.type = "success";
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
