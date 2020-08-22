import * as api from "../vendor/api";

export const TYPE_PUT_TEAMS_CHARACTERS_SEND = "PUT_TEAMS_CHARACTERS_SEND";

export const TYPE_PUT_TEAMS_CHARACTERS_SUCCESS = "PUT_TEAMS_CHARACTERS_SUCCESS";

export const TYPE_PUT_TEAMS_CHARACTERS_FAILURE = "PUT_TEAMS_CHARACTERS_FAILURE";

const RESPONSE_MESSAGE_SUCCESS = "Team membership updated!";

const putTeamsCharactersSendAction = (teamId, characterId, data) => ({
    type: TYPE_PUT_TEAMS_CHARACTERS_SEND,
    teamId,
    characterId,
    data,
});

const putTeamsCharactersSuccessAction = (response, message, teamId, characterId) => ({
    type: TYPE_PUT_TEAMS_CHARACTERS_SUCCESS,
    response,
    message,
    teamId,
    characterId,
});

const putTeamsCharactersFailureAction = error => ({
    type: TYPE_PUT_TEAMS_CHARACTERS_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const putTeamsCharactersAction = (teamId, characterId, data) => (dispatch, getState) => {
    dispatch(putTeamsCharactersSendAction(teamId, characterId, data));
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .putTeamsCharacters(axiosCancelTokenSource, teamId, characterId, data, dispatch)
        .then(response => {
            dispatch(putTeamsCharactersSuccessAction(response, RESPONSE_MESSAGE_SUCCESS, teamId, characterId));
        })
        .catch(error => {
            dispatch(putTeamsCharactersFailureAction(error));
        });
};

export default putTeamsCharactersAction;
