import * as getActions from '../actions/get-skills';

const skillsReducer = (state = null, action) => {
    if (action.type === getActions.TYPE_GET_SKILLS_SUCCESS) {
        let newState = undefined;
        if (action.response.result.length) {
            newState = [];
            Object.keys(action.response.entities.skills).forEach(key => {
                newState.push(action.response.entities.skills[key]);
            });
        } else {
            newState = [];
        }

        return newState;
    }

    return state;
};

export default skillsReducer;
