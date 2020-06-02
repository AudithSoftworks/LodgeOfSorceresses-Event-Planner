export const TYPE_DEQUEUE_NOTIFICATION = 'DEQUEUE_NOTIFICATION';

export const TYPE_TRIGGER_ERROR = 'TRIGGER_ERROR';

export const TYPE_TRIGGER_WARNING = 'TRIGGER_WARNING';

export const TYPE_TRIGGER_SUCCESS = 'TRIGGER_SUCCESS';

export const TYPE_TRIGGER_INFO = 'TRIGGER_INFO';

export const errorsAction = (error, options = {}, key = null, persist = false) => {
    return {
        type: TYPE_TRIGGER_ERROR,
        message: (typeof error === 'string' || error instanceof Array) ? error : error.message || error.response.data.message || error.response.statusText,
        options,
        key,
        persist,
    };
};

export const warningsAction = (message, options = {}, key = null, persist = false) => {
    return {
        type: TYPE_TRIGGER_WARNING,
        message,
        options,
        key,
        persist,
    };
};

export const successAction = (message, options = {}, key = null, persist = false) => {
    return {
        type: TYPE_TRIGGER_SUCCESS,
        message,
        options,
        key,
        persist,
    };
};

export const infosAction = (message, options = {}, key = null, persist = false) => {
    return {
        type: TYPE_TRIGGER_INFO,
        message,
        options,
        key,
        persist,
    };
};

export const dequeueAction = uuidToRemove => {
    return {
        type: TYPE_DEQUEUE_NOTIFICATION,
        uuidToRemove,
    };
};
