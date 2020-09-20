import * as getActions from '../actions/get-sets';

const setsReducer = (state = null, action) => {
    if (action.type === getActions.TYPE_GET_SETS_SUCCESS) {
        let newState = undefined;
        if (action.response.result.length) {
            newState = [];
            Object.keys(action.response.entities.sets).forEach(key => {
                newState.push(action.response.entities.sets[key]);
            });
        } else {
            newState = [];
        }

        return newState;
    }

    return state;
};

export default setsReducer;
