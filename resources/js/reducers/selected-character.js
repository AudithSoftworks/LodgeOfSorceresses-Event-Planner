import * as deleteMyCharacterActions from "../actions/delete-my-character";
import * as getCharacterActions from "../actions/get-character";
import * as putCharacterActions from "../actions/put-character";
import * as viewCharacterActions from "../actions/view-character";

const selectedCharacterReducer = (state = {}, action) => {
    if (action.type === getCharacterActions.TYPE_GET_CHARACTER_SUCCESS || action.type === putCharacterActions.TYPE_PUT_CHARACTER_SUCCESS) {
        const character = action.response.entities.characters[action.response.result];
        if (state && state.id === character.id) {
            return character;
        }
    } else if (action.type === viewCharacterActions.TYPE_VIEW_CHARACTER_SUCCESS) {
        return action.response.entities.characters[action.response.result];
    } else if (action.type === viewCharacterActions.TYPE_VIEW_CHARACTER_FAILURE) {
        return null;
    } else if (action.type === deleteMyCharacterActions.TYPE_DELETE_MY_CHARACTER_SUCCESS) {
        return null;
    }

    return state;
};

export default selectedCharacterReducer;
