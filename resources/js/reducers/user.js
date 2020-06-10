import * as getActions from "../actions/get-user";
import * as putActions from "../actions/put-user";
import * as destroyAction from "../actions/delete-user";
import * as onboardingActions from "../actions/finalize-onboarding";

const userReducer = (state = null, action) => {
    if (action.type === getActions.TYPE_GET_USER_SUCCESS || action.type === putActions.TYPE_PUT_USER_SUCCESS || action.type === onboardingActions.TYPE_FINALIZE_ONBOARDING_SUCCESS) {
        let newState = {};
        if (action.response.result) {
            newState = { ...action.response.entities.user[action.response.result] };
        }

        return newState;
    } else if (action.type === destroyAction.TYPE_DELETE_USER_SUCCESS) {
        return null;
    }

    return state;
};

export default userReducer;
