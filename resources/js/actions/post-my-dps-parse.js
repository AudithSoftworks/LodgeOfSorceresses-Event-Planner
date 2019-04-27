import * as api from '../vendor/api';
import getMyDpsParseAction from './get-my-dps-parse';

export const TYPE_POST_MY_DPS_PARSE_SEND = 'POST_MY_DPS_PARSE_SEND';

export const TYPE_POST_MY_DPS_PARSE_FAILURE = 'POST_MY_DPS_PARSE_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Parse submitted.';

const postMyDpsParseSendAction = (characterId, data) => ({
    type: TYPE_POST_MY_DPS_PARSE_SEND,
    characterId,
    data,
});

const postMyDpsParseFailureAction = error => {
    return {
        type: TYPE_POST_MY_DPS_PARSE_FAILURE,
        message: error.response.data.message || error.response.statusText || error.message,
    };
};

const postMyDpsParseAction = (characterId, data) => (dispatch, getState) => {
    dispatch(postMyDpsParseSendAction(characterId, data));
    let axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .postMyDpsParse(axiosCancelTokenSource, characterId, data, dispatch)
        .then(response => {
            dispatch(getMyDpsParseAction(characterId, response.data['lastInsertId'], RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(postMyDpsParseFailureAction(error));
        });
};

export default postMyDpsParseAction;
