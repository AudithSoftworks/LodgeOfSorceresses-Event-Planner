import * as api from '../vendor/api/auth';

export const TYPE_GET_USER_SEND = 'GET_USER_SEND';

export const TYPE_GET_USER_SUCCESS = 'GET_USER_SUCCESS';

export const TYPE_GET_USER_FAILURE = 'GET_USER_FAILURE';

const getUserSendAction = () => ({
    type: TYPE_GET_USER_SEND,
});

const getUserSuccessAction = (response, message) => ({
    type: TYPE_GET_USER_SUCCESS,
    response: response,
    message,
});

const getUserFailureAction = error => ({
    type: TYPE_GET_USER_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
});

const getUserAction = customMessage => (dispatch, getState) => {
    dispatch(getUserSendAction());
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getUser(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(getUserSuccessAction(response, customMessage));
        })
        .catch(error => {
            dispatch(getUserFailureAction(error));
        });
};

export default getUserAction;
