import * as api from "../vendor/api/auth";
import getCharacterAction from "./get-character";

export const TYPE_PUT_MY_CHARACTER_SEND = "PUT_MY_CHARACTER_SEND";

export const TYPE_PUT_MY_CHARACTER_SUCCESS = "PUT_MY_CHARACTER_SUCCESS";

export const TYPE_PUT_MY_CHARACTER_FAILURE = "PUT_MY_CHARACTER_FAILURE";

const RESPONSE_MESSAGE_SUCCESS = "Character updated.";

const putMyCharacterSendAction = (characterId, data) => ({
    type: TYPE_PUT_MY_CHARACTER_SEND,
    characterId,
    data,
});

const putMyCharacterFailureAction = error => ({
    type: TYPE_PUT_MY_CHARACTER_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const putMyCharacterAction = (characterId, data) => (dispatch, getState) => {
    dispatch(putMyCharacterSendAction(characterId, data));
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .putMyCharacter(axiosCancelTokenSource, characterId, data, dispatch)
        .then(() => {
            dispatch(getCharacterAction(characterId, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(putMyCharacterFailureAction(error));
        });
};

export default putMyCharacterAction;
