import * as api from '../vendor/api';

export const TYPE_DELETE_TEAMS_CHARACTERS_SEND = 'DELETE_TEAMS_CHARACTERS_SEND';

export const TYPE_DELETE_TEAMS_CHARACTERS_SUCCESS = 'DELETE_TEAMS_CHARACTERS_SUCCESS';

export const TYPE_DELETE_TEAMS_CHARACTERS_FAILURE = 'DELETE_TEAMS_CHARACTERS_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Member(s) removed.';

const deleteTeamsCharactersSendAction = (teamId, characterId) => ({
    type: TYPE_DELETE_TEAMS_CHARACTERS_SEND,
    teamId,
    characterId,
});

const deleteTeamsCharactersSuccessAction = (response, message, teamId, characterId) => ({
    type: TYPE_DELETE_TEAMS_CHARACTERS_SUCCESS,
    response,
    message,
    teamId,
    characterId,
});

const deleteTeamsCharactersFailureAction = error => ({
    type: TYPE_DELETE_TEAMS_CHARACTERS_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const deleteTeamsCharactersAction = (teamId, characterId) => (dispatch, getState) => {
    dispatch(deleteTeamsCharactersSendAction(teamId, characterId));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .deleteTeamsCharacters(axiosCancelTokenSource, teamId, characterId, dispatch)
        .then(response => {
            dispatch(deleteTeamsCharactersSuccessAction(response, RESPONSE_MESSAGE_SUCCESS, teamId, characterId));
        })
        .catch(error => {
            dispatch(deleteTeamsCharactersFailureAction(error));
        });
};

export default deleteTeamsCharactersAction;
