import * as getActions from '../actions/get-user';

const userReducer = (state = null, action) => {
    if (action.type === getActions.TYPE_GET_USER_SUCCESS) {
        let newState = {};
        if (action.response.result) {
            newState = {...action.response.entities.user[action.response.result]};
        }

        return newState;
    }

    return state;
};

export default userReducer;
