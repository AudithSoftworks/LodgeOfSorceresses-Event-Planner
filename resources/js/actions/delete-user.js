import * as api from "../vendor/api/auth";

export const TYPE_DELETE_USER_SEND = "DELETE_USER_SEND";

export const TYPE_DELETE_USER_SUCCESS = "DELETE_USER_SUCCESS";

export const TYPE_DELETE_USER_FAILURE = "DELETE_USER_FAILURE";

const RESPONSE_MESSAGE_SUCCESS = "Account deleted.";

const deleteUserSendAction = () => ({
    type: TYPE_DELETE_USER_SEND,
});

const deleteUserSuccessAction = (response, message, teamId) => ({
    type: TYPE_DELETE_USER_SUCCESS,
    response,
    message,
    teamId,
});

const deleteUserFailureAction = error => {
    return {
        type: TYPE_DELETE_USER_FAILURE,
        message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    };
};

const deleteUserAction = () => (dispatch, getState) => {
    dispatch(deleteUserSendAction());
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .deleteUser(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(deleteUserSuccessAction(response, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(deleteUserFailureAction(error));
        });
};

export default deleteUserAction;
