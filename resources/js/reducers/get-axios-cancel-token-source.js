import axios from '../vendor/axios';

const getAxiosCancelTokenSourceReducer = (state = null, action) => {
    if (action.type.indexOf('_SEND') !== -1) {
        return axios.CancelToken.source();
    } else if (action.type.indexOf('RECEIVE_') !== -1 && state !== null) {
        return null;
    }

    return state;
};

export default getAxiosCancelTokenSourceReducer;
