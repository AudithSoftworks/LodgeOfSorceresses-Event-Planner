import * as getActions from '../actions/get-content';

const contentReducer = (state = null, action) => {
    if (action.type === getActions.TYPE_GET_CONTENT_SUCCESS) {
        let newState = undefined;
        if (action.response.result.length) {
            newState = [];
console.log(action.response);
            Object.keys(action.response.entities.content).forEach(key => {
                newState.push(action.response.entities.content[key]);
            });
        } else {
            newState = [];
        }

        return newState;
    }

    return state;
};

export default contentReducer;
