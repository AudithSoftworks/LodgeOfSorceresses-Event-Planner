import * as api from '../vendor/api/auth';

export const TYPE_GET_USER_SEND = 'GET_USER_SEND';

export const TYPE_GET_USER_SUCCESS = 'GET_USER_SUCCESS';

export const TYPE_GET_USER_FAILURE = 'GET_USER_FAILURE';

const getUserSendAction = () => ({
    type: TYPE_GET_USER_SEND,
});

const getUserSuccessAction = response => ({
    type: TYPE_GET_USER_SUCCESS,
    response: response,
});

const getUserFailureAction = error => {
    return {
        type: TYPE_GET_USER_FAILURE,
        message: error.response.data.message || error.response.statusText || error.message,
    };
};

const getUserAction = () => (dispatch, getState) => {
    dispatch(getUserSendAction());
    let axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getUser(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(getUserSuccessAction(response));
        })
        .catch(error => {
            dispatch(getUserFailureAction(error));
        });
};

export default getUserAction;
