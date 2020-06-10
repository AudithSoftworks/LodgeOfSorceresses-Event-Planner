import * as api from "../vendor/api";

export const TYPE_GET_TEAMS_SEND = "GET_TEAMS_SEND";

export const TYPE_GET_TEAMS_SUCCESS = "GET_TEAMS_SUCCESS";

export const TYPE_GET_TEAMS_FAILURE = "GET_TEAMS_FAILURE";

const getTeamsSendAction = () => ({
    type: TYPE_GET_TEAMS_SEND,
});

const getTeamsSuccessAction = response => ({
    type: TYPE_GET_TEAMS_SUCCESS,
    response,
});

const getTeamsFailureAction = error => ({
    type: TYPE_GET_TEAMS_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
});

const getTeamsAction = () => (dispatch, getState) => {
    dispatch(getTeamsSendAction());
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .getTeams(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(getTeamsSuccessAction(response));
        })
        .catch(error => {
            dispatch(getTeamsFailureAction(error));
        });
};

export default getTeamsAction;
