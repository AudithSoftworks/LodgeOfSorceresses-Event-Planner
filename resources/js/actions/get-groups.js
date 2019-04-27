import * as api from '../vendor/api';

export const TYPE_GET_GROUPS_SEND = 'GET_GROUPS_SEND';

export const TYPE_GET_GROUPS_SUCCESS = 'GET_GROUPS_SUCCESS';

export const TYPE_GET_GROUPS_FAILURE = 'GET_GROUPS_FAILURE';

const getGroupsSendAction = () => ({
    type: TYPE_GET_GROUPS_SEND,
});

const getGroupsSuccessAction = response => ({
    type: TYPE_GET_GROUPS_SUCCESS,
    response: response,
});

const getGroupsFailureAction = error => {
    return {
        type: TYPE_GET_GROUPS_FAILURE,
        message: error.response.data.message || error.response.statusText || error.message,
    };
};

const getGroupsAction = () => (dispatch, getState) => {
    dispatch(getGroupsSendAction());
    let axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .getUserGroups(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(getGroupsSuccessAction(response));
        })
        .catch(error => {
            dispatch(getGroupsFailureAction(error));
        });
};

export default getGroupsAction;
