import * as api from '../vendor/api/auth';

export const TYPE_GET_MY_CHARACTER_SEND = 'GET_MY_CHARACTER_SEND';

export const TYPE_GET_MY_CHARACTER_SUCCESS = 'GET_MY_CHARACTER_SUCCESS';

export const TYPE_GET_MY_CHARACTER_FAILURE = 'GET_MY_CHARACTER_FAILURE';

const getMyCharacterSendAction = characterId => ({
    type: TYPE_GET_MY_CHARACTER_SEND,
    characterId,
});

const getMyCharacterSuccessAction = (response, characterId, message) => ({
    type: TYPE_GET_MY_CHARACTER_SUCCESS,
    response: response,
    characterId,
    message,
});

const getMyCharacterFailureAction = error => {
    return {
        type: TYPE_GET_MY_CHARACTER_FAILURE,
        message: error.response.data.message || error.response.statusText || error.message,
    };
};

const getMyCharacterAction = (characterId, customMessage) => (dispatch, getState) => {
    dispatch(getMyCharacterSendAction(characterId));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getMyCharacter(axiosCancelTokenSource, characterId, dispatch)
        .then(response => {
            dispatch(getMyCharacterSuccessAction(response, characterId, customMessage));
        })
        .catch(error => {
            dispatch(getMyCharacterFailureAction(error));
        });
};

export default getMyCharacterAction;
