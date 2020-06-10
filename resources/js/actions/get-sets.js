import * as api from "../vendor/api";

export const TYPE_GET_SETS_SEND = "GET_SETS_SEND";

export const TYPE_GET_SETS_SUCCESS = "GET_SETS_SUCCESS";

export const TYPE_GET_SETS_FAILURE = "GET_SETS_FAILURE";

const getSetsSendAction = () => ({
    type: TYPE_GET_SETS_SEND,
});

const getSetsSuccessAction = response => ({
    type: TYPE_GET_SETS_SUCCESS,
    response: response,
});

const getSetsFailureAction = error => {
    return {
        type: TYPE_GET_SETS_FAILURE,
        message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    };
};

const getSetsAction = () => (dispatch, getState) => {
    dispatch(getSetsSendAction());
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .getSets(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(getSetsSuccessAction(response));
        })
        .catch(error => {
            dispatch(getSetsFailureAction(error));
        });
};

export default getSetsAction;
