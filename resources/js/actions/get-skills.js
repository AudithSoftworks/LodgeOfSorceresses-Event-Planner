import * as api from "../vendor/api";

export const TYPE_GET_SKILLS_SEND = "GET_SKILLS_SEND";

export const TYPE_GET_SKILLS_SUCCESS = "GET_SKILLS_SUCCESS";

export const TYPE_GET_SKILLS_FAILURE = "GET_SKILLS_FAILURE";

const getSkillsSendAction = () => ({
    type: TYPE_GET_SKILLS_SEND,
});

const getSkillsSuccessAction = response => ({
    type: TYPE_GET_SKILLS_SUCCESS,
    response: response,
});

const getSkillsFailureAction = error => {
    return {
        type: TYPE_GET_SKILLS_FAILURE,
        message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    };
};

const getSkillsAction = () => (dispatch, getState) => {
    dispatch(getSkillsSendAction());
    const axiosCancelTokenSource = getState().getIn(["axiosCancelTokenSource"]);
    return api
        .getSkills(axiosCancelTokenSource, dispatch)
        .then(response => {
            dispatch(getSkillsSuccessAction(response));
        })
        .catch(error => {
            dispatch(getSkillsFailureAction(error));
        });
};

export default getSkillsAction;
