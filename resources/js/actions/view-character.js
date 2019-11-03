import * as api from '../vendor/api';

export const TYPE_VIEW_CHARACTER_SEND = 'VIEW_CHARACTER_SEND';

export const TYPE_VIEW_CHARACTER_SUCCESS = 'VIEW_CHARACTER_SUCCESS';

export const TYPE_VIEW_CHARACTER_FAILURE = 'VIEW_CHARACTER_FAILURE';

const viewCharacterInitializeAction = characterId => ({
    type: TYPE_VIEW_CHARACTER_SEND,
    characterId,
});

const viewCharacterSuccessAction = (response, message) => ({
    type: TYPE_VIEW_CHARACTER_SUCCESS,
    response,
    message,
});

const viewCharacterFailureAction = error => ({
    type: TYPE_VIEW_CHARACTER_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
});

const viewCharacterAction = (characterId, customMessage) => (dispatch, getState) => {
    dispatch(viewCharacterInitializeAction(characterId));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getCharacter(axiosCancelTokenSource, characterId, dispatch)
        .then(response => {
            dispatch(viewCharacterSuccessAction(response, customMessage));
        })
        .catch(error => {
            dispatch(viewCharacterFailureAction(error));
        });
};

export default viewCharacterAction;
