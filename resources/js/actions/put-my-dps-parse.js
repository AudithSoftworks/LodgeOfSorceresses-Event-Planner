import * as api from '../vendor/api/auth';
import getMyDpsParseAction from './get-my-dps-parse';

export const TYPE_PUT_MY_DPS_PARSE_SEND = 'PUT_MY_DPS_PARSE_SEND';

export const TYPE_PUT_MY_DPS_PARSE_FAILURE = 'PUT_MY_DPS_PARSE_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Parse updated.';

const putMyDpsParseSendAction = (characterId, parseId, data) => ({
    type: TYPE_PUT_MY_DPS_PARSE_SEND,
    characterId,
    parseId,
    data,
});

const putMyDpsParseFailureAction = error => ({
    type: TYPE_PUT_MY_DPS_PARSE_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const putMyDpsParseAction = (characterId, parseId, data) => (dispatch, getState) => {
    dispatch(putMyDpsParseSendAction(characterId, parseId, data));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .putMyDpsParse(axiosCancelTokenSource, characterId, parseId, data, dispatch)
        .then(() => {
            dispatch(getMyDpsParseAction(characterId, parseId, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(putMyDpsParseFailureAction(error));
        });
};

export default putMyDpsParseAction;
