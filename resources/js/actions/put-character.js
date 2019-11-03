import * as api from '../vendor/api/admin';
import getCharacterAction from './get-character';

export const TYPE_PUT_CHARACTER_SEND = 'PUT_CHARACTER_SEND';

export const TYPE_PUT_CHARACTER_SUCCESS = 'PUT_CHARACTER_SUCCESS';

export const TYPE_PUT_CHARACTER_FAILURE = 'PUT_CHARACTER_FAILURE';

const putCharacterSendAction = (characterId, data) => ({
    type: TYPE_PUT_CHARACTER_SEND,
    characterId,
    data,
});

const putCharacterFailureAction = error => ({
    type: TYPE_PUT_CHARACTER_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const putCharacterAction = (characterId, data) => (dispatch, getState) => {
    dispatch(putCharacterSendAction(characterId, data));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .putCharacter(axiosCancelTokenSource, characterId, data, dispatch)
        .then(response => {
            dispatch(getCharacterAction(characterId, response.data.message));
        })
        .catch(error => {
            dispatch(putCharacterFailureAction(error));
        });
};

export default putCharacterAction;
