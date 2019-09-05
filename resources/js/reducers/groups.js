import * as getActions from '../actions/get-groups';

const groupsReducer = (state = null, action) => {
    if (action.type === getActions.TYPE_GET_GROUPS_SUCCESS) {
        let newState = {};
        if (action.response) {
            newState = { ...action.response };
        }

        return newState;
    }

    return state;
};

export default groupsReducer;
