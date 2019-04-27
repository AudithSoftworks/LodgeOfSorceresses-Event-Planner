import * as api from '../vendor/api';

export const TYPE_DELETE_MY_DPS_PARSE_SEND = 'DELETE_MY_DPS_PARSE_SEND';

export const TYPE_DELETE_MY_DPS_PARSE_SUCCESS = 'DELETE_MY_DPS_PARSE_SUCCESS';

export const TYPE_DELETE_MY_DPS_PARSE_FAILURE = 'DELETE_MY_DPS_PARSE_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Parse deleted.';

export const deleteMyDpsParseSendAction = (characterId, parseId) => ({
    type: TYPE_DELETE_MY_DPS_PARSE_SEND,
    characterId,
    parseId,
});

export const deleteMyDpsParseSuccessAction = (response, message, characterId, parseId) => ({
    type: TYPE_DELETE_MY_DPS_PARSE_SUCCESS,
    response,
    message,
    characterId,
    parseId,
});

export const deleteMyDpsParseFailureAction = error => ({
    type: TYPE_DELETE_MY_DPS_PARSE_FAILURE,
    message: error.message || error.response.data.message || error.response.statusText,
});

const deleteMyDpsParseAction = (characterId, parseId) => (dispatch, getState) => {
    dispatch(deleteMyDpsParseSendAction(characterId, parseId));
    let axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .deleteMyDpsParse(axiosCancelTokenSource, characterId, parseId, dispatch)
        .then(response => {
            dispatch(deleteMyDpsParseSuccessAction(response, RESPONSE_MESSAGE_SUCCESS, characterId, parseId));
        })
        .catch(error => {
            dispatch(deleteMyDpsParseFailureAction(error));
        });
};

export default deleteMyDpsParseAction;
