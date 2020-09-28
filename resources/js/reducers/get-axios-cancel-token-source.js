import axios from '../vendor/axios';

const getAxiosCancelTokenSourceReducer = (state = null, action) => {
    if (action.type.indexOf('_SEND') !== -1) {
        return axios.CancelToken.source();
    }

    return state;
};

export default getAxiosCancelTokenSourceReducer;
