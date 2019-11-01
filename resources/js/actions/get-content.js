import * as api from '../vendor/api';

export const TYPE_GET_CONTENT_SEND = 'GET_CONTENT_SEND';

export const TYPE_GET_CONTENT_SUCCESS = 'GET_CONTENT_SUCCESS';

export const TYPE_GET_CONTENT_FAILURE = 'GET_CONTENT_FAILURE';

const getContentSendAction = () => ({
    type: TYPE_GET_CONTENT_SEND,
});

const getContentSuccessAction = response => ({
    type: TYPE_GET_CONTENT_SUCCESS,
    response: response,
});

const getContentFailureAction = error => ({
    type: TYPE_GET_CONTENT_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
});

const getContentAction = () => (dispatch, getState) => {
    dispatch(getContentSendAction());
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getContent(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(getContentSuccessAction(response));
        })
        .catch(error => {
            dispatch(getContentFailureAction(error));
        });
};

export default getContentAction;
