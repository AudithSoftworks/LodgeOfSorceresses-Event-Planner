import * as api from '../vendor/api';

export const TYPE_GET_CHARACTER_SEND = 'GET_CHARACTER_SEND';

export const TYPE_GET_CHARACTER_SUCCESS = 'GET_CHARACTER_SUCCESS';

export const TYPE_GET_CHARACTER_FAILURE = 'GET_CHARACTER_FAILURE';

const getCharacterSendAction = characterId => ({
    type: TYPE_GET_CHARACTER_SEND,
    characterId,
});

const getCharacterSuccessAction = (response, message) => ({
    type: TYPE_GET_CHARACTER_SUCCESS,
    response,
    message,
});

const getCharacterFailureAction = error => ({
    type: TYPE_GET_CHARACTER_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
});

const getCharacterAction = (characterId, customMessage) => (dispatch, getState) => {
    dispatch(getCharacterSendAction(characterId));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getCharacter(axiosCancelTokenSource, characterId, dispatch)
        .then(response => {
            dispatch(getCharacterSuccessAction(response, customMessage));
        })
        .catch(error => {
            dispatch(getCharacterFailureAction(error));
        });
};

export default getCharacterAction;
