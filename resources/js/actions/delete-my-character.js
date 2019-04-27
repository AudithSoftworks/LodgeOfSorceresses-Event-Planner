import * as api from '../vendor/api';

export const TYPE_DELETE_MY_CHARACTER_SEND = 'DELETE_MY_CHARACTER_SEND';

export const TYPE_DELETE_MY_CHARACTER_SUCCESS = 'DELETE_MY_CHARACTER_SUCCESS';

export const TYPE_DELETE_MY_CHARACTER_FAILURE = 'DELETE_MY_CHARACTER_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Character deleted.';

const deleteMyCharacterSendAction = characterId => ({
    type: TYPE_DELETE_MY_CHARACTER_SEND,
    characterId,
});

const deleteMyCharacterSuccessAction = (response, message, characterId) => ({
    type: TYPE_DELETE_MY_CHARACTER_SUCCESS,
    response,
    message,
    characterId,
});

const deleteMyCharacterFailureAction = error => {
    return {
        type: TYPE_DELETE_MY_CHARACTER_FAILURE,
        message: error.response.data.message || error.response.statusText || error.message,
    };
};

const deleteMyCharacterAction = characterId => (dispatch, getState) => {
    dispatch(deleteMyCharacterSendAction(characterId));
    let axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .deleteMyCharacter(axiosCancelTokenSource, characterId, dispatch)
        .then(response => {
            dispatch(deleteMyCharacterSuccessAction(response, RESPONSE_MESSAGE_SUCCESS, characterId));
        })
        .catch(error => {
            dispatch(deleteMyCharacterFailureAction(error));
        });
};

export default deleteMyCharacterAction;
