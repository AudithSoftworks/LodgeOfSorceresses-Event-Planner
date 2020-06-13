import * as api from "../vendor/api/admin";

export const TYPE_PUT_CHARACTER_SEND = "PUT_CHARACTER_SEND";

export const TYPE_PUT_CHARACTER_SUCCESS = "PUT_CHARACTER_SUCCESS";

export const TYPE_PUT_CHARACTER_FAILURE = "PUT_CHARACTER_FAILURE";

const RESPONSE_MESSAGE_SUCCESS = "Character reranked.";

const putCharacterSendAction = (characterId, data) => ({
    type: TYPE_PUT_CHARACTER_SEND,
    characterId,
    data,
});

const putCharacterSuccessAction = (characterId, response, message) => ({
    type: TYPE_PUT_CHARACTER_SUCCESS,
    characterId,
    response,
    message,
});

const putCharacterFailureAction = error => ({
    type: TYPE_PUT_CHARACTER_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const putCharacterAction = (characterId, data) => (dispatch, getState) => {
    dispatch(putCharacterSendAction(characterId, data));
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .putCharacter(axiosCancelTokenSource, characterId, data, dispatch)
        .then(response => {
            dispatch(putCharacterSuccessAction(characterId, response, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(putCharacterFailureAction(error));
        });
};

export default putCharacterAction;
