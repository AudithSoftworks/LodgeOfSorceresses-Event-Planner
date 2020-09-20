import * as api from '../vendor/api';

export const TYPE_POST_TEAM_SEND = 'POST_TEAM_SEND';

export const TYPE_POST_TEAM_SUCCESS = 'POST_TEAM_SUCCESS';

export const TYPE_POST_TEAM_FAILURE = 'POST_TEAM_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Team created.';

const postTeamSendAction = data => ({
    type: TYPE_POST_TEAM_SEND,
    data,
});

const postTeamSuccessAction = (response, message) => ({
    type: TYPE_POST_TEAM_SUCCESS,
    response,
    message,
});

const postTeamFailureAction = error => ({
    type: TYPE_POST_TEAM_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const postTeamAction = data => (dispatch, getState) => {
    dispatch(postTeamSendAction(data));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .postTeam(axiosCancelTokenSource, data, dispatch)
        .then(response => {
            dispatch(postTeamSuccessAction(response, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(postTeamFailureAction(error));
        });
};

export default postTeamAction;
