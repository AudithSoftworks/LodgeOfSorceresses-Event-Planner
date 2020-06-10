import * as api from "../vendor/api/auth";

export const TYPE_POST_MY_DPS_PARSE_SEND = "POST_MY_DPS_PARSE_SEND";

export const TYPE_POST_MY_DPS_PARSE_SUCCESS = "POST_MY_DPS_PARSE_SUCCESS";

export const TYPE_POST_MY_DPS_PARSE_FAILURE = "POST_MY_DPS_PARSE_FAILURE";

const RESPONSE_MESSAGE_SUCCESS = "Parse submitted.";

const postMyDpsParseSendAction = (characterId, data) => ({
    type: TYPE_POST_MY_DPS_PARSE_SEND,
    characterId,
    data,
});

const postMyDpsParseSuccessAction = (response, message) => ({
    type: TYPE_POST_MY_DPS_PARSE_SUCCESS,
    response,
    message,
});

const postMyDpsParseFailureAction = error => ({
    type: TYPE_POST_MY_DPS_PARSE_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const postMyDpsParseAction = (characterId, data) => (dispatch, getState) => {
    dispatch(postMyDpsParseSendAction(characterId, data));
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .postMyDpsParse(axiosCancelTokenSource, characterId, data, dispatch)
        .then(response => {
            dispatch(postMyDpsParseSuccessAction(response, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(postMyDpsParseFailureAction(error));
        });
};

export default postMyDpsParseAction;
