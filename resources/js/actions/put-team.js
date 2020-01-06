import * as api from '../vendor/api';

export const TYPE_PUT_TEAM_SEND = 'PUT_TEAM_SEND';

export const TYPE_PUT_TEAM_SUCCESS = 'PUT_TEAM_SUCCESS';

export const TYPE_PUT_TEAM_FAILURE = 'PUT_TEAM_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Character updated.';

const putTeamSendAction = (characterId, data) => ({
    type: TYPE_PUT_TEAM_SEND,
    characterId,
    data,
});

const putTeamSuccessAction = (teamId, response) => ({
    type: TYPE_PUT_TEAM_SUCCESS,
    teamId,
    response,
});

const putTeamFailureAction = error => ({
    type: TYPE_PUT_TEAM_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const putTeamAction = (teamId, data) => (dispatch, getState) => {
    dispatch(putTeamSendAction(teamId, data));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .putTeam(axiosCancelTokenSource, teamId, data, dispatch)
        .then(response => {
            dispatch(putTeamSuccessAction(teamId, response, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(putTeamFailureAction(error));
        });
};

export default putTeamAction;
