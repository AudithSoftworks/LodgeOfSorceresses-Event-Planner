import * as api from '../vendor/api/auth';

export const TYPE_DELETE_TEAM_SEND = 'DELETE_TEAM_SEND';

export const TYPE_DELETE_TEAM_SUCCESS = 'DELETE_TEAM_SUCCESS';

export const TYPE_DELETE_TEAM_FAILURE = 'DELETE_TEAM_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Team deleted.';

const deleteTeamSendAction = teamId => ({
    type: TYPE_DELETE_TEAM_SEND,
    teamId,
});

const deleteTeamSuccessAction = (response, message, teamId) => ({
    type: TYPE_DELETE_TEAM_SUCCESS,
    response,
    message,
    teamId,
});

const deleteTeamFailureAction = error => {
    return {
        type: TYPE_DELETE_TEAM_FAILURE,
        message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    };
};

const deleteTeamAction = teamId => (dispatch, getState) => {
    dispatch(deleteTeamSendAction(teamId));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .deleteMyCharacter(axiosCancelTokenSource, teamId, dispatch)
        .then(response => {
            dispatch(deleteTeamSuccessAction(response, RESPONSE_MESSAGE_SUCCESS, teamId));
        })
        .catch(error => {
            dispatch(deleteTeamFailureAction(error));
        });
};

export default deleteTeamAction;
