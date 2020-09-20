import * as api from '../vendor/api/auth';

export const TYPE_FINALIZE_ONBOARDING_SEND = 'FINALIZE_ONBOARDING_SEND';

export const TYPE_FINALIZE_ONBOARDING_SUCCESS = 'FINALIZE_ONBOARDING_SUCCESS';

export const TYPE_FINALIZE_ONBOARDING_FAILURE = 'FINALIZE_ONBOARDING_FAILURE';

const RESPONSE_MESSAGE_SUCCESS = 'Onboarding complete.';

const finalizeOnboardingSendAction = data => ({
    type: TYPE_FINALIZE_ONBOARDING_SEND,
    data,
});

const finalizeOnboardingSuccessAction = (response, message) => ({
    type: TYPE_FINALIZE_ONBOARDING_SUCCESS,
    response,
    message,
});

const finalizeOnboardingFailureAction = error => ({
    type: TYPE_FINALIZE_ONBOARDING_FAILURE,
    message: (error.response ? error.response.data.message || error.response.statusText : null) || error.message,
    errors: error.response.data.errors || {},
});

const finalizeOnboardingAction = data => (dispatch, getState) => {
    dispatch(finalizeOnboardingSendAction(data));
    const axiosCancelTokenSource = getState().getIn(['axiosCancelTokenSource']);
    return api
        .finalizeOnboarding(axiosCancelTokenSource, data, dispatch)
        .then(response => {
            dispatch(finalizeOnboardingSuccessAction(response, RESPONSE_MESSAGE_SUCCESS));
        })
        .catch(error => {
            dispatch(finalizeOnboardingFailureAction(error));
        });
};

export default finalizeOnboardingAction;
