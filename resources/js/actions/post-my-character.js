import * as api from '../vendor/api';
import getMyCharacterAction from './get-my-character';

export const TYPE_POST_MY_CHARACTER_SEND = 'POST_MY_CHARACTER_SEND';

export const TYPE_POST_MY_CHARACTER_FAILURE = 'POST_MY_CHARACTER_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Character created.';

const postMyCharacterSendAction = data => ({
    type: TYPE_POST_MY_CHARACTER_SEND,
    data,
});

const postMyCharacterFailureAction = error => {
    return {
        type: TYPE_POST_MY_CHARACTER_FAILURE,
        message: error.response.data.message || error.response.statusText || error.message,
    };
};

const postMyCharacterAction = data => (dispatch, getState) => {
    dispatch(postMyCharacterSendAction(data));
    let axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .postMyCharacter(axiosCancelTokenSource, data, dispatch)
        .then(response => {
            dispatch(getMyCharacterAction(response.data['lastInsertId'], RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(postMyCharacterFailureAction(error));
        });
};

export default postMyCharacterAction;
