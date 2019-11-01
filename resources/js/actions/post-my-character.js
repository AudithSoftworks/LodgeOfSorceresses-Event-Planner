import * as api from '../vendor/api/auth';
import getCharacterAction from './get-character';

export const TYPE_POST_MY_CHARACTER_SEND = 'POST_MY_CHARACTER_SEND';

export const TYPE_POST_MY_CHARACTER_FAILURE = 'POST_MY_CHARACTER_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Character created.';

const postMyCharacterSendAction = data => ({
    type: TYPE_POST_MY_CHARACTER_SEND,
    data,
});

const postMyCharacterFailureAction = error => ({
    type: TYPE_POST_MY_CHARACTER_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const postMyCharacterAction = data => (dispatch, getState) => {
    dispatch(postMyCharacterSendAction(data));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .postMyCharacter(axiosCancelTokenSource, data, dispatch)
        .then(response => {
            dispatch(getCharacterAction(response.data['lastInsertId'], RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(postMyCharacterFailureAction(error));
        });
};

export default postMyCharacterAction;
