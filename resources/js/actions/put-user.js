import * as api from '../vendor/api/auth';

export const TYPE_PUT_USER_SEND = 'PUT_USER_SEND';

export const TYPE_PUT_USER_SUCCESS = 'PUT_USER_SUCCESS';

export const TYPE_PUT_USER_FAILURE = 'PUT_USER_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'User updated.';

const putUserSendAction = data => ({
    type: TYPE_PUT_USER_SEND,
    data,
});

const putUserSuccessAction = (response, message) => ({
    type: TYPE_PUT_USER_SUCCESS,
    response,
    message,
});

const putUserFailureAction = error => ({
    type: TYPE_PUT_USER_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const putUserAction = data => (dispatch, getState) => {
    dispatch(putUserSendAction(data));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .putUser(axiosCancelTokenSource, data, dispatch)
        .then(response => {
            dispatch(putUserSuccessAction(response, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(putUserFailureAction(error));
        });
};

export default putUserAction;
