import * as api from '../vendor/api/auth';
import getUserAction from './get-user';

export const TYPE_PUT_USER_SEND = 'PUT_USER_SEND';

export const TYPE_PUT_USER_FAILURE = 'PUT_USER_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'User updated.';

const putUserSendAction = data => ({
    type: TYPE_PUT_USER_SEND,
    data,
});

const putUserFailureAction = error => ({
    type: TYPE_PUT_USER_FAILURE,
    message: error.response.data.message || error.response.statusText || error.message,
    errors: error.response.data.errors || {},
});

const putUserAction = data => (dispatch, getState) => {
    dispatch(putUserSendAction(data));
    let axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .putUser(axiosCancelTokenSource, data, dispatch)
        .then(response => {
            dispatch(getUserAction(RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(putUserFailureAction(error));
        });
};

export default putUserAction;
