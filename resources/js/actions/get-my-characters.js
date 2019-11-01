import * as api from '../vendor/api/auth';

export const TYPE_GET_MY_CHARACTERS_SEND = 'GET_MY_CHARACTERS_SEND';

export const TYPE_GET_MY_CHARACTERS_SUCCESS = 'GET_MY_CHARACTERS_SUCCESS';

export const TYPE_GET_MY_CHARACTERS_FAILURE = 'GET_MY_CHARACTERS_FAILURE';

const getMyCharactersSendAction = () => ({
    type: TYPE_GET_MY_CHARACTERS_SEND,
});

const getMyCharactersSuccessAction = response => ({
    type: TYPE_GET_MY_CHARACTERS_SUCCESS,
    response: response,
});

const getMyCharactersFailureAction = error => ({
    type: TYPE_GET_MY_CHARACTERS_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
});

const getMyCharactersAction = () => (dispatch, getState) => {
    dispatch(getMyCharactersSendAction());
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getMyCharacters(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(getMyCharactersSuccessAction(response));
        })
        .catch(error => {
            dispatch(getMyCharactersFailureAction(error));
        });
};

export default getMyCharactersAction;
