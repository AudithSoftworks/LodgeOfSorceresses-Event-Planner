import * as api from '../vendor/api/auth';

export const TYPE_GET_MY_DPS_PARSE_SEND = 'GET_MY_DPS_PARSE_SEND';

export const TYPE_GET_MY_DPS_PARSE_SUCCESS = 'GET_MY_DPS_PARSE_SUCCESS';

export const TYPE_GET_MY_DPS_PARSE_FAILURE = 'GET_MY_DPS_PARSE_FAILURE';

const getMyDpsParseSendAction = (characterId, parseId) => ({
    type: TYPE_GET_MY_DPS_PARSE_SEND,
    characterId,
    parseId,
});

const getMyDpsParseSuccessAction = (response, characterId, parseId, message) => ({
    type: TYPE_GET_MY_DPS_PARSE_SUCCESS,
    response: response,
    characterId,
    parseId,
    message,
});

const getMyDpsParseFailureAction = error => {
    return {
        type: TYPE_GET_MY_DPS_PARSE_FAILURE,
        message: error.response.data.message || error.response.statusText || error.message,
    };
};

const getMyDpsParseAction = (characterId, parseId, customMessage) => (dispatch, getState) => {
    dispatch(getMyDpsParseSendAction(characterId, parseId));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getMyDpsParse(axiosCancelTokenSource, characterId, parseId, dispatch)
        .then(response => {
            dispatch(getMyDpsParseSuccessAction(response, characterId, parseId, customMessage));
        })
        .catch(error => {
            dispatch(getMyDpsParseFailureAction(error));
        });
};

export default getMyDpsParseAction;
