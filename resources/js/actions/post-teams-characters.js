import * as api from "../vendor/api";

export const TYPE_POST_TEAMS_CHARACTERS_SEND = "POST_TEAMS_CHARACTERS_SEND";

export const TYPE_POST_TEAMS_CHARACTERS_SUCCESS = "POST_TEAMS_CHARACTERS_SUCCESS";

export const TYPE_POST_TEAMS_CHARACTERS_FAILURE = "POST_TEAMS_CHARACTERS_FAILURE";

const RESPONSE_MESSAGE_SUCCESS = "Character(s) invited.";

const postTeamsCharactersSendAction = data => ({
    type: TYPE_POST_TEAMS_CHARACTERS_SEND,
    data,
});

const postTeamsCharactersSuccessAction = (response, message) => ({
    type: TYPE_POST_TEAMS_CHARACTERS_SUCCESS,
    response,
    message,
});

const postTeamsCharactersFailureAction = error => ({
    type: TYPE_POST_TEAMS_CHARACTERS_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const postTeamsCharactersAction = (teamId, data) => (dispatch, getState) => {
    dispatch(postTeamsCharactersSendAction(teamId, data));
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .postTeamsCharacters(axiosCancelTokenSource, teamId, data, dispatch)
        .then(response => {
            dispatch(postTeamsCharactersSuccessAction(response, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(postTeamsCharactersFailureAction(error));
        });
};

export default postTeamsCharactersAction;
